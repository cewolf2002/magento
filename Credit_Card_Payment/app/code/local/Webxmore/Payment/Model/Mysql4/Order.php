<?php

class Webxmore_Payment_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init("wbxpayment/order", "id");
    }

}

?>