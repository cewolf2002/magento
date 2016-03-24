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

        $info = Mage::getModel("wbxpayment/order");
        $info->setInfo(json_encode($params));
        $info->setOrder($order->getId());

	$info->save();

	if ($params["RC"] == "00") {
		$order->setData('pending_payment', "new");
		$order->setStatus("pending_payment");
		$order->sendNewOrderEmail();
		$order->setEmailSent(true);
		$order->save();

            Mage::helper("wbxpayment")->createInvoice();

            $this->_redirect('checkout/onepage/success');
        } else {
		$order->setData('state', "new");
		$order->setStatus("canceled");
		$order->save();
            
               if($order->canCancel())  $order->cancel();
            //  $order->setStatus('canceled_pendings');
            $order->save();
            return $this->_redirect('checkout/onepage/failure');
        }
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
