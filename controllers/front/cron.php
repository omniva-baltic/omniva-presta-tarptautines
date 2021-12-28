<?php

use OmnivaApi\API;
require_once __DIR__ . "/../../classes/OmnivaIntTerminal.php";
require_once __DIR__ . "/../../classes/OmnivaIntService.php";

class OmnivaInternationalCronModuleFrontController extends ModuleFrontController
{
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
            $response = $api->getTerminals();
            dump($response);
            if($response && isset($response->parcel_machines))
            {
                $result &= Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'omniva_int_terminal`');
                foreach($response->parcel_machines as $terminal)
                {
                    $terminalObj = new OmnivaIntTerminal();
                    $terminalObj->name = $terminal->name;
                    $terminalObj->city = $terminal->city;
                    $terminalObj->country_code = $terminal->country_code;
                    $terminalObj->address = $terminal->address;
                    $terminalObj->x_cord = $terminal->x_cord;
                    $terminalObj->y_cord = $terminal->y_cord;
                    $terminalObj->comment = $terminal->comment;
                    $terminalObj->identifier = $terminal->identifier;
                    $result &= $terminalObj->add();
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
