<?php

use OmnivaApi\API;

class OmnivaIntHelper
{
    const POST_NL_SERVICE_NAME = 'PostNL';

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

    // Return boolean.
    public function checkServiceException($final_services)
    {
        foreach($final_services as $id_service)
        {
            $service = new OmnivaIntService($id_service);
            if($service->name == self::POST_NL_SERVICE_NAME)
                return true;
        }
        return false;
    }
}