<?php

use OmnivaApi\API;
require_once __DIR__ . "/../../classes/proxy/OmnivaIntUpdater.php";

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
        $updater = new OmnivaIntUpdater($type);

        if($updater->run())
        {
            echo "Successfully updated $type";
        }
        else
        {
            echo "Failed updating $type";  
        }
        die();
    }
}
