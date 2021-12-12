<?php

class OmnivaIntHelper
{

    /**
     * Get country id in API by country code
     */
    public function getCountryId($country_code, $on_error_return_code = false)
    {
        $country_code = strtoupper($country_code);

        return ($on_error_return_code) ? $country_code : false;
    }

    /**
     * Get list of all Prestashop carriers
     */
    public function getAllCarriers($id_only = false)
    {
        $carriers = Carrier::getCarriers(
            Context::getContext()->language->id,
            true,
            false,
            false,
            NULL,
            PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
        );
        if ($id_only) {
            $id_list = array();
            foreach ($carriers as $carrier)
                $id_list[] = $carrier['id_carrier'];
            return $id_list;
        }

        return $carriers;
    }

    /**
     * Check if Prestashop carrier belongs to this module
     */
    public function itIsThisModuleCarrier($carrier_reference)
    {
        foreach (MijoraVenipak::$_carriers as $carrier) {
            if (Configuration::get($carrier['reference_name']) == $carrier_reference) {
                if (isset($carrier['type'])) {
                    return $carrier['type'];
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Write log files
     */
    public function writeToLog($message, $file_name = 'errors')
    {
        $logger = new FileLogger(0);
        $logger->setFilename(MijoraVenipak::$_moduleDir . "logs/" . $file_name . '.log');
        $logger->logDebug(print_r($message,true));
    }
}
