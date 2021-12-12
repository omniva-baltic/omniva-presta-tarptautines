<?php

class AdminOmnivaIntCategories extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    /**
     * AdminOmnivaIntCategories class constructor
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->list_no_link = true;
        $this->className = 'OmnivaIntCategory';
        $this->table = 'omniva_int_category';
        $this->identifier = 'id';
        parent::__construct();

        $this->_select = '';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->content .= $this->displayMenu();
            $this->categoryList();
        }
        parent::init();
    }

    protected function categoryList()
    {
    }

    public function postProcess()
    {
    }
}