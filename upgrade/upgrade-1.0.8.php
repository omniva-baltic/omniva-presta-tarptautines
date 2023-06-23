<?php

function upgrade_module_1_0_8($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'omniva_int_terminal` ADD `zip` varchar(10);';
    return Db::getInstance()->execute($sql);
}
