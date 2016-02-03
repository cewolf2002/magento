<?php

class Webxmore_Payment_Model_System_Config_Source_Supportedcards {

    public function toOptionArray() {
        return array(
            array(
                'value' => 1,
                'label' => 'Visa',
            ),
            array(
                'value' => 2,
                'label' => 'MasterCard',
            ),
            array(
                'value' => 3,
                'label' => 'American Express',
            ),
            array(
                'value' => 4,
                'label' => 'Dinners',
            ),
            array(
                'value' => 5,
                'label' => 'local IsraCard',
            )
        );
    }

}