<?php

$prefix = Mage::getConfig()->getTablePrefix();
$table = $prefix . "wbxpayment_orders_info";
$installer = $this;
$installer->startSetup();


$sql = <<<SQLTEXT
    drop table if exists $table; create table $table (id int not null auto_increment,
    `order` int, 
    info text,
    primary key(id)) CHARACTER SET utf8;       
        
SQLTEXT;
$installer->run($sql);

$installer->endSetup();
?>