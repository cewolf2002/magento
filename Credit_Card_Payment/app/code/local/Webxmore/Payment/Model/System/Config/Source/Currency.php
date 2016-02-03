<?php

class Webxmore_Payment_Model_System_Config_Source_Currency {

    public function toOptionArray() {
        return array(
            array(
                'value' => 1,
                'label' => 'Sheqel',
            ),
            array(
                'value' => 2,
                'label' => 'Dollar',
            ),
            array(
                'value' => 978,
                'label' => 'Euro',
            )
        );
    }

}