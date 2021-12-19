<?php

require_once "AdminOmnivaIntBaseController.php";

class AdminOmnivaIntServicesController extends AdminOmnivaIntBaseController
{
    /**
     * AdminOmnivaIntCategories class constructor
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
        $this->title_icon = 'icon-server';
        $this->list_no_link = true;
        $this->bootstrap = true;
        $this->_orderBy = 'id';
        $this->icon = 'wheel';
        $this->className = 'OmnivaIntService';
        $this->table = 'omniva_int_service';
        $this->identifier = 'id';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->serviceList();
        }
        parent::init();
    }

    protected function serviceList()
    {
        $this->fields_list = array(
            'name' => array(
                'title' => $this->module->l('Name'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'name'
            ),
            'service_code' => array(
                'type' => 'text',
                'title' => $this->module->l('Service Code'),
                'align' => 'center',
            ),
            'image' => array(
                'title' => $this->module->l('Image'),
                'callback' => 'formatImage',
                'align' => 'center',
                'search' => false
            ),
            'insurance' => array(
                'type' => 'bool',
                'title' => $this->module->l('Insurance'),
                'align' => 'center',
            ),
            'return' => array(
                'type' => 'bool',
                'title' => $this->module->l('Return'),
                'align' => 'center',
            ),
            'carry_service' => array(
                'type' => 'bool',
                'title' => $this->module->l('Carry Service'),
                'align' => 'center',
            ),
            'doc_return' => array(
                'type' => 'bool',
                'title' => $this->module->l('Document Return'),
                'align' => 'center',
            ),
        );
    }

    public function initToolbar()
    {
    }

    public function formatImage($image)
    {
        return "<img src='$image'></img>";
    }
}