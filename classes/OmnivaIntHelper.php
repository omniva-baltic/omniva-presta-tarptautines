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

    private static $order_is_exception = [];

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

    public function checkIfOrderException($order)
    {
        if(isset(self::$order_is_exception[$order->id]))
            return self::$order_is_exception[$order->id];
        $carrier = new Carrier($order->id_carrier);
        $carrier_reference = $carrier->id_reference;
        $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($carrier_reference);
        $id_carrier = $omnivaCarrier->id;

        $address = new Address($order->id_address_delivery);
        $id_country = $address->id_country;

        $omnivaCarrierCountry = OmnivaIntCarrierCountry::getCarrierCountry((int) $id_carrier, (int) $id_country);

        $is_exception = (bool) $omnivaCarrierCountry->is_exception;
        self::$order_is_exception[$order->id] = $is_exception;
        return $is_exception;
    }
}