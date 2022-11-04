<?php

function upgrade_module_1_0_4($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'omniva_int_carrier_country` DROP COLUMN `tax`;
            ALTER TABLE `' . _DB_PREFIX_ . 'omniva_int_carrier` DROP COLUMN `tax`;';
    return Db::getInstance()->execute($sql);
}