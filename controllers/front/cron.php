<?php

class OmnivaInternationalCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $token = 'jSrek3Y10MNaHfs2a3NXugtt';
        $api = $ps = new API($token, true, true);
        $response = $api->getTerminals('LT');
        dump($response);
    }
}
