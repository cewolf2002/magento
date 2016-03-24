<?php
/**
	Copyright (c) 2011, WebXMore
 * Configuration of the button in admin panel
*/

class Webxmore_Payment_Block_System_Config_Form_Field_Regexceptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('totalamount', array(
            'label' => Mage::helper('wbxpayment')->__('From last amount - up to'),
            'style' => 'width:120px',
        ));
        $this->addColumn('paymentsno', array(
            'label' => Mage::helper('wbxpayment')->__('Payments number'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('wbxpayment')->__('Add payments number');
        parent::__construct();
    }
}
