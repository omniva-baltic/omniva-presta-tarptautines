<?php

require_once "AdminOmnivaIntBaseController.php";

class AdminOmnivaIntCarriersController extends AdminOmnivaIntBaseController
{
    /**
     * AdminOmnivaIntCategories class constructor
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
        $this->title_icon = 'icon-truck';
        $this->list_no_link = true;
        $this->bootstrap = true;
        $this->_orderBy = 'id';
        $this->className = 'OmnivaIntCarrier';
        $this->table = 'omniva_int_carrier';
        $this->identifier = 'id';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->carrierList();
        }
        parent::init();
    }

    protected function carrierList()
    {
        $this->fields_list = array(
            'name' => array(
                'title' => $this->module->l('Name'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'c!name'
            ),
            'service' => array(
                'type' => 'text',
                'title' => $this->module->l('Service'),
                'align' => 'center',
                'filter_key' => 'ois!name'
            ),
            'price_type' => array(
                'title' => $this->module->l('Price Type'),
                'align' => 'center',
            ),
            'price' => array(
                'title' => $this->module->l('Price'),
                'align' => 'center',
            ),
            'free_shipping' => array(
                'type' => 'numer',
                'title' => $this->module->l('Free Shipping'),
                'align' => 'center',
            ),
            'select_fastest' => array(
                'type' => 'text',
                'title' => $this->module->l('Price method'),
                'align' => 'center',
                'callback' => 'fastestOrCheapest'
            ),
        );
    }

    public function fastestOrCheapest($select_fastest)
    {
        if($select_fastest)
            return $this->module->l('Fastest');
        else
            return $this->module->l('Cheapest');
    }

    public function renderForm()
    {
        $this->table = 'omniva_int_carrier';
        $this->identifier = 'id';

        $switcher_values = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Yes')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('No')
            )
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Omniva International Carrier'),
                'icon' => 'icon-truck',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Carrier Name'),
                    'name' => 'carrier_name',
                    'required' => true,
                    'col' => '3',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Services'),
                    'name' => 'service',
                    'required' => true,
                    'col' => '3',
                    'desc' => $this->module->l('Select all services which will be used by this carrier'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Width'),
                    'name' => 'width',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->module->l('Enter default category item width'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Height'),
                    'name' => 'height',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->module->l('Enter default category item height'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'desc' => $this->l('Activate/disable this category settings.'),
                    'values' => $switcher_values
                ),
            ),
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->module->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->fields_form['submit'] = array(
            'title' => $this->module->l('Save'),
        );

        return parent::renderForm();
    }
}