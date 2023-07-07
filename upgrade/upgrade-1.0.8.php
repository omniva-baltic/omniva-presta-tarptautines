<?php

function upgrade_module_1_0_8($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'omniva_int_terminal` ADD `zip` varchar(10);';
    if ( ! Db::getInstance()->execute($sql) ) {
        return false;
    }

    try {
        $updater = new OmnivaIntUpdater('terminals');
        if ( ! $updater->run() ) {
            return false;
        }
    } catch (\Exception $e) {
        return false;
    }

    return true;
}
