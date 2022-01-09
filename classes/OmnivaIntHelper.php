<?php

use OmnivaApi\API;

class OmnivaIntHelper
{
    // separate class to bypass ugly 1.6 architechture botch, which does not allow "use" statements in the main module file.
    public function getApi()
    {
        if(Configuration::get('OMNIVA_TOKEN'))
            return new API(Configuration::get('OMNIVA_TOKEN'), Configuration::get('OMNIVA_INT_TEST_MODE'));
        return false;
    }
}