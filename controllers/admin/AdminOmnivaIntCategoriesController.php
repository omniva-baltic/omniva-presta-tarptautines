<?php

class AdminOmnivaIntCategoriesController extends ModuleAdminController
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
        $this->_orderBy = 'id';
        $this->className = 'OmnivaIntCategory';
        $this->table = 'omniva_int_category';
        $this->identifier = 'id';
        parent::__construct();

        $this->_select = " CONCAT(a.weight, ' ', 'kg') as weight,
            CONCAT(a.width, ' x ', a.length, ' x ', a.height) as measures,
            cl.name as name, cl2.name as parent";

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = a.`id_category`)
            LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON (c.`id_category` = a.`id_category`)
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl2 ON (cl2.`id_category` = c.`id_parent`)
    ';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->categoryList();
        }
        parent::init();
    }

    protected function categoryList()
    {
        $this->fields_list = array(
            'name' => array(
                'title' => $this->module->l('Title'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'cl!name',
            ),
            'parent' => array(
                'type' => 'text',
                'title' => $this->module->l('Parent'),
                'align' => 'center',
                'filter_key' => 'cl2!name',
            ),
            'weight' => array(
                'title' => $this->module->l('Weight'),
                'type' => 'text',
            ),
            'measures' => array(
                'title' => $this->module->l('Measures'),
                'type' => 'text',
                'havingFilter' => true,
            ),
            'active' => array(
                'type' => 'bool',
                'title' => $this->module->l('Active'),
                'active' => 'status',
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ),
        );

        $this->actions = array('edit', 'delete');
    }
        /**
     * Change object status (active, inactive).
     *
     * @return ObjectModel|false
     *
     * @throws PrestaShopException
     */
    public function processStatus()
    {
        $id_omniva_category = (int) Tools::getValue('id');
        $current_status = (int) Db::getInstance()->getValue('SELECT `active` FROM ' . _DB_PREFIX_ . 'omniva_int_category WHERE `id` = ' . $id_omniva_category);
        $result = Db::getInstance()->update('omniva_int_category', ['active' => !$current_status], 'id = ' . $id_omniva_category);

        return $result;
    }

    public function renderForm()
    {
        $this->table = 'omniva_int_category';
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
                'title' => $this->module->l('Category Settings'),
                'icon' => 'icon-info-sign',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Weight'),
                    'name' => 'weight',
                    'required' => true,
                    'col' => '3',
                    'hint' => $this->module->l('Enter default category item weight'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Length'),
                    'name' => 'length',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->module->l('Enter default category item length'),
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