<?php

require_once "AdminOmnivaIntBaseController.php";

class AdminOmnivaIntCategoriesController extends AdminOmnivaIntBaseController
{
    /**
     * AdminOmnivaIntCategories class constructor
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->list_no_link = true;
        $this->title_icon = 'icon-moon-o';
        $this->_orderBy = 'id_category';
        $this->className = 'OmnivaIntCategory';
        $this->table = 'omniva_int_category';
        $this->identifier = 'id_category';
        parent::__construct();

        $this->_select = " CONCAT(a.weight, ' ', 'kg') as weight,
            CONCAT(a.width, ' cm x ', a.length, ' cm x ', a.height, ' cm') as measures,
            cl.name as name, cl2.name as parent";

        $this->_join = '
            INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = a.`id_category`)
            INNER JOIN `' . _DB_PREFIX_ . 'category` c ON (c.`id_category` = a.`id_category`)
            INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl2 ON (cl2.`id_category` = c.`id_parent`)
    ';

        $this->_where = ' AND cl.id_lang = ' . $this->context->language->id . 
                        ' AND cl2.id_lang = ' . $this->context->language->id;
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
        $this->fields_list = [
            'name' => [
                'title' => $this->module->l('Title'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'cl!name',
            ],
            'parent' => [
                'type' => 'text',
                'title' => $this->module->l('Parent'),
                'align' => 'center',
                'filter_key' => 'cl2!name',
            ],
            'weight' => [
                'title' => $this->module->l('Weight'),
                'type' => 'text',
                'align' => 'center',
            ],
            'measures' => [
                'title' => $this->module->l('Measures'),
                'type' => 'text',
                'align' => 'center',
                'havingFilter' => true,
            ],
            'active' => [
                'type' => 'bool',
                'title' => $this->module->l('Active'),
                'active' => 'status',
                'align' => 'center',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ],
        ];

        $this->actions = ['edit', 'delete'];
    }

    public function renderForm()
    {
        $this->table = 'omniva_int_category';
        $this->identifier = 'id_category';

        $switcher_values = [
            [
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Yes')
            ],
            [
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('No')
            ]
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Category Settings'),
                'icon' => 'icon-info-sign',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Weight'),
                    'name' => 'weight',
                    'required' => true,
                    'col' => '3',
                    'hint' => $this->module->l('Enter default category item weight'),
                    'prefix' => 'kg'
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Length'),
                    'name' => 'length',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->module->l('Enter default category item length'),
                    'prefix' => 'cm'
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Width'),
                    'name' => 'width',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->module->l('Enter default category item width'),
                    'prefix' => 'cm'
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Height'),
                    'name' => 'height',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->module->l('Enter default category item height'),
                    'prefix' => 'cm'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'desc' => $this->l('Activate/disable this category settings.'),
                    'values' => $switcher_values
                ],
            ],
        ];

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = [
                'type' => 'shop',
                'label' => $this->module->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            ];
        }

        $this->fields_form['submit'] = [
            'title' => $this->module->l('Save'),
        ];

        return parent::renderForm();
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
        $this->page_header_toolbar_btn['sync_categories'] = [
            'href' => self::$currentIndex . '&sync_categories=1&token=' . $this->token,
            'desc' => $this->module->l('Sync Categories'),
            'imgclass' => 'refresh',
            'color' => 'green'
        ];
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::getValue('sync_categories'))
        {
            $this->syncOmnivaCategories();
        }
    }

    // Adds missing categories to the Omniva categories settings list.
    public function syncOmnivaCategories()
    {
        $categories = Category::getSimpleCategories($this->context->language->id);
        $omnivaCategoriesObj = (new PrestaShopCollection('OmnivaIntCategory'))->getResults();
        $omnivaCategories = array_map(function($omnivaCategory) {
            return $omnivaCategory->id;
        }, $omnivaCategoriesObj);

        foreach($categories as $category)
        {
            if(!in_array($category['id_category'], $omnivaCategories))
            {
                $omnivaCategory = new OmnivaIntCategory();
                $omnivaCategory->id = $category['id_category'];

                $prestaCategory = new Category($category['id_category']);
                $omnivaCategoryParent = new OmnivaIntCategory($prestaCategory->id_parent);

                $omnivaCategory->weight = $omnivaCategoryParent->weight;
                $omnivaCategory->length = $omnivaCategoryParent->length;
                $omnivaCategory->width = $omnivaCategoryParent->width;
                $omnivaCategory->height = $omnivaCategoryParent->height;
                $omnivaCategory->active = 1;
                $omnivaCategory->force_id = true;
                $omnivaCategory->add();
            }
        } 
        $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;  
    }

    public function processUpdate()
    {
        $this->object = parent::processUpdate();
        $category = new Category($this->object->id);
        $childCategories = $category->getAllChildren()->getResults();
        $updatedCategory = $this->object;

        // If updated category has any children, they inherit measures from parent, if child measure is 0.
        foreach($childCategories as $category)
        {
            $omnivaCategory = new OmnivaIntCategory($category->id);
            if(Validate::isLoadedObject($omnivaCategory))
            {
                $omnivaCategory->weight = $omnivaCategory->weight == 0 ? $updatedCategory->weight : $omnivaCategory->weight;
                $omnivaCategory->length = $omnivaCategory->length == 0 ? $updatedCategory->length : $omnivaCategory->length;
                $omnivaCategory->width = $omnivaCategory->width == 0 ? $updatedCategory->width : $omnivaCategory->width;
                $omnivaCategory->height = $omnivaCategory->height == 0 ? $updatedCategory->height :  $omnivaCategory->height;
                $omnivaCategory->update();
            }
        }
    }
}