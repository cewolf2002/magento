<?php

abstract class Webxmore_Payment_Model_Abstract extends Mage_Payment_Model_Method_Abstract {

    /**
     * unique internal payment method identifier
     */
    protected $_code = 'wbxpayment_abstract';

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_paymentMethod = 'abstract';
    protected $_defaultLocale = 'he';
    protected $_supportedLocales = array('en');
    protected $_hidelogin = '1';
    protected $_order;

    /**
     * Return url for redirection after order placed
     * @return string
     */
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('wbxpayment/processing/pay');
    }

    /**
     * Get initialized flag status
     * @return true
     */
    public function isInitializeNeeded() {
        return true;
    }

    /**
     * Instantiate state and set it to state onject
     * //@param
     * //@param
     */
    public function initialize($paymentAction, $stateObject) {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }

    public function getConfigPaymentAction() {
        $paymentAction = $this->getConfigData('payment_action');
        return empty($paymentAction) ? true : $paymentAction;
    }

    public function getOrder() {
        if (!$this->_order) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }

}
