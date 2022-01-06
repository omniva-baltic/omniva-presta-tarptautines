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

    public function displayAlert($content, $sugar, $type = 'info')
    {
        $context = Context::getContext();
        $context->smarty->assign(
            [
                'content' => $content,
                'sugar' => $sugar,
                'type' => $type,
            ]
        );
        return $context->smarty->fetch(_PS_MODULE_DIR_ . "omnivainternational/views/templates/admin/alert.tpl");
    }
}
