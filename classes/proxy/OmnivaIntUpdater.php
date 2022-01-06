<?php

use OmnivaApi\API;
require_once __DIR__ . "/../models/OmnivaIntTerminal.php";
require_once __DIR__ . "/../models/OmnivaIntService.php";
require_once __DIR__ . "/../models/OmnivaIntCountry.php";

class OmnivaIntUpdater {

    private $api;

    private $type;

    public function __construct($type)
    {
        $token = Configuration::get('OMNIVA_TOKEN');
        $test_mode = Configuration::get('OMNIVA_INT_TEST_MODE');
        $this->api = new API($token, $test_mode);
        $this->type = $type;
    }

    public function run()
    {
        switch($this->type)
        {
            case 'terminals':
                return $this->updateTerminals();
            case 'services':
                return $this->updateServices();
            case 'countries':
                return $this->updateCountries();
            case 'all':
                return $this->updateTerminals() && $this->updateServices() && $this->updateCountries();
            default:
                return false;
        }
    }

    public function updateTerminals()
    {
        $response = $this->api->getTerminals();
        $result = true;
        if($response && isset($response->terminals))
        {
            $result &= Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'omniva_int_terminal`');
            foreach($response->terminals as $terminal)
            {
                $terminalObj = new OmnivaIntTerminal();
                $terminalObj->id = $terminal->id;
                $terminalObj->force_id = true;
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
        return $result;
    }

    public function updateServices()
    {
        $response = $this->api->listAllServices();
        $result = true;
        if($response && !empty($response))
        {
            foreach($response as $service)
            {
                $id_service = $service->id;
                $serviceObj = new OmnivaIntService($id_service);
                if(Validate::isLoadedObject($serviceObj))
                {
                    $result &= $this->hydrateService($serviceObj, $service)->save();
                }
                else
                {
                    $serviceObj = $this->hydrateService($serviceObj, $service);
                    $serviceObj->id = $service->id;
                    $serviceObj->force_id = true;
                    // Categories managment for services is disabled by default.
                    $serviceObj->manage_categories = false;
                    $result &= $serviceObj->add();
                }
            }
        }
        return $result;
    }

    public function updateCountries()
    {
        $response = $this->api->listAllCountries();
        $result = true;
        if($response && !empty($response))
        {
            $result &= Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'omniva_int_country`');
            foreach($response as $country)
            {
                $countryObj = new OmnivaIntCountry();
                $countryObj->id = $country->id;
                $countryObj->name = $country->name;
                $countryObj->en_name = $country->en_name;
                $countryObj->code = $country->code;
                $countryObj->force_id = true;
                $result &= $countryObj->add();
            }
        }
        return $result;
    }


    public function hydrateService($prestaService, $apiService)
    {
        $prestaService->name = $apiService->name;
        $prestaService->service_code = $apiService->service_code;
        $prestaService->image = $apiService->image;
        $prestaService->pickup_from_address = $apiService->pickup_from_address;
        $prestaService->delivery_to_address = $apiService->delivery_to_address;
        $prestaService->parcel_terminal_type = $apiService->parcel_terminal_type;

        if(isset($apiService->additional_services))
        {
            $prestaService->cod = isset($apiService->additional_services->cod) ? $apiService->additional_services->cod : false;
            $prestaService->insurance = isset($apiService->additional_services->insurance) ? $apiService->additional_services->insurance : false;
            $prestaService->carry_service = isset($apiService->additional_services->carry_service) ? $apiService->additional_services->carry_service : false;
            $prestaService->doc_return = isset($apiService->additional_services->doc_return) ? $apiService->additional_services->doc_return : false;
            $prestaService->own_login = isset($apiService->additional_services->own_login) ? $apiService->additional_services->own_login : false;
            $prestaService->fragile = isset($apiService->additional_services->fragile) ? $apiService->additional_services->fragile : false;
        }

        return $prestaService;
    }
}