<?php

require_once "AdminOmnivaIntBaseController.php";
require_once __DIR__ . "/../../classes/OmnivaIntCarrier.php";
require_once __DIR__ . "/../../classes/OmnivaIntService.php";
require_once __DIR__ . "/../../classes/OmnivaIntCarrierService.php";

class AdminOmnivaIntCarriersController extends AdminOmnivaIntBaseController
{
    const PRICE_TYPES = ['fixed', 'surcharge-percent', 'surcharge-fixed'];

    public $price_types;

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

        $this->price_type_trans = array_combine(
            self::PRICE_TYPES, [
                $this->module->l('Fixed'),
                $this->module->l('Surcharge %'),
                $this->module->l('Surcharge, Eur'),
            ]
        );

        $this->price_types = [
            [
                'id' => 'fixed',
                'value' => 'fixed',
                'label' => $this->module->l('Fixed'),
            ],
            [
                'id' => 'surcharge-percent',
                'value' => 'surcharge-percent',
                'label' => $this->module->l('Surcharge %'),
            ],
            [
                'id' => 'surcharge-fixed',
                'value' => 'surcharge-fixed',
                'label' => $this->module->l('Surcharge, Eur'),
            ],
        ];

        $this->_select = ' c.name as name, os.name as service';

        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_carrier_service ocs ON (ocs.id_carrier = a.id)
            LEFT JOIN ' . _DB_PREFIX_ . 'carrier c ON (c.id_carrier = a.id_carrier)
            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_service os ON (os.id = ocs.id_service)';
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
                'filter_key' => 'c!name'
            ),
            'service' => array(
                'type' => 'text',
                'title' => $this->module->l('Service'),
                'align' => 'center',
                'filter_key' => 'os!name'
            ),
            'price_type' => array(
                'title' => $this->module->l('Price Type'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!price_type',
                'list' => $this->price_type_trans,
                'callback' => 'transPriceType'
            ),
            'price' => array(
                'title' => $this->module->l('Price'),
                'align' => 'center',
                'callback' => 'displayPrice'
            ),
            'free_shipping' => array(
                'type' => 'numer',
                'title' => $this->module->l('Free Shipping'),
                'align' => 'center',
                'callback' => 'displayPrice'
            ),
            'select_cheapest' => array(
                'type' => 'text',
                'title' => $this->module->l('Price method'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!select_cheapest',
                'list' => [
                    $this->module->l('Fastest'),
                    $this->module->l('Cheapest'),
                ],
                'callback' => 'fastestOrCheapest'
            ),
        );
    }

    public function fastestOrCheapest($select_cheapest)
    {
        if($select_cheapest)
            return $this->module->l('Cheapest');
        else
            return $this->module->l('Fastest');
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

        $fastest_cheapest_switcher_values = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Cheapest'),
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Cheapest')
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
                    'type' => 'radio',
                    'label' => $this->l('Price'),
                    'name' => 'price_type',
                    'values' => $this->price_types,
                    'class' => 'col-xs-2'
                ),
                array(
                    'type' => 'text',
                    'name' => 'price',
                    'label' => '',
                    'col' => '2',
                    'prefix' => '€'
                ),
                array(
                    'type' => 'text',
                    'name' => 'free_shipping',
                    'label' => 'Free Shipping',
                    'col' => '2',
                    'prefix' => '€'
                ),
                // array(
                //     'type' => 'switch',
                //     'label' => $this->l('Insurance'),
                //     'name' => 'insurance',
                //     'values' => $switcher_values
                // ),
                // array(
                //     'type' => 'switch',
                //     'label' => $this->l('Return'),
                //     'name' => 'return',
                //     'values' => $switcher_values
                // ),
                // array(
                //     'type' => 'switch',
                //     'label' => $this->l('Carry service'),
                //     'name' => 'carry',
                //     'values' => $switcher_values
                // ),
                // array(
                //     'type' => 'switch',
                //     'label' => $this->l('Document Return'),
                //     'name' => 'insurance',
                //     'values' => $switcher_values
                // ),
                array(
                    'type' => 'swap',
                    'label' => $this->module->l('Services'),
                    'name' => 'services',
                    'multiple' => true,
                    'default_value' => $this->l('Multiple select'),
                    'options' => [
                        'query' => OmnivaIntService::getServices(),
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $this->module->l('Select all services which will be used by this carrier'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('My login'),
                    'name' => 'my_login',
                    'values' => $switcher_values
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('User'),
                    'name' => 'user',
                    'col' => '3',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Password'),
                    'name' => 'password',
                    'col' => '3',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Fastest'),
                    'name' => 'fastest',
                    'values' => $fastest_cheapest_switcher_values
                ),
                array(
                    'type' => 'text',
                    'name' => 'radius',
                    'label' => 'Radius',
                    'col' => '3',
                    'suffix' => 'km'
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

    public function processSave()
    {
        if(Tools::getValue('submitAddomniva_int_carrier'))
        {
            $carrier = new Carrier();
            $carrier->name = Tools::getValue('carrier_name', '');
            $carrier->delay[Configuration::get('PS_LANG_DEFAULT')] = '1-2 business days';
            $carrier->is_module = true;
            $carrier->external_module_name = $this->module->name;
            $carrier->shipping_external = true;
            $carrier->shipping_handling = false;
            
            if (!$carrier->add()) {
                $this->errors[] = $this->module->l('Select shop');
            }
            else
            {
                $groups = array_map(function ($group) { return $group['id_group']; }, Group::getGroups(true));
                $carrier->setGroups($groups);
                $image_path = _PS_MODULE_DIR_ . $this->module->name . '/logo.png';
                copy($image_path, _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');

                // Let's add some range weight, which is meaningless, but necessary not to break default carrier edit page..
                $rangeWeight = new RangeWeight();
                $rangeWeight->id_carrier = (int) $carrier->id;
                $rangeWeight->delimiter1 = '0';
                $rangeWeight->delimiter2 = '9999';
                $rangeWeight->add();

                // Let's add zone to not wreck Presta's default carrier page..
                $europe = Zone::getIdByName('Europe');

                // If shop does not have Europe zone, just add the first one we find..
                if(!$europe)
                {
                    $zones = Zone::getZones(true);
                    $first_zone_id = $zones[0]['id_zone'];
                    $carrier->addZone($first_zone_id);
                }
                else
                    $carrier->addZone($europe);

                // If ship didn't sink at this point, we created all Omniva Inernational relevant entries.
                $this->createOmnivaCarrier($carrier);
            }
        }
    }

    public function createOmnivaCarrier($carrier)
    {
        // First - the actual OmnivaIntCarrier, which will be linked to international services and carrier logins, should they be used.
        /* Validation is pretty simple.
        // 1. Proper carrier name.
        // 2. At least one EXISTING service (otherwise, there is no point to this module..)
        // 3. Price type default to surcharge with 0 % (meaning whatever API returns)
        // 4. Default radius to 100 km ?? */

        $carrier_name = Tools::getValue('carrier_name', '');
        $services = Tools::getValue('services_selected');
        $price_type = Tools::getValue('price_type', false);
        $price = (float) Tools::getValue('price', 0.0);
        $free_shipping = (float) Tools::getValue('free_shipping', 0.0);
        $my_login = (bool) Tools::getValue('my_login', 0);
        $user = Tools::getValue('user', '');
        $password = Tools::getValue('password', '');
        $select_cheapest = (bool) Tools::getValue('fastest', false);
        $radius = (int) Tools::getValue('radius', 100);

        if(!Validate::isCarrierName($carrier_name))
        {
            $this->errors[] = $this->module->l('Carrier name is invalid.');
        }

        // Filter out non-existing services, if user is playing around.
        $final_services = [];
        if(is_array($services) && !empty($services))
        {
            foreach($services as $id_service)
            {
                if(OmnivaIntService::checkServiceExists($id_service))
                {
                    $final_services[] = $id_service;
                }
            }
        }

        if(empty($final_services))
        {
            $this->errors[] = $this->module->l('No valid services were provided. Carrier creation could not be finished.');
            $carrier->delete();
            return;
        }

        if(!$price_type || !in_array($price_type, self::PRICE_TYPES))
        {
            $price_type = 'surcharge-percent';
            $price = 0;
        }

        $omnivaCarrier = new OmnivaIntCarrier();
        $omnivaCarrier->id_carrier = $carrier->id;
        $omnivaCarrier->price_type = $price_type;
        $omnivaCarrier->price = $price;
        $omnivaCarrier->free_shipping = $free_shipping;
        $omnivaCarrier->my_login = $my_login;
        $omnivaCarrier->user = $user;
        $omnivaCarrier->password = $password;
        $omnivaCarrier->select_cheapest = $select_cheapest;
        $omnivaCarrier->radius = $radius;

        $result = $omnivaCarrier->add();
        if(!$result)
        {
            $this->errors[] = $this->module->l('Could not create Omniva Carrier.');
            $carrier->delete();
            return;
        }
        // If carrier was created successfully, we proceed to adding services.
        else
            $this->createOmnivaCarrierServices($omnivaCarrier, $final_services);
    }

    public function createOmnivaCarrierServices($omnivaCarrier, $services)
    {
        if(Validate::isLoadedObject($omnivaCarrier))
        {
            $id_carrier = $omnivaCarrier->id;
            foreach($services as $id_service)
            {
                $carrier_service = new OmnivaIntCarrierService();
                $carrier_service->id_carrier = $id_carrier;
                $carrier_service->id_service = $id_service;
                $result = $carrier_service->add();
                if(!$result)
                    $this->errors[] = $this->module->l('Failed to add service code ') . $id_service;
            }
        }
        else
            $this->errors[] = $this->module->l('Failed to load Omniva Carrier object.');
    }

    public function displayPrice($price)
    {
        return Tools::displayPrice($price);
    }

    public function transPriceType($price_type)
    {
        return isset($this->price_type_trans[$price_type]) ? $this->price_type_trans[$price_type] : $this->module->l("Price type not determined.");
    }
}