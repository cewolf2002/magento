<?php

class Webxmore_Payment_Model_System_Config_Source_Creditpayments {

    public function toOptionArray() {
        $arr = array();

        for ($i = 0; $i < 100; $i++) {
            $arr[] = array('value' => $i, 'label' => $i);
        }
        return $arr;
    }

}
