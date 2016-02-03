<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Webxmore
 * @package     Webxmore_Pelecard
 * @copyright   Copyright (c) 2011 Webxmore 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Pelecard notification processor model
 */
class Webxmore_Payment_Model_Event {

    const PELECARD_STATUS_SUCCESS = '000';

    protected $_dataMap = array(
        'status' => array(1, 3),
        'CCNo' => array(5, 19),
        'cardBrand' => array(24, 1),
        'amount' => array(36, 8),
        'authNo' => array(71, 7),
        'firstPayment' => array(78, 8),
        'nextPayments' => array(86, 8),
        'noOfPayments' => array(94, 2),
        'currencyCode' => array(65, 1),
        'transactionId' => array(71, 7),
    );

    /*
     * @param Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Event request data
     * @var array
     */
    protected $_eventData = array();

    /**
     * Enent request data setter
     * @param array $data
     * @return Webxmore_Pelecard_Model_Event
     */
    public function setEventData(array $data) {
        $this->_eventData = $data;
        return $this;
    }

    /**
     * Event request data getter
     * @param string $key
     * @return array|string
     */
    public function getEventData($key = null) {
        if (null === $key) {
            return $this->_eventData;
        }
        return isset($this->_eventData[$key]) ? $this->_eventData[$key] : null;
    }

    public function getResultData($key = null) {
        return $this->_eventData[$key];
    }

 
    protected function _getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Process status notification from Monebookers server
     *
     * @return String
     */
    public function processStatusEvent() {
        try {
            $msg = '';
            switch ($this->getResultData('PelecardStatusCode')) {
                case self::PELECARD_STATUS_SUCCESS: //ok
                    if ($amount = $this->_validateEventData()) {
                        $msg = Mage::helper('wbxpayment')->__('%s has been authorized and captured by Pelecard.', $amount / 100);
                        $this->_processSale($this->getResultData('PelecardStatusCode'), $msg);
                        break;
                    }
                default: //fail
                    $msg = Mage::helper('wbxpayment')->__('Payment failed.');
                    $this->_processCancel($msg);
                    break;
            }
            return $msg;
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }

    /**
     * Processed order cancelation
     * @param string $msg Order history message
     */
    protected function _processCancel($msg) {
        $this->_validateEventData(false);
        $this->_order->cancel();
        $this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, $msg . ' with error number: ' . $this->getResultData('PelecardStatusCode'));
        $this->_order->save();
        $session = $this->_getCheckout();
        $quote = Mage::getModel('sales/quote')
                ->load($this->_order->getQuoteId());
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

    /**
     * Validate request and return QuoteId
     * Can throw Mage_Core_Exception and Exception
     *
     * @return int
     */
    public function successEvent() {
        $this->_validateEventData(false);
        return $this->_order->getQuoteId();
    }

    /**
     * Processes payment confirmation, creates invoice if necessary, updates order status,
     * sends order confirmation to customer
     * @param string $msg Order history message
     */
    protected function _processSale($status, $msg) {
        switch ($status) {
            case self::PELECARD_STATUS_SUCCESS:
                $this->_createInvoice();
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $msg);
                // save transaction ID
                $this->_order->getPayment()->setLastTransId($this->getResultData('authNo'));
                $additionalInformation = $this->_order->getPayment()->getAdditionalInformation();
                $additional = array_merge(array($this->getResultData('firstPayment'), $this->getResultData('nextPayments'), $this->getResultData('noOfPayments'), $this->getResultData('CCNo')), $additionalInformation);
                $this->_order->getPayment()->setAdditionalInformation($additional);
                // send new order email
                $this->_order->sendNewOrderEmail();
                $this->_order->setEmailSent(true);
                break;
            case self::PELECARD_STATUS_PENDING:
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $msg);
                // save transaction ID
                $this->_order->getPayment()->setLastTransId($this->getResultData('authNo'));
                break;
        }
        $this->_order->save();
    }

    /**
     * Builds invoice for order
     */
    protected function _createInvoice() {
        if (!$this->_order->canInvoice()) {
            return;
        }
        $invoice = $this->_order->prepareInvoice();
        $invoice->register()->capture();
        $invoice->sendEmail();
        $this->_order->addRelatedObject($invoice);
    }

    /**
     * Checking returned parameters
     * Thorws Mage_Core_Exception if error
     * @param bool $fullCheck Whether to make additional validations such as payment status, transaction signature etc.
     *
     * @return array  $params request params
     */
    protected function _validateEventData($fullCheck = true) {
        // get request variables
        $params = $this->_eventData;

        if (empty($params)) {
            Mage::throwException('Request does not contain any elements.');
        }

        // load order for further validation
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($params['ParamX']);

        if (!$this->_order->getId()) {
            Mage::throwException('Order not found.');
        }
        Mage::log($this->_order->getPayment()->getMethodInstance()->getCode());
        if (0 !== strpos($this->_order->getPayment()->getMethodInstance()->getCode(), 'wbxpayment_acc')) {
            Mage::throwException('Unknown payment method.');
        }

        // make additional validation
        if ($fullCheck) {

            $additionalInformation = $this->_order->getPayment()->getAdditionalInformation();
            // Mage::log("event " . print_r($additionalInformation,true));
            if (isset($additionalInformation['creditamount'])) {
                $paymentAmount = $additionalInformation['creditamount'];
            } else {
                $paymentAmount = round($this->_order->getGrandTotal(), 2) * 100;
            }

            return $paymentAmount;
        }

        return true;
    }

   

}
