<?php

require_once "AdminOmnivaIntBaseController.php";
require_once __DIR__ . "/../../classes/OmnivaIntServiceCategory.php";

use OmnivaApi\API;

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
        $this->_orderBy = 'id';
        $this->className = 'OmnivaIntService';
        $this->table = 'omniva_int_service';
        $this->identifier = 'id';
        $this->override_folder = _PS_MODULE_DIR_ . $this->module->name . '/views/admin/';
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

        $this->actions = array('manageCategories');
    }

        /**
     * Display edit action link.
     */
    public function displayManageCategoriesLink($token, $id, $name = null)
    {
        if (!array_key_exists('Manage Categories', self::$cache_lang)) {
            self::$cache_lang['Manage Categories'] = Context::getContext()->getTranslator()->trans('Manage Categories', [], 'Admin.Actions');
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&action=categories&token=' . $this->token . '&id=' . $id,
            'action' => Context::getContext()->getTranslator()->trans('Manage Categories', array(), 'Admin.Actions'),
            'id' => $id,
        ));

        return $this->module->fetch('module:' . $this->module->name . '/views/templates/admin/list_category_action.tpl');
    }

    // Added fictive button to so that counter would be displayed in list header (check list-header.tpl #144-145)
    public function initToolbar()
    {
        $this->toolbar_btn['bogus'] = [
            'href' => '#',
            'desc' => $this->trans('Back to list'),
        ];
    }

    public function formatImage($image)
    {
        return "<img src='$image'></img>";
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['sync_services'] = [
            'href' => self::$currentIndex . '&sync_services=1&token=' . $this->token . '&cron_token=' . Configuration::get('OMNIVA_CRON_TOKEN'),
            'desc' => $this->trans('Update Services'),
            'imgclass' => 'refresh',
        ];
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::getValue('sync_services'))
        {
            $this->updateServices();
        }
    }

    public function updateServices()
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

        $response = $api->listAllServices();
        if($response && !empty($response))
        {
            $result &= Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'omniva_int_service`');
            foreach($response as $service)
            {
                $serviceObj = new OmnivaIntService();
                $serviceObj->name = $service->name;
                $serviceObj->service_code = $service->service_code;
                $serviceObj->image = $service->image;
                $result &= $serviceObj->add();
            }
        }
        if($result)
            $this->confirmations[] = $this->trans('Successfully updated services', array(), 'Admin.Notifications.Error');
        else
            $this->errors[] = $this->trans("Failed updating services", array(), 'Admin.Notifications.Error');
    }

    public function processCategories()
    {
        // $this->processConfirmations();
        $this->display = 'edit';
        $this->loadObject();
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Edit Categories for Service ') . $this->object->name,
                'icon' => 'icon-glass',
            ),
            'input' => array(
                array(
                    'type' => 'categories',
                    'label' => $this->module->l('Carrier Name'),
                    'name' => 'service_categories',
                    'tree' => [
                        'id' => 'categories-tree',
                        'selected_categories' => OmnivaIntServiceCategory::getServiceCategories($this->object->id),
                        'root_category' => 2,
                        'use_search' => true,
                        'use_checkbox' => true,
                    ],
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'action',
                    'value' => 'categories'
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

        if(Tools::getValue('submitAddomniva_int_service'))
        {
            $this->mapServiceToCategories();
        }
    }

    public function mapServiceToCategories()
    {
        $service_categories = Tools::getValue('service_categories');

        // Add whatever is submited (if user didn't change anything, old categories should persist)

        if(!$service_categories)
            $service_categories = [];

        if($this->object)
        {
            $existing_categories = OmnivaIntServiceCategory::getServiceCategories($this->object->id);

            // These categories will be mapped to the service in this process (exists in new set, but not in the old set)
            $selected_categories = array_diff($service_categories, $existing_categories);

            // These categories were un-selected and will be unlinked form the service (does not exist in the new set, but exists in the old one)
            $unselected_categories = array_diff($existing_categories, $service_categories);
            // dump($unselected_categories); die();
            foreach($selected_categories as $service_category)
            {
                $omnivaServiceCategory = new OmnivaIntServiceCategory();
                $omnivaServiceCategory->id_service = $this->object->id;
                $omnivaServiceCategory->id_category = $service_category;
                $omnivaServiceCategory->add();
            }
            foreach($unselected_categories as $unselected_category)
            {
                $omnivaServiceCategoryId = OmnivaIntServiceCategory::getServiceCategoryId($this->object->id, $unselected_category);
                if((int)$omnivaServiceCategoryId > 0)
                {
                    $omnivaServiceCategory = new OmnivaIntServiceCategory($omnivaServiceCategoryId);
                    if(Validate::isLoadedObject($omnivaServiceCategory))
                    {
                        $omnivaServiceCategory->delete();
                    }
                }
            }
            $this->redirect_after = self::$currentIndex . '&conf=4&action=categories&token=' . $this->token . '&id=' . $this->object->id;
        }
    }

    public function initProcess()
    {
        parent::initProcess();
        if(Tools::getValue('submitAddomniva_int_service'))
        {
            $this->setAction('categories');
            $this->display = 'edit';
        }

    }
}