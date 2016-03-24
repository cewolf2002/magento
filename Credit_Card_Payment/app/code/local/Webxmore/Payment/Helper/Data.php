<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author kryuch
 */
class Webxmore_Payment_Helper_Data extends Mage_Payment_Helper_Data {

    function createInvoice() {
        $order = Mage::getModel("sales/order")->load(Mage::getSingleton('checkout/session')->getLastOrderId());

        if (!$order->canInvoice()) {
            Mage::log("Can't create invoice");
        }

        $invoice = Mage::getModel("sales/service_order", $order)->prepareInvoice();
        if (!$invoice->getTotalQty()) {
            Mage::log("No price");
        }
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

        $transactionSave->save();
    }

    function getParam($key) {
        $store = Mage::app()->getStore();
        $value = Mage::getStoreConfig("payment/wbxpayment_acc/$key", $store);
        return ($value == "") ? Mage::getStoreConfig("payment/wbxpayment/$key", $store) : $value;
    }

    function getUrl() {
        return
                Mage::helper("wbxpayment")->getParam("testmode") ? "https://acqtest.esunbank.com.tw/acq_online/online/sale42Mobile.htm" : "https://acq.esunbank.com.tw/acq_online/online/sale42Mobile.htm";
    }

    public function log($text, $title) {
        if ($this->getParam("debug")) {
            Mage::log($title, null, "wbxpayment");
            Mage::log("***************************", null, "wbxpayment");
            Mage::log($text, null, "wbxpayment");
            Mage::log("", null, "wbxpayment");
        }
    }

    public function getParams() {
        $order = Mage::getModel("sales/order")->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        $params = array
            (
            "U" => "http://" . $_SERVER['HTTP_HOST'] . "/wbxpayment/processing/result",
            "ONO" => $order->getData('increment_id'),
            "TA" => round($order->getGrandTotal(), 2)
        );
        foreach (array("mid", "cid", "tid") as $param) {
            $params[strtoupper($param)] = Mage::helper("wbxpayment")->getParam($param);
        }
        $marray = array();
        foreach (array("MID", "CID", "TID", "ONO", "TA", "U") as $field)
            $marray[] = $params[$field];
        $marray[] = Mage::helper("wbxpayment")->getParam("mv");
        $params["M"] = md5(implode("&", $marray));
        Mage::helper("wbxpayment")->log(implode("&", $marray), "M");
        Mage::helper("wbxpayment")->log(print_r($params, true), "params");
        return $params;
    }

}
