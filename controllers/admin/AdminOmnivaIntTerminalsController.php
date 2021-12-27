<?php

require_once "AdminOmnivaIntBaseController.php";

use OmnivaApi\API;

class AdminOmnivaIntTerminalsController extends AdminOmnivaIntBaseController
{
    const LIST_ALLOWED_COUNTRIES = ['LT', 'LV', 'EE'];

    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function __construct()
    {
        parent::__construct();

        $this->list_no_link = true;
        $this->title_icon = 'icon-map-marker';
        $this->_orderBy = 'id';
        $this->className = 'OmnivaIntTerminal';
        $this->table = 'omniva_int_terminal';
        $this->identifier = 'id';

        $this->_select = ' cl.name as country';
        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'country c ON (c.iso_code = a.country_code)
            LEFT JOIN ' . _DB_PREFIX_ . 'country_lang cl ON (cl.id_country = c.id_country)';

        $this->_where = ' AND cl.id_lang = ' . $this->context->language->id;
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->terminalList();
        }
        parent::init();
    }

    protected function terminalList()
    {
        $this->fields_list = array(
            'name' => array(
                'title' => $this->module->l('Name'),
                'align' => 'text-center',
            ),
            'city' => array(
                'type' => 'text',
                'title' => $this->module->l('City'),
                'align' => 'center',
            ),
            'country' => array(
                'title' => $this->module->l('Country'),
                'type' => 'text',
                'filter_key' => 'cl!name'
            ),
            'address' => array(
                'title' => $this->module->l('Address'),
                'type' => 'text',
            ),
            'zipcode' => array(
                'type' => 'text',
                'title' => $this->module->l('Zipcode'),
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
        $this->page_header_toolbar_btn['sync_terminals'] = [
            'href' => self::$currentIndex . '&sync_terminals=1&token=' . $this->token . '&cron_token=' . Configuration::get('OMNIVA_CRON_TOKEN'),
            'desc' => $this->trans('Update Terminals'),
            'imgclass' => 'refresh',
        ];
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::getValue('sync_terminals'))
        {
            $this->updateTerminals();
        }
    }

    public function updateTerminals()
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
            $this->confirmations[] = $this->trans('Successfully updated terminals', array(), 'Admin.Notifications.Error');
        else
            $this->errors[] = $this->trans("Failed updating terminals", array(), 'Admin.Notifications.Error');
    }
    
}