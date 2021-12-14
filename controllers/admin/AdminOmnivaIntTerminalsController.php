<?php

class AdminOmnivaIntTerminalsController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function __construct()
    {
        $this->list_no_link = true;
        $this->_orderBy = 'id';
        $this->className = 'OmnivaIntTerminal';
        $this->table = 'omniva_int_terminal';
        $this->identifier = 'id';

        $this->_select = ' cl.name';
        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'country c ON (c.iso_code = a.country_code)
            LEFT JOIN ' . _DB_PREFIX_ . 'country_lang cl ON (cl.id_country = c.id_country)';

        $this->_where = ' AND cl.id_lang = ' . $this->context->language->id;
        parent::__construct();
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
}