<?php

function upgrade_module_1_0_5($module)
{
    return $module->registerHook('actionOrderGridDefinitionModifier') 
        && $module->registerHook('actionAdminOrdersListingFieldsModifier')
        && $module->registerHook('displayAdminListBefore');
}