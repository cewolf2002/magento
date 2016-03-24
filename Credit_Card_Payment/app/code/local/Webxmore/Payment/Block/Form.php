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
 * This file controls the webxmore_pelecard/form.phtml from comments folder
 * 
 * @company     WebXMore
 * @package     Webxmore_Pelecard
 * @copyright   Copyright (c) 2014 WebXMore 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webxmore_Payment_Block_Form extends Mage_Payment_Block_Form {

    /**
     * Constructor. Set template.
     */
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('webxmore_wbxpayment/form.phtml');
    }

}
