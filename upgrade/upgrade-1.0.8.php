<?php

function upgrade_module_1_0_8($module)
{
    $table_terminals = _DB_PREFIX_ . 'omniva_int_terminal';
    
    $sql = "SHOW COLUMNS FROM `" . $table_terminals . "` LIKE 'zip';";
    if ( ! Db::getInstance()->execute($sql) ) {
        $sql = "ALTER TABLE `" . $table_terminals . "` ADD `zip` varchar(10);";
        if ( ! Db::getInstance()->execute($sql) ) {
            return false;
        }
    }

    $sql = "SHOW COLUMNS FROM `" . $table_terminals . "` LIKE 'terminal_id';";
    if ( ! Db::getInstance()->execute($sql) ) {
        $sql = "ALTER TABLE `" . $table_terminals . "` ADD `terminal_id` varchar(10);";
        if ( ! Db::getInstance()->execute($sql) ) {
            return false;
        }
    }

    return true;
}
