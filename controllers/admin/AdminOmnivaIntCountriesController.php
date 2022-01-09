<?php

require_once "AdminOmnivaIntBaseController.php";

use OmnivaApi\API;

class AdminOmnivaIntCountriesController extends AdminOmnivaIntBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->list_no_link = true;
        $this->title_icon = 'icon-flag';
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
        $this->fields_list = [
            'id_country' => [
                'title' => $this->module->l('ID'),
                'align' => 'text-center',
            ],
            'name' => [
                'type' => 'text',
                'title' => $this->module->l('Name'),
                'align' => 'center',
            ],
            'en_name' => [
                'title' => $this->module->l('Name EN'),
                'type' => 'text',
                'align' => 'center',
            ],
            'code' => [
                'title' => $this->module->l('Code'),
                'type' => 'text',
            ],
        ];

        $this->bulk_actions = [];
    }

    public function initToolbar()
    {
        $this->toolbar_btn['bogus'] = [
            'href' => '#',
            'desc' => $this->module->l('Back to list'),
        ];
    }

    public function initPageHeaderToolbar()
    {
        if(Configuration::get('OMNIVA_TOKEN'))
        {
            $this->page_header_toolbar_btn['sync_countries'] = [
                'href' => self::$currentIndex . '&sync_countries=1&token=' . $this->token . '&cron_token=' . Configuration::get('OMNIVA_CRON_TOKEN'),
                'desc' => $this->module->l('Update Countries'),
                'imgclass' => 'refresh',
            ];
        }
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
        $updater = new OmnivaIntUpdater('countries');
        if($updater->run())
            $this->confirmations[] = $this->module->l('Successfully updated countries');
        else
            $this->errors[] = $this->module->l("Failed updating countries");
    }
    
}