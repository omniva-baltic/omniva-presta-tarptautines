<?php

require_once __DIR__ . "/classes/OmnivaIntDb.php";

require_once __DIR__ . "/classes/models/OmnivaIntCarrier.php";
require_once __DIR__ . "/classes/models/OmnivaIntCategory.php";
require_once __DIR__ . "/classes/models/OmnivaIntManifest.php";
require_once __DIR__ . "/classes/models/OmnivaIntService.php";
require_once __DIR__ . "/classes/models/OmnivaIntOrder.php";
require_once __DIR__ . "/classes/models/OmnivaIntTerminal.php";
require_once __DIR__ . "/classes/models/OmnivaIntCountry.php";
require_once __DIR__ . "/classes/models/OmnivaIntCarrierService.php";
require_once __DIR__ . "/classes/models/OmnivaIntParcel.php";
require_once __DIR__ . "/classes/models/OmnivaIntCartTerminal.php";
require_once __DIR__ . "/classes/models/OmnivaIntRateCache.php";
require_once __DIR__ . "/classes/models/OmnivaIntServiceCategory.php";

require_once __DIR__ . "/classes/proxy/OmnivaIntUpdater.php";
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
    const CONTROLLER_OMNIVA_ORDER = 'AdminOmnivaIntOrder';

    /**
     * List of hooks
     */
    protected $_hooks = [
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
        'displayAdminOmnivaIntCountriesListBefore',
        'displayBeforeCarrier'
    ];

    /**
     * List of fields keys in module configuration
     */
    public $_configKeys = [
        'API' => [
            'token' => 'OMNIVA_TOKEN',
            'test_mode' => 'OMNIVA_INT_TEST_MODE'
        ],
        'SHOP' => [
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
        ],
    ];

    /**
     * Fields names and required
     */
    private function getConfigField($section_id, $config_key)
    {
        if ($section_id == 'SHOP') {
            if($config_key == 'sender_address')
                return ['name' => str_replace('_', ' ', $config_key), 'required' => false];
            return ['name' => str_replace('_', ' ', $config_key), 'required' => true];
        }

        return ['name' => 'ERROR_' . $config_key, 'required' => false];
    }

    public static $_order_states = [
        'order_state_ready' => [
            'key' => 'OMNIVA_INT_ORDER_STATE_READY',
            'color' => '#FCEAA8',
            'lang' => [
                'en' => 'Omniva International shipment ready',
                'lt' => 'Omniva tarptautinė siunta paruošta',
            ],
        ],
        'order_state_error' => [
            'key' => 'OMNIVA_INT_ORDER_STATE_ERROR',
            'color' => '#F24017',
            'lang' => [
                'en' => 'Error on Omniva International shipment',
                'lt' => 'Klaida Omniva siuntoje',
            ],
        ],
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
        $this->version = '0.8.0';
        $this->author = 'mijora.lt';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6.0', 'max' => '1.7.9'];
        $this->bootstrap = true;
        if(Configuration::get('OMNIVA_TOKEN'))
            $this->api = new API(Configuration::get('OMNIVA_TOKEN'), Configuration::get('OMNIVA_INT_TEST_MODE'));

        parent::__construct();

        $this->displayName = $this->l('Omniva International Shipping');
        $this->description = $this->l('Shipping module for Omniva international delivery method');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
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
        return [
            self::CONTROLLER_OMNIVA_MAIN => [
                'title' => $this->l('Omniva International'),
                'parent_tab' => 'AdminParentModulesSf',
            ],
            self::CONTROLLER_OMNIVA_SETTINGS => [
                'title' => $this->l('Settings'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
            self::CONTROLLER_OMNIVA_CARRIERS => [
                'title' => $this->l('Carriers'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
            self::CONTROLLER_OMNIVA_SERVICES => [
                'title' => $this->l('Services'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
            self::CONTROLLER_CATEGORIES => [
                'title' => $this->l('Category Settings'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
            self::CONTROLLER_TERMINALS => [
                'title' => $this->l('Terminals'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
            self::CONTROLLER_OMNIVA_COUNTRIES => [
                'title' => $this->l('Countries'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
            self::CONTROLLER_OMNIVA_ORDER => [
                'title' => $this->l('Omniva Orders'),
                'parent_tab' => self::CONTROLLER_OMNIVA_MAIN,
            ],
        ];
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
            $tab->name = [];
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
                $orderState->name = [];
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

    // It's important to cache returned prices, because API may timeout on repeated requests.
    public function getOrderShippingCost($params, $shipping_cost) {
        if($this->context->controller instanceof AdminController)
            return false;
        $cart = $this->context->cart;
        $carrier = new Carrier($this->id_carrier);
        $carrier_reference = $carrier->id_reference;
        $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($carrier_reference);
        if(Validate::isLoadedObject($omnivaCarrier) && $omnivaCarrier->active && Validate::isLoadedObject($cart))
        {
            // Check if rate is already cached. Use id_cart also to reduce possibility of hash collision.
            $cache_key_hash = $this->getCacheKey($cart, $omnivaCarrier);
            $rate = OmnivaIntRateCache::getCachedRate($cart->id, $cache_key_hash);

            // Check against false, as 0 is a valid value.
            if($rate == -1)
                return false;
            elseif($rate !== false)
                return $rate;

            $offersProvider = new OmnivaIntOffersProvider();
            $offersProvider
                ->setType($omnivaCarrier->type)
                ->setCart($cart)
                ->setCarrier($omnivaCarrier)
                ->setModule($this);

            $rate = $offersProvider->getPrice();

            $rateCache = new OmnivaIntRateCache();
            $rateCache->id_cart = $cart->id;
            $rateCache->hash = $cache_key_hash;

            // We want to cache the unavailability of carrier too. In such case, the "false" is converted to -1, because otherwise false will be
            // cast to 0 in DB, but 0 does not suggest that carrier is unavailable, but rather that it is free.
            $rateCache->rate = $rate !== false ? $rate : -1;
            
            $rateCache->add();

            return $rate;
        }
        return false;
    }

    // Cache key structure - hash of: {id_customer}-{id_cart}-{id_carrier}-{id_address}-{id_country}-{postcode}-({cart_product_id}-{quantity})
    // + all OmnivaIntCarrier fields, following the same pattern,  if old cart is used, but carrier was changed in the abandonment period.
    public function getCacheKey($cart, $omnivaCarrier)
    {
        $cache_key = '';
        $customer = new Customer($cart->id_customer);
        $address = new Address($cart->id_address_delivery);

        $id_customer = $customer->id;
        $id_cart = $cart->id;
        $id_carrier = $omnivaCarrier->id;
        $id_address = $address->id;
        $id_country = $address->id_country;
        $postcode = $address->postcode;

        $cache_key = "$id_customer-$id_cart-$id_carrier-$id_address-$id_country-$postcode";
        $cart_products = $cart->getProducts();
        foreach ($cart_products as $product)
        {
            $cache_key .= $product['id_product'] . '-' . $product['cart_quantity'];
        }

        // OmnivaIntCarrier fields..
        $cache_key .= $omnivaCarrier->price_type . "-" . $omnivaCarrier->price . "-" . 
                      $omnivaCarrier->free_shipping . "-" . $omnivaCarrier->cheapest . "-" . $omnivaCarrier->type;
        // ..and all it's services 
        $cache_key .= implode('-', OmnivaIntCarrierService::getCarrierServices($omnivaCarrier->id));

        return md5($cache_key);
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

    public function hookHeader($params)
    {
      if (in_array(Context::getContext()->controller->php_self, ['order-opc', 'order'])) {

        Media::addJsDef([
            'omniva_front_controller_url' => $this->context->link->getModuleLink($this->name, 'front')
        ]);
        if(version_compare(_PS_VERSION_, '1.7', '>='))
        {
            $this->context->controller->registerJavascript(
                'int-leaflet',
                'modules/' . $this->name . '/views/js/leaflet.js',
                ['priority' => 190]
              );
              $this->context->controller->registerJavascript(
                'omnivalt-int',
                'modules/' . $this->name . '/views/js/omniva.js',
                [
                  'priority' => 200,
                ]
              );
        
        }
        else
        {
            $this->context->controller->addJS('modules/' . $this->name . '/views/js/leaflet.js');
            $this->context->controller->addJS('modules/' . $this->name . '/views/js/omniva.js');
        }
        $this->context->controller->addCSS('modules/' . $this->name . '/views/css/leaflet.css');
        $this->context->controller->addCSS('modules/' . $this->name . '/views/css/omniva.css');
  
        $this->smarty->assign(array(
          'module_url' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        ));
  
        return $this->display(__FILE__, 'header.tpl');
      }
    }


    /**
     * Hook to display block in Prestashop order edit
     */
    public function hookDisplayAdminOrder($params)
    {
        $id_order = Tools::getValue('id_order');
        $order = new Order($id_order);

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

        if(Validate::isLoadedObject($order))
        {
            $orderCarrier = new Carrier($order->id_carrier);

            if($orderCarrier->external_module_name == $this->name)
            {
                $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($orderCarrier->id_reference);
                $omnivaOrder = new OmnivaIntOrder($id_order);
                $this->context->smarty->assign('admin_default_tpl_path', _PS_BO_ALL_THEMES_DIR_ . 'default/template/');

                $form_fields = [];
                if($omnivaCarrier->type == 'terminal')
                {
                    $address = new Address($order->id_address_delivery);
                    $country_code = Country::getIsoById($address->id_country);
                    $service = OmnivaIntService::getServiceByCode($omnivaOrder->service_code);
                    $cities = OmnivaIntTerminal::getTerminalsByIsoAndIndentifier($country_code, $service->parcel_terminal_type, false, 'city');
                    
                    $terminalsByCities = [];
                    foreach($cities as $key => $city)
                    {
                        $terminalsByCities[$key]['city'] = $city['city'];
                        $terminalsByCities[$key]['terminals'] = OmnivaIntTerminal::getTerminalsByIsoAndIndentifier($country_code, $service->parcel_terminal_type, $city['city']);
                    }

                    if(!empty($terminalsByCities))
                    {
                        $form_fields[] = [
                            'type' => 'select',
                            'label' => $this->l('Terminal'),
                            'name' => 'terminal',
                            'options' => [
                                'optiongroup' => [
                                    'query' => $terminalsByCities,
                                    'label' => 'city',
                                ],
                                'options' => [
                                    'query' => 'terminals',
                                    'name' => 'name',
                                    'id' => 'id'
                                ]
                            ],
                            'required' => true
                        ];
    
                        $cartTerminal = new OmnivaIntCartTerminal($order->id_cart);
                        $id_terminal = $cartTerminal->id_terminal;
                    }
                }

                $form_fields_services = [
                    [
                        'type' => 'switch',
                        'label' => $this->l('C.O.D'),
                        'name' => 'cod',
                        'values' => $switcher_values
                    ],
                    [
                        'form_group_class' => 'cod-amount-block',
                        'type' => 'text',
                        'label' => $this->l('C.O.D amount'),
                        'name' => 'cod_amount',
                        'prefix' => '€'
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Insurance'),
                        'name' => 'insurance',
                        'values' => $switcher_values
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Carry service'),
                        'name' => 'carry_service',
                        'values' => $switcher_values
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Document Return'),
                        'name' => 'doc_return',
                        'values' => $switcher_values
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Fragile'),
                        'name' => 'fragile',
                        'values' => $switcher_values
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_order',
                        'value' => $id_order,
                    ],
                ];

                $form_fields = array_merge($form_fields, $form_fields_services);
                $fieldsForm[0]['form'] = [
                    'legend' => [
                        'title' => 'Omniva International Shipment',
                    ],
                    'input' => $form_fields,
                    'buttons' => [
                        [
                            'title' => $this->l('Save'),
                            'class' => 'btn btn-primary',
                            'id' => 'save-shipment'
                        ],
                        [
                            'title' => $this->l('Send Shipment'),
                            'class' => 'btn btn-success',
                            'id' => 'send-shipment'
                        ],
                    ]
                ];

                $omnivaOrderParcels = OmnivaIntParcel::getParcelsByOrderId($id_order);
                $untrackedParcelsCount = OmnivaIntParcel::getCountUntrackedParcelsByOrderId($id_order);
                if($omnivaOrder->shipment_id && $untrackedParcelsCount > 0)
                {
                    // Just catch the exception, because it is thrown, if order is not yet ready, i.e gives error "Your order is being generated, please try again later"
                    try {
                        $orderTrackingInfo = $this->api->getLabel($omnivaOrder->shipment_id);
                        if($orderTrackingInfo && isset($orderTrackingInfo->tracking_numbers))
                        {
                            $this->changeOrderStatus($id_order, Configuration::get(self::$_order_states['order_state_ready']['key']));
                            foreach($omnivaOrderParcels as $key => $id_parcel)
                            {
                                $omnivaParcel = new OmnivaIntParcel($id_parcel);
                                $omnivaParcel->setFieldsToUpdate(['tracking_number' => true]);
                                $omnivaParcel->tracking_number = $orderTrackingInfo->tracking_numbers[$key];
                                $omnivaParcel->update();
                            }
                        }
                        else
                        {
                            $this->changeOrderStatus($id_order, Configuration::get(self::$_order_states['order_state_error']['key']));
                        }
                        // for debugging
                    } catch (Exception $e) {
                        sleep(0);
                    }
                }

                $helper = new HelperForm();
                $helper->fields_value = [
                    'id_order' => $id_order,
                    'cod' => $omnivaOrder->cod,
                    'cod_amount' => $omnivaOrder->cod_amount,
                    'insurance' => $omnivaOrder->insurance,
                    'carry_service' => $omnivaOrder->carry_service,
                    'doc_return' => $omnivaOrder->doc_return,
                    'fragile' => $omnivaOrder->fragile,
                    'terminal' => isset($id_terminal) ? $id_terminal : null,
                ];
        
                // Module, token and currentIndex
                $helper->module = $this;
                $helper->table = 'omniva_shipment';
                // $helper->bootstrap = true;
                $helper->name_controller = $this->name;
        
                // Title and toolbar
                $helper->title = $this->displayName;
                $helper->show_toolbar = true;        // false -> remove toolbar
                $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
                $helper->submit_action = 'submit' . $this->name . 'shipment';

                $this->context->smarty->assign([
                    'form' => $helper->generateForm($fieldsForm),
                ]);

                $tracking_numbers = OmnivaIntParcel::getTrackingNumbersByOrderId($id_order);
                // Check if order has manifest. If it does not - give option to cancel order.
                $orderHasManifest = OmnivaIntManifest::checkManifestExists($omnivaOrder->cart_id);
                $this->context->smarty->assign('orderHasManifest', $orderHasManifest);

                // Assign this one separatly, otherwise tracking_codes.tpl is does not see it.
                $this->context->smarty->assign([
                    'shipment_id' => $omnivaOrder->shipment_id,
                    'tracking_numbers' => $tracking_numbers,
                    'omniva_admin_order_link' => $this->context->link->getAdminLink('AdminOmnivaIntOrder') . '&submitPrintLabels=1&action=printLabels&id_order=' . $id_order
                ]);
                $this->context->smarty->assign([

                    'list' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name .'/views/templates/admin/tracking_codes.tpl')
                ]);
                
                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name .'/views/templates/admin/displayAdminOrder.tpl');
            }
        }
    }

    public function hookActionValidateStepComplete($params)
    {
        $cart = $params['cart'];
        if($params['step_name'] != 'delivery' || !$cart->id_carrier)
            return true;
        $carrier = new Carrier($cart->id_carrier);
        $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($carrier->id_reference);

        if($omnivaCarrier->type == 'terminal')
        {
            $cartTerminal = new OmnivaIntCartTerminal($cart->id);
            // Check if terminal was selected
            if(!Validate::isLoadedObject($cartTerminal) || !$cartTerminal->id_terminal)
            {
                $this->context->controller->errors['omniva_terminal_error'] = $this->l('Please select a terminal.');
                $params['completed'] = false;
                return false;
            }
        }
    }

        /**
     * Use hook to validate carrier selection in Prestashop 1.6
     */
    public function hookActionCarrierProcess($params)
    {
        if(version_compare(_PS_VERSION_, '1.7', '<'))
        {
            $data = [
                'step_name' => 'delivery',
                'cart' => $params['cart']
            ];
            $this->hookActionValidateStepComplete($data);
        }
    }

    /**
     * Hook to display content on carrier in checkout page
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        if(version_compare(_PS_VERSION_, '1.7', '<'))
        {
            $omniva_terminal_carrier_exists = false;
            foreach(reset($params['delivery_option_list']) as $key => $carrier)
            {
                $carrierObj = new Carrier(trim($key, ','));
                $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($carrierObj->id_reference);
                if(Validate::isLoadedObject($omnivaCarrier) && $omnivaCarrier->type == 'terminal')
                {
                    $omniva_terminal_carrier_exists = true;
                    break;
                }
            }
        }
        else
            $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($params['carrier']['id_reference']);

        if ((version_compare(_PS_VERSION_, '1.7', '>=') && $omnivaCarrier->type == 'terminal') || (version_compare(_PS_VERSION_, '1.7', '<') && $omniva_terminal_carrier_exists))
        {
            // If it is terminal, it should have only one service, which we need to filter out terminals by identifier.
            $carrierServices = OmnivaIntCarrierService::getCarrierServices($omnivaCarrier->id);
            if(isset($carrierServices[0]))
            {
                $service = new OmnivaIntService($carrierServices[0]);
            }
            else
            {
                return '';
            }

            $address = new Address($params['cart']->id_address_delivery);
            $country_code = Country::getIsoById($address->id_country);

            if (empty($country_code)) {
                return '';
            }

            $terminals = OmnivaIntTerminal::getTerminalsByIsoAndIndentifier($country_code, $service->parcel_terminal_type);
            if (!$terminals || empty($terminals)) {
                return '';
            }
            $this->context->smarty->assign('terminals', $terminals);
            $this->context->smarty->assign(array(
                'id_carrier' => version_compare(_PS_VERSION_, '1.7', '>=') ? $params['carrier']['id'] : $carrierObj->id,
                'parcel_terminals' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name .'/views/templates/front/terminal_options.tpl'),
                'terminals_list' => $terminals,
                'omniva_current_country' => $country_code,
                'omniva_postcode' => $address->postcode,
                'omniva_map' => 1,
                'module_url' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
              ));

            return $this->display(__FILE__, 'displayCarrierExtraContent.tpl');
        }
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
        $carrier = new Carrier($order->id_carrier);
        $carrier_reference = $carrier->id_reference;
        if($carrier->external_module_name == $this->name)
        {
            $omnivaOrder = new OmnivaIntOrder();
            $omnivaOrder->force_id = true;
            $omnivaOrder->id = $order->id;
            $omnivaOrder->id_shop = $order->id_shop; 
            $omnivaOrder->service_code = $this->context->cookie->{'omniva_carrier_' . $carrier_reference};
            $omnivaOrder->cod_amount = $order->total_paid_tax_incl;
            $omnivaOrder->add();
            if(Validate::isLoadedObject($omnivaOrder))
            {
                $cart_products = $cart->getProducts();
                foreach ($cart_products as $product)
                {
                    $id_category = $product['id_category_default'];
                    $amount = (int) $product['cart_quantity'];
                    $omnivaCategory = new OmnivaIntCategory($id_category);
                    
                    if($omnivaCategory->active)
                    { 
                        for($i = 0; $i < $amount; $i++)
                        {
                            $omnivaParcel = new OmnivaIntParcel();
                            $omnivaParcel->id_order = $omnivaOrder->id;
                            $omnivaParcel->amount = 1;
                            $omnivaParcel->weight = $omnivaCategory->weight ? $omnivaCategory->weight : 1;
                            $omnivaParcel->length = $omnivaCategory->length ? $omnivaCategory->length : 1;
                            $omnivaParcel->width = $omnivaCategory->width ? $omnivaCategory->width : 1;
                            $omnivaParcel->height = $omnivaCategory->height ? $omnivaCategory->height : 1;
                            $omnivaParcel->add();
                        }
                    }
                    else
                    {
                        for($i = 0; $i < $amount; $i++)
                        {
                            $omnivaParcel = new OmnivaIntParcel();
                            $omnivaParcel->id_order = $omnivaOrder->id;
                            $omnivaParcel->amount = 1;
                            $omnivaParcel->weight = $product['weight'] ? $product['weight'] : 1;
                            $omnivaParcel->length = $product['depth'] ? $product['depth'] : 1;
                            $omnivaParcel->width = $product['width'] ? $product['width'] : 1;
                            $omnivaParcel->height = $product['height'] ? $product['height'] : 1;
                            $omnivaParcel->add();
                        }
                    }
                }
            }
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (get_class($this->context->controller) == 'AdminOrdersController' || get_class($this->context->controller) == 'AdminLegacyLayoutControllerCore') {
            {
                Media::addJsDef([
                    'omniva_admin_order_link' => $this->context->link->getAdminLink('AdminOmnivaIntOrder'),
                ]);
                $this->context->controller->addCSS($this->_path . 'views/css/omniva-admin-order.css');
                $this->context->controller->addJS($this->_path . 'views/js/omniva-admin-order.js');
            }
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

        return $this->displayAlert($content, $sugar);
    }

    public function hookDisplayAdminOmnivaIntTerminalsListBefore($params)
    {
        $link = $this->context->link->getModuleLink($this->name, 'cron', ['type' => 'terminals', 'token' => Configuration::get('OMNIVA_CRON_TOKEN')]);
        $content = $this->l("To udate terminals periodically, add this CRON job to your cron table: ");

        $sugar = "<b><i>$link</i></b>";

        return $this->displayAlert($content, $sugar);
    }

    public function hookDisplayAdminOmnivaIntCountriesListBefore($params)
    {
        $link = $this->context->link->getModuleLink($this->name, 'cron', ['type' => 'countries', 'token' => Configuration::get('OMNIVA_CRON_TOKEN')]);
        $content = $this->l("To udate countries periodically, add this CRON job to your cron table: ");

        $sugar = "<b><i>$link</i></b>";

        return $this->displayAlert($content, $sugar);
    }

    public function hookDisplayBeforeCarrier($params)
    {
        if(version_compare(_PS_VERSION_, '1.7', '<'))
        {
            return $this->hookDisplayCarrierExtraContent($params);
        }
    }

    public function displayAlert($content, $sugar, $type = 'info')
    {
        $context = Context::getContext();
        $context->smarty->assign(
            [
                'content' => $content,
                'sugar' => $sugar,
                'type' => $type,
            ]
        );
        return $context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . "/views/templates/admin/alert.tpl");
    }
}
