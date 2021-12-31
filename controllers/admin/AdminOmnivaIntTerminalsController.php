<?php

require_once "AdminOmnivaIntBaseController.php";
require_once __DIR__ . "/../../classes/OmnivaIntUpdater.php";

use OmnivaApi\API;

class AdminOmnivaIntTerminalsController extends AdminOmnivaIntBaseController
{

    const IDENTIFIER_MAPPINGS = [
        'omniva' => 'Omniva',
        'lp_express' => 'LP Express'
    ];

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
        $identifiers = Db::getInstance()->executeS("SELECT DISTINCT `identifier` FROM " . _DB_PREFIX_ . "omniva_int_terminal");
        $identifiers = array_map(function($identifier) {
            return $identifier['identifier'];
        }, $identifiers);

        $identifiers_trans = array_map(function($identifier) {
            return $this->transTerminalIdentifier($identifier);
        }, $identifiers);

        $terminal_identifiers = array_combine($identifiers, $identifiers_trans);

        $this->fields_list = array(
            'name' => array(
                'title' => $this->module->l('Name'),
                'align' => 'text-center',
                'filter_key' => 'a!name'
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
            'comment' => array(
                'title' => $this->module->l('Comment'),
                'type' => 'text',
            ),
            'identifier' => array(
                'title' => $this->module->l('Identifier'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!identifier',
                'list' => $terminal_identifiers,
                'callback' => 'transTerminalIdentifier'
            ),
        );

        $this->bulk_actions = [];
    }

    public function transTerminalIdentifier($identifier)
    {
        return isset(self::IDENTIFIER_MAPPINGS[$identifier]) ? self::IDENTIFIER_MAPPINGS[$identifier] : $identifier;
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
        $updater = new OmnivaIntUpdater('terminals');
        if($updater->run())
            $this->confirmations[] = $this->trans('Successfully updated terminals', array(), 'Admin.Notifications.Error');
        else
            $this->errors[] = $this->trans("Failed updating terminals", array(), 'Admin.Notifications.Error');
    }
    
}