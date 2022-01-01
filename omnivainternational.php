<?php

require_once __DIR__ . "/classes/models/OmnivaIntShipment.php";
require_once __DIR__ . "/classes/models/OmnivaIntCarrier.php";
require_once __DIR__ . "/classes/models/OmnivaIntCategory.php";
require_once __DIR__ . "/classes/OmnivaIntDb.php";
require_once __DIR__ . "/classes/OmnivaIntHelper.php";
require_once __DIR__ . "/classes/models/OmnivaIntManifest.php";
require_once __DIR__ . "/classes/models/OmnivaIntService.php";
require_once __DIR__ . "/classes/models/OmnivaIntShipment.php";
require_once __DIR__ . "/classes/models/OmnivaIntTerminal.php";
require_once __DIR__ . "/classes/models/OmnivaIntCountry.php";
require_once __DIR__ . "/classes/models/OmnivaIntCarrierService.php";
require_once __DIR__ . "/classes/proxy/OmnivaIntOffersProvider.php";
require_once __DIR__ . "/vendor/autoload.php";

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use OmnivaApi\API;
use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;

class OmnivaInternational extends CarrierModule
{
    const CONTROLLER_OMNIVA_MAIN = 'AdminOmnivaIntMain';

    const CONTROLLER_OMNIVA_SETTINGS = 'AdminOmnivaIntSettings';

    const CONTROLLER_OMNIVA_CARRIERS = 'AdminOmnivaIntCarriers';

    const CONTROLLER_CATEGORIES = 'AdminOmnivaIntCategories';

    const CONTROLLER_TERMINALS = 'AdminOmnivaIntTerminals';

    const CONTROLLER_OMNIVA_SERVICES = 'AdminOmnivaIntServices';

    const CONTROLLER_OMNIVA_COUNTRIES = 'AdminOmnivaIntCountries';

    /**
     * List of hooks
     */
    protected $_hooks = array(
        'header',
        'displayCarrierExtraContent',
        'updateCarrier',
        'displayAdminOrder',
        'actionValidateStepComplete',
        'actionValidateOrder',
        'actionAdminControllerSetMedia',
        'displayAdminListBefore',
        'actionCarrierProcess',
        'displayAdminOmnivaIntServicesListBefore',
        'displayAdminOmnivaIntTerminalsListBefore',
        'displayAdminOmnivaIntCountriesListBefore'
    );

    /**
     * List of fields keys in module configuration
     */
    public $_configKeys = array(
        'API' => array(
            'token' => 'OMNIVA_TOKEN',
            'test_mode' => 'OMNIVA_INT_TEST_MODE'
        ),
        'SHOP' => array(
            'sender_name' => 'OMNIVA_SENDER_NAME',
            'shop_contact' => 'OMNIVA_SHOP_CONTACT',
            'company_code' => 'OMNIVA_SHOP_COMPANY_CODE',
            'shop_country_code' => 'OMNIVA_SHOP_COUNTRY_CODE',
            'shop_city' => 'OMNIVA_SHOP_CITY',
            'shop_address' => 'OMNIVA_SHOP_ADDRESS',
            'shop_postcode' => 'OMNIVA_SHOP_POSTCODE',
            'shop_phone' => 'OMNIVA_SHOP_PHONE',
            'shop_email' => 'OMNIVA_SHOP_EMAIL',
            'sender_address' => 'OMNIVA_SENDER_ADDRESS',
        ),
    );

    /**
     * Fields names and required
     */
    private function getConfigField($section_id, $config_key)
    {
        if ($section_id == 'SHOP') {
            if($config_key == 'sender_address')
                return array('name' => str_replace('_', ' ', $config_key), 'required' => false);
            return array('name' => str_replace('_', ' ', $config_key), 'required' => true);
        }

        return array('name' => 'ERROR_' . $config_key, 'required' => false);
    }

    public static $_order_states = array(
        'order_state_ready' => array(
            'key' => 'OMNIVA_INT_ORDER_STATE_READY',
            'color' => '#FCEAA8',
            'lang' => array(
                'en' => 'Omniva International shipment ready',
                'lt' => 'Omniva tarptautinė siunta paruošta',
            ),
        ),
        'order_state_error' => array(
            'key' => 'OMNIVA_INT_ORDER_STATE_ERROR',
            'color' => '#F24017',
            'lang' => array(
                'en' => 'Error on Omniva International shipment',
                'lt' => 'Klaida Omniva siuntoje',
            ),
        ),
    );

    public static $_classes = [
        'OmnivaIntShipment',
        'OmnivaIntTerminal',
        'OmnivaIntManifest',
        'OmnivaIntService',
        'OmnivaIntCarrier',
        'OmnivaIntCategory',
        'OmnivaIntRateCache'
    ];

    public $id_carrier;

    public $api;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->name = 'omnivainternational';
        $this->tab = 'shipping_logistics';
        $this->version = '0.0.1';
        $this->author = 'mijora.lt';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6.0', 'max' => '1.7.8');
        $this->bootstrap = true;
        $this->helper = new OmnivaIntHelper();
        $this->api = new API(Configuration::get('OMNIVA_TOKEN'), Configuration::get('OMNIVA_INT_TEST_MODE'));

        parent::__construct();

        $this->displayName = $this->l('Omniva International Shipping');
        $this->description = $this->l('Shipping module for Omniva international delivery method');
        $this->available_countries = array('LT', 'LV', 'EE', 'PL');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function getModuleService($service_name, $id = null)
    {
        $reflection = new \ReflectionClass($service_name);
        if(class_exists($service_name) && in_array($service_name, self::$_classes))
            return $id ? $reflection->newInstanceArgs([$id]) : $reflection->newInstance() ;
        elseif (!class_exists($service_name) && in_array($service_name, self::$_classes))
        {
            require_once __DIR__ . 'classes/' . $service_name . '.php';
            return $id ? $reflection->newInstanceArgs([$id]) : $reflection->newInstance() ;
        }
    }

    /**
     * Module installation function
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        foreach ($this->_hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->_errors[] = $this->l('Failed to install hook') . ' ' . $hook . '.';
                return false;
            }
        }

        if (!$this->createDbTables()) {
            $this->_errors[] = $this->l('Failed to create tables.');
            return false;
        }

        if (!$this->addOrderStates()) {
            $this->_errors[] = $this->l('Failed to order states.');
            return false;
        }

        $this->registerTabs();
        $this->createCategoriesSettings();

        if(!Configuration::get('OMNIVA_CRON_TOKEN'))
        {
            Configuration::updateValue('OMNIVA_CRON_TOKEN', md5(time()));
        }

        return true;
    }

    /**
     * Provides list of Admin controllers info
     *
     * @return array BackOffice Admin controllers
     */
    private function getModuleTabs()
    {
        return array(
            self::CONTROLLER_OMNIVA_MAIN => array(
                'title' => $this->l('Omniva International'),
                'parent_tab' => 'AdminParentModulesSf',
            ),
            self::CONTROLLER_OMNIVA_SETTINGS => array(
                'title' => $this->l('Settings'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ),
            self::CONTROLLER_OMNIVA_CARRIERS => array(
                'title' => $this->l('Carriers'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ),
            self::CONTROLLER_OMNIVA_SERVICES => array(
                'title' => $this->l('Services'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ),
            self::CONTROLLER_CATEGORIES => array(
                'title' => $this->l('Category Settings'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ),
            self::CONTROLLER_TERMINALS => array(
                'title' => $this->l('Terminals'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ),
            self::CONTROLLER_OMNIVA_COUNTRIES => array(
                'title' => $this->l('Countries'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ),
        );
    }

    /**
     * Registers module Admin tabs (controllers)
     */
    private function registerTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true; // Nothing to register
        }

        foreach ($tabs as $controller => $tabData) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $controller;
            $tab->name = array();
            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $tabData['title'];
            }

            $tab->id_parent = Tab::getIdFromClassName($tabData['parent_tab']);
            $tab->module = $this->name;
            if (!$tab->save()) {
                $this->displayError($this->l('Error while creating tab ') . $tabData['title']);
                return false;
            }
        }
        return true;
    }

    /**
     * Add Omniva order states
     */
    private function addOrderStates()
    {

        foreach (self::$_order_states as $os)
        {
            $order_state = (int)Configuration::get($os['key']);
            $order_status = new OrderState($order_state, (int)Context::getContext()->language->id);

            if (!$order_status->id || !$order_state) {
                $orderState = new OrderState();
                $orderState->name = array();
                foreach (Language::getLanguages() as $language) {
                    if (strtolower($language['iso_code']) == 'lt')
                        $orderState->name[$language['id_lang']] = $os['lang']['lt'];
                    else
                        $orderState->name[$language['id_lang']] = $os['lang']['en'];
                }
                $orderState->send_email = false;
                $orderState->color = $os['color'];
                $orderState->hidden = false;
                $orderState->delivery = false;
                $orderState->logable = true;
                $orderState->invoice = false;
                $orderState->unremovable = false;
                if ($orderState->add()) {
                    Configuration::updateValue($os['key'], $orderState->id);
                }
                else
                    return false;
            }
        }
        return true;
    }

    /**
     * Deletes module Admin controllers
     * Used for module uninstall
     *
     * @return bool Module Admin controllers deleted successfully
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function deleteTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true; // Nothing to remove
        }

        foreach (array_keys($tabs) as $controller) {
            $idTab = (int) Tab::getIdFromClassName($controller);
            $tab = new Tab((int) $idTab);

            if (!Validate::isLoadedObject($tab)) {
                continue; // Nothing to remove
            }

            if (!$tab->delete()) {
                $this->displayError($this->l('Error while uninstalling tab') . ' ' . $tab->name);
                return false;
            }
        }

        return true;
    }

    /**
     * Module uninstall function
     */
    public function uninstall()
    {
        $cDb = new OmnivaIntDb();

        $cDb->deleteTables();
        $this->deleteTabs();

        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Get terminals for all countries
     */
    private function updateTerminals()
    {
        $cFiles = new MjvpFiles();
        $cFiles->updateCountriesList();
        $cFiles->updateTerminalsList();
    }

    /**
     * Create module database tables
     */
    public function createDbTables()
    {
        try {
            $cDb = new OmnivaIntDb();

            $result = $cDb->createTables();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function getOrderShippingCost($params, $shipping_cost) {

        $cart = $this->context->cart;
        $carrier = new Carrier($this->id_carrier);
        $carrier_reference = $carrier->id_reference;
        $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($carrier_reference);

        $offersProvider = new OmnivaIntOffersProvider();
        $offersProvider
            ->setCart($cart)
            ->setCarrier($omnivaCarrier)
            ->setModule($this);

        return $offersProvider->getPrice();
    }

    public function getOrderShippingCostExternal($params) {
        return true;
    }

    public function createCategoriesSettings()
    {
        $categories = Category::getSimpleCategories($this->context->language->id);
        foreach($categories as $category)
        {
            $omnivaCategory = new OmnivaIntCategory();
            $omnivaCategory->id = $category['id_category'];
            $omnivaCategory->weight = 0;
            $omnivaCategory->length = 0;
            $omnivaCategory->width = 0;
            $omnivaCategory->height = 0;
            $omnivaCategory->active = 1;
            $omnivaCategory->force_id = true;
            $omnivaCategory->add();
        }   
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::CONTROLLER_OMNIVA_SETTINGS));
    }

    /**
     * Hook for js/css files and other elements in header
     */
    public function hookHeader($params)
    {
        $address = new Address($params['cart']->id_address_delivery);
        $filtered_terminals = $this->getFilteredTerminals();

        $address_query = $address->address1 . ' ' . $address->postcode . ', ' . $address->city;
        Media::addJsDef(array(
                'mjvp_front_controller_url' => $this->context->link->getModuleLink($this->name, 'front'),
                'mjvp_carriers_controller_url' => $this->context->link->getModuleLink($this->name, 'carriers'),
                'address_query' => $address_query,
                'mjvp_translates' => array(
                    'loading' => $this->l('Loading'),
                ),
                'images_url' => $this->_path . 'views/images/',
                'mjvp_terminal_select_translates' => array(
                'modal_header' => $this->l('Pickup points map'),
                'terminal_list_header' => $this->l('Pickup points list'),
                'seach_header' => $this->l('Search around'),
                'search_btn' => $this->l('Find'),
                'modal_open_btn' => $this->l('Select a pickup point'),
                'geolocation_btn' => $this->l('Use my location'),
                'your_position' => $this->l('Distance calculated from this point'),
                'nothing_found' => $this->l('Nothing found'),
                'no_cities_found' => $this->l('There were no cities found for your search term'),
                'geolocation_not_supported' => $this->l('Geolocation is not supported'),
                'select_pickup_point' => $this->l('Select a pickup point'),
                'search_placeholder' => $this->l('Enter postcode/address'),
                'workhours_header' => $this->l('Workhours'),
                'contacts_header' => $this->l('Contacts'),
                'no_pickup_points' => $this->l('No points to select'),
                'select_btn' => $this->l('select'),
                'back_to_list_btn' => $this->l('reset search'),
                'no_information' => $this->l('No information'),
                ),
                'mjvp_terminals' => $filtered_terminals
            )
        );
    }

    public function getFilteredTerminals() 
    {
        return [];
    }


    /**
     * Hook to display block in Prestashop order edit
     */
    public function hookDisplayAdminOrder($params)
    {
    }

    public function hookActionValidateStepComplete($params)
    {
    }

    // Separate method, as methods of getting a checkout step on 1.7 are inconsistent among minor versions.
    public function check17PaymentStep($cart)
    {
        if(version_compare(_PS_VERSION_, '1.7', '>'))
        {
            $rawData = Db::getInstance()->getValue(
                'SELECT checkout_session_data FROM ' . _DB_PREFIX_ . 'cart WHERE id_cart = ' . (int) $cart->id
            );
            $data = json_decode($rawData, true);
            if (!is_array($data)) {
                $data = [];
            }
            // Do not add this module extra content, if it is payment step to avoid conflicts with venipakcod.
            if((isset($data['checkout-delivery-step']) && $data['checkout-delivery-step']['step_is_complete']) &&
                (isset($data['checkout-payment-step']) && !$data['checkout-payment-step']['step_is_complete'])
            )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Hook to display content on carrier in checkout page
     */
    public function hookDisplayCarrierExtraContent($params)
    {
    }

    /**
     * Mass label printing
     */
    public function bulkActionPrintLabels($orders_ids)
    {
    }

    private function showErrors($errors)
    {
       foreach ($errors as $key => $error) {
            $this->context->controller->errors[$key] = $error;
        } 
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $cart = $params['cart'];
        $id_order = $order->id;
        $id_cart = $cart->id;

        $carrier = new Carrier($cart->id_carrier);
        $carrier_reference = $carrier->id_reference;
        if(!in_array($carrier_reference, Configuration::getMultiple([self::$_carriers['courier']['reference_name'], self::$_carriers['pickup']['reference_name']])))
            return;

        $cDb = new OmnivaIntDb();
        $check_order_id = $cDb->getOrderIdByCartId($id_cart);

        if (empty($check_order_id)) {

            $order_weight = $order->getTotalWeight();

            // Convert to kg, if weight is in grams.
            if(Configuration::get('PS_WEIGHT_UNIT') == 'g')
                $order_weight *= 0.001;

            $is_cod = 0;
            if(in_array($order->module, self::$_codModules))
                $is_cod = 1;

             $cDb->updateOrderInfo($id_cart, array(
                 'id_order' => $id_order,
                 'warehouse_id' => MjvpWarehouse::getDefaultWarehouse(),
                 'order_weight' => $order_weight,
                 'cod_amount' => $order->total_paid_tax_incl,
                 'is_cod' => $is_cod
             ));
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        if(Tools::getValue('configure') == $this->name)
        {
            $this->context->controller->addJs('modules/' . $this->name . '/views/js/mjvp-admin.js');
            $this->context->controller->addCSS($this->_path . 'views/css/mjvp-admin.css');
        }
    }

    public function getTerminalById($terminals, $terminal_id)
    {
        foreach ($terminals as $terminal)
        {
            if ($terminal->id == $terminal_id)
            {
                return $terminal;
            }
        }
        return false;
    }

    public function changeOrderStatus($id_order, $status)
    {
        $order = new Order((int)$id_order);
        if ($order->current_state != $status)
        {
            $history = new OrderHistory();
            $history->id_order = (int)$id_order;
            $history->id_employee = Context::getContext()->employee->id;
            $history->changeIdOrderState((int)$status, $order);
            $order->update();
            $history->add();
        }
    }

    /**
     * Use hook to validate carrier selection in Prestashop 1.6
     */
    public function hookActionCarrierProcess($params)
    {
        $data = [
            'step_name' => 'delivery',
            'cart' => $params['cart']
        ];
        $this->hookActionValidateStepComplete($data);
    }

        /**
     * Get config key from all keys list
     */
    public function getConfigKey($key_name, $section = '')
    {
        return $this->_configKeys[$section][$key_name] ?? '';
    }

    public function hookDisplayAdminOmnivaIntServicesListBefore($params)
    {
        $link = $this->context->link->getModuleLink($this->name, 'cron', ['type' => 'services', 'token' => Configuration::get('OMNIVA_CRON_TOKEN')]);
        $content = $this->l("To udate services periodically, add this CRON job to your cron table: ");

        // Something not-translatable, usually a link..
        $sugar = "<b><i>$link</i></b>";

        return $this->helper->displayAlert($content, $sugar);
    }

    public function hookDisplayAdminOmnivaIntTerminalsListBefore($params)
    {
        $link = $this->context->link->getModuleLink($this->name, 'cron', ['type' => 'terminals', 'token' => Configuration::get('OMNIVA_CRON_TOKEN')]);
        $content = $this->l("To udate terminals periodically, add this CRON job to your cron table: ");

        $sugar = "<b><i>$link</i></b>";

        return $this->helper->displayAlert($content, $sugar);
    }

    public function hookDisplayAdminOmnivaIntCountriesListBefore($params)
    {
        $link = $this->context->link->getModuleLink($this->name, 'cron', ['type' => 'countries', 'token' => Configuration::get('OMNIVA_CRON_TOKEN')]);
        $content = $this->l("To udate countries periodically, add this CRON job to your cron table: ");

        $sugar = "<b><i>$link</i></b>";

        return $this->helper->displayAlert($content, $sugar);
    }
}
