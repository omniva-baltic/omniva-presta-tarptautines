<?php

use OmnivaApi\API;
require_once __DIR__ . "/../../classes/OmnivaIntTerminal.php";
require_once __DIR__ . "/../../classes/OmnivaIntService.php";

class OmnivaInternationalCronModuleFrontController extends ModuleFrontController
{
    const LIST_ALLOWED_COUNTRIES = ['LT', 'LV', 'EE'];

    public function initContent()
    {
        $cron_token = Configuration::get('OMNIVA_CRON_TOKEN');
        $token = Tools::getValue('token');
        if($token != $cron_token)
        {
            Tools::redirect('pagenotfound');
        }

        $type = Tools::getValue('type');
        $token = Configuration::get('OMNIVA_TOKEN');
        $api = new API($token, Configuration::get('OMNIVA_INT_TEST_MODE'));
        $result = true;

        if($type == 'terminals')
        {
            $countries = Country::getCountries(Configuration::get('PS_LANG_DEFAULT'), true);
            $countries_isos = array_map(function ($country) {
                return $country['iso_code'];
            }, $countries);
    
            foreach($countries_isos as $iso)
            {
                $iso = trim($iso);
                if(!in_array($iso, self::LIST_ALLOWED_COUNTRIES))
                    continue;
                $response = $api->getTerminals($iso);
                if($response && isset($response->terminals))
                {
                    $result &= Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'omniva_int_terminal`');
                    foreach($response->terminals as $terminal)
                    {
                        $terminalObj = new OmnivaIntTerminal();
                        $terminalObj->name = $terminal->name;
                        $terminalObj->city = $terminal->city;
                        $terminalObj->country_code = $terminal->country_code;
                        $terminalObj->address = $terminal->address;
                        $terminalObj->zipcode = $terminal->zipcode;
                        $result &= $terminalObj->add();
                    }
                } 
            }
            if($result)
                echo 'Successfully updated terminals';
            else
                echo "Failed updating terminals";  
        }
        elseif($type == 'services')
        {
            $response = $api->listAllServices();
            if($response && !empty($response))
            {
                $result &= Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'omniva_int_service`');
                foreach($response as $service)
                {
                    $serviceObj = new OmnivaIntService();
                    $serviceObj->name = $service->name;
                    $serviceObj->service_code = $service->service_code;
                    $serviceObj->image = $service->image;
                    $result &= $serviceObj->add();
                }
            }
            if($result)
                echo 'Successfully updated services';
            else
                echo "Failed updating services";  
        }
        die();
    }
}
