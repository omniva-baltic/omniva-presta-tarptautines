<?php

require_once "AdminOmnivaIntBaseController.php";

use Siusk24LT\API;

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
}