<?php

use Siusk24LT\API;
require_once __DIR__ . "/../../classes/OmnivaIntTerminal.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class OmnivaInternationalCronModuleFrontController extends ModuleFrontController
{
    const LIST_ALLOWED_COUNTRIES = ['LT', 'LV', 'EE'];

    public function initContent()
    {
        $countries = Country::getCountries(Configuration::get('PS_LANG_DEFAULT'), true);
        $countries_isos = array_map(function ($country) {
            return $country['iso_code'];
        }, $countries);
        $cron_token = Configuration::get('OMNIVA_CRON_TOKEN');
        $token = Tools::getValue('token');
        if($token != $cron_token)
        {
            Tools::redirect('pagenotfound');
        }
        $token = Configuration::get('OMNIVA_TOKEN');
        $api = new API($token, Configuration::get('OMNIVA_INT_TEST_MODE'));

        $result = true;
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
            die('Success');
        else
            die("Failure");  
    }
}
