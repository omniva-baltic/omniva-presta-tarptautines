<?php

require_once "AdminOmnivaIntBaseController.php";

use OmnivaApi\API;

class AdminOmnivaIntCountriesController extends AdminOmnivaIntBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->list_no_link = true;
        $this->title_icon = 'icon-map-marker';
        $this->_orderBy = 'id_country';
        $this->className = 'OmnivaIntCountry';
        $this->table = 'omniva_int_country';
        $this->identifier = 'id_country';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->countriesList();
        }
        parent::init();
    }

    protected function countriesList()
    {
        $this->fields_list = array(
            'id_country' => array(
                'title' => $this->module->l('ID'),
                'align' => 'text-center',
            ),
            'name' => array(
                'type' => 'text',
                'title' => $this->module->l('Name'),
                'align' => 'center',
            ),
            'en_name' => array(
                'title' => $this->module->l('Name EN'),
                'type' => 'text',
                'align' => 'center',
            ),
            'code' => array(
                'title' => $this->module->l('Code'),
                'type' => 'text',
            ),
        );

        $this->bulk_actions = [];
    }

    public function initToolbar()
    {
        $this->toolbar_btn['bogus'] = [
            'href' => '#',
            'desc' => $this->trans('Back to list'),
        ];
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['sync_countries'] = [
            'href' => self::$currentIndex . '&sync_countries=1&token=' . $this->token . '&cron_token=' . Configuration::get('OMNIVA_CRON_TOKEN'),
            'desc' => $this->trans('Update Countries'),
            'imgclass' => 'refresh',
        ];
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::getValue('sync_countries'))
        {
            $this->updateCountries();
        }
    }

    public function updateCountries()
    {
        $cron_token = Configuration::get('OMNIVA_CRON_TOKEN');
        $token = Tools::getValue('cron_token');
        if($token != $cron_token)
        {
            $this->errors[] = $this->trans('Invalid cron token.', array(), 'Admin.Notifications.Error');
            return;
        }

        $token = Configuration::get('OMNIVA_TOKEN');
        $api = new API($token, Configuration::get('OMNIVA_INT_TEST_MODE'));
        $result = true;

        $response = $api->listAllCountries();
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
        
        if($result)
            $this->confirmations[] = $this->trans('Successfully updated countries', array(), 'Admin.Notifications.Error');
        else
            $this->errors[] = $this->trans("Failed updating countries", array(), 'Admin.Notifications.Error');
    }
    
}