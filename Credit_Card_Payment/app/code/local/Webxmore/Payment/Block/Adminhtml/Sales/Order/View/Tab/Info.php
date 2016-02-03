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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order information tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webxmore_Payment_Block_Adminhtml_Sales_Order_View_Tab_Info
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
   
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment').$this->__getComment();
    }


    function __getComment() {
        $order = $this->getOrder();
        if ("wbxpayment_acc" != $order->getPayment()->getMethodInstance()->getCode()) return ;
        
        $info = Mage::getModel("wbxpayment/order")->getCollection()->addFieldToFilter("`order`", array("eq" => $order->getId()))->getFirstItem();
        return $info->getInfo();
    }
}
