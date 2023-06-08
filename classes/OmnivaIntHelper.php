<?php

use OmnivaApi\API;

class OmnivaIntHelper
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    // separate class to bypass ugly 1.6 architechture botch, which does not allow "use" statements in the main module file.
    public function getApi()
    {
        if(Configuration::get('OMNIVA_TOKEN'))
            return new API(Configuration::get('OMNIVA_TOKEN'), Configuration::get('OMNIVA_INT_TEST_MODE'));
        return false;
    }

    public function getConfigValue($key)
    {
        foreach($this->module->_configKeys as $section)
        {
            foreach($section as $configKey => $configName)
            {
                if($configKey == $key)
                {
                    return Configuration::get($configName);
                }
            }
        }
        return null;
    }

    public function changeWeightUnit($value, $current_unit, $new_unit)
    {
        $to_kg = array(
            'mg' => 0.000001,
            'g' => 0.001,
            'kg' => 1,
            't' => 1000,
            'gr' => 0.0000648,
            'k' => 0.0002,
            'oz' => 0.02835,
            'lb' => 0.45359,
            'cnt' => 100,
        );

        if (isset($to_kg[$current_unit]) && isset($to_kg[$new_unit])) {
            $current_kg = $value * $to_kg[$current_unit]; //Change value to kg
            return $current_kg / $to_kg[$new_unit]; //Change kg value to new unit
        }

        return $value;
    }
}