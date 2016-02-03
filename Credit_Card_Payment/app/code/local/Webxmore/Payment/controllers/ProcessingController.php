<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Process
 *
 * @author kryuch
 */
class Webxmore_Payment_ProcessingController extends Mage_Core_Controller_Front_Action {

    function indexAction() {
        $block = $this->getLayout()->createBlock('core/template')->setTemplate('webxmore_wbxpayment/index.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Iframe page which submits the payment data to Pelecard.
     */
    public function payAction() {

        $block = $this->getLayout()->createBlock('core/template')->setTemplate('webxmore_wbxpayment/pay.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function resultAction() {
        $params = $this->getRequest()->getParams();
Mage::helper("wbxpayment")->log(print_r($params, true), "Result");

        $order = Mage::getModel("sales/order")->loadByIncrementId($params["ONO"]);
        
        $text = array();
        foreach ($params as $field => $value) {
            $text[] = $field." = ".$value;
        }
        $info = Mage::getModel("wbxpayment/order");
        $data = array("info" => implode("; ".$text), "`order`" => $order->getId());
        Mage::helper("wbxpayment")->log(print_r($data, true), "Save to DB");
        $info->setData($data);
        $info->save();
        
        if ($params["RC"] == "00") {
            $order->setData('state', "new");
            $order->setStatus("complete");
            $this->_redirect('checkout/onepage/success');
        }
        else
            return $this->_redirect('checkout/cart');
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return mixed
     */
    public function cancelAction($errorMsg = '') {
        $session = $this->_getCheckout();

        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {

                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->registerCancellation($errorMsg)->save();
                }
                $quote = Mage::getModel('sales/quote')
                        ->load($order->getQuoteId());
                if ($quote->getId()) {
                    $quote->setIsActive(1)
                            ->setReservedOrderId(NULL)
                            ->save();
                    $session->replaceQuote($quote);
                }
                $session->unsLastRealOrderId();
            }
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Action to which the transaction details will be posted after the payment
     * process is complete.
     */
    public function errorAction() {
        Mage::helper("wbxpayment")->log(print_r($this->getRequest()->getParams(), true), "Error");
        $result = $this->getRequest()->getParams();

        $session = $this->_getCheckout();
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->registerCancellation($result['result'])->save();
                }
                $quote = Mage::getModel('sales/quote')
                        ->load($order->getQuoteId());
                //Return quote
                if ($quote->getId()) {
                    $quote->setIsActive(1)
                            ->setReservedOrderId(NULL)
                            ->save();
                    $session->replaceQuote($quote);
                }
                //Unset data
                $session->unsLastRealOrderId();
            }
        }
        Mage::getSingleton('core/session')->addError('ההזמנה נכשלה עם שגיאה מספר ' . substr($result['result'], 0, 3) . ' אנא פנה למוקד שרות הלקוחות במידה וההזמנה נכשלת שנית');

        $this->_redirect('checkout/cart');
    }

    /**
     * Set redirect into responce. This has to be encapsulated in an JavaScript
     * call to jump out of the iframe.
     *
     * @param string $path
     * @param array $arguments
     */
    protected function _redirect($path, $arguments = array()) {
        $block = $this->getLayout()->createBlock('core/template')->setTemplate('webxmore_wbxpayment/redirect.phtml');
        Mage::getSingleton('core/session')->setRedirectUrl(Mage::getUrl($path, $arguments)); // In the Controller
        $this->getResponse()->setBody($block->toHtml());
    }

}
