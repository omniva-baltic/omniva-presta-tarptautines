<?php

require_once "AdminOmnivaIntBaseController.php";

class AdminOmnivaIntCarriersController extends AdminOmnivaIntBaseController
{
    const PRICE_TYPES = ['fixed', 'surcharge-percent', 'surcharge-fixed'];

    public $price_types;

    public $adding_terminal_carrier = false;

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

        if(Tools::getValue('submitManageCountries') || Tools::isSubmit('statusomniva_int_carrier_country')
           || Tools::isSubmit('updateomniva_int_carrier_country') || Tools::getValue('submitAddomniva_int_carrier_country'))
        {
            $this->className = 'OmnivaIntCarrierCountry';
            $this->table = 'omniva_int_carrier_country';
        }
        else
        {
            $this->className = 'OmnivaIntCarrier';
            $this->table = 'omniva_int_carrier';
        }
        $this->_orderBy = 'id';
        $this->identifier = 'id';
        $this->price_type_trans = array_combine(
            self::PRICE_TYPES, [
                $this->module->l('Fixed, EUR'),
                $this->module->l('Surcharge, %'),
                $this->module->l('Surcharge, EUR'),
            ]
        );

        $this->price_types = [
            [
                'id' => 'fixed',
                'value' => 'fixed',
                'label' => $this->module->l('Fixed, EUR'),
            ],
            [
                'id' => 'surcharge-percent',
                'value' => 'surcharge-percent',
                'label' => $this->module->l('Surcharge, %'),
            ],
            [
                'id' => 'surcharge-fixed',
                'value' => 'surcharge-fixed',
                'label' => $this->module->l('Surcharge, EUR'),
            ],
        ];

        $this->delivery_types = [
            [
                'id' => 'fastest',
                'value' => 0,
                'label' => $this->module->l('Fastest'),
            ],
            [
                'id' => 'cheapest',
                'value' => 1,
                'label' => $this->module->l('Cheapest'),
            ],
        ];

        $this->_select = ' c.name as name,
                            CONCAT(IFNULL(a.tax, 0), " %") as tax, 
                            a.`id` as id_1, 
                            (SELECT GROUP_CONCAT(os.service_code SEPARATOR ", ") 
                            FROM `' . _DB_PREFIX_ .'omniva_int_service` os 
                            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_carrier_service ocs ON (os.`id` = ocs.`id_service`)
                            WHERE a.id = ocs.id_carrier AND os.active = 1) as services';

        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_carrier_service ocs ON (ocs.id_carrier = a.id)
            LEFT JOIN ' . _DB_PREFIX_ . 'carrier c ON (c.id_reference = a.id_reference)
            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_service os ON (os.id = ocs.id_service)';

        $this->_group = 'GROUP BY id_reference';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        }
        elseif (Tools::getValue('submitManageCountries'))
        {
            $this->carrierCountriesList();
        }
        else {
            $this->carrierList();
        }
        parent::init();
    }

    protected function carrierCountriesList()
    {
        $this->_select = ' cl.name, CONCAT(IFNULL(a.tax, 0), " %") as tax';
        $this->toolbar_title = $this->module->l('Carrier Countries');
        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'country_lang cl 
            ON (cl.id_country = a.id_country AND cl.id_lang = ' . $this->context->language->id . ')';

        $this->_group = '';
        $this->_where = ' AND a.id_carrier = ' . $this->loadObject()->id;

        $this->fields_list = [
            'name' => [
                'title' => $this->module->l('Name'),
                'align' => 'text-center',
                'search' => false,
                'filter_key' => 'c!name'
            ],
            'price_type' => [
                'title' => $this->module->l('Price Type'),
                'align' => 'center',
                'type' => 'select',
                'search' => false,
                'filter_key' => 'a!price_type',
                'list' => $this->price_type_trans,
                'callback' => 'transPriceType'
            ],
            'price' => [
                'title' => $this->module->l('Price'),
                'align' => 'center',
                'search' => false,
                'callback' => 'displayPriceType'
            ],
            'tax' => [
                'title' => $this->module->l('Tax'),
                'align' => 'center',
            ],
            'free_shipping' => [
                'type' => 'number',
                'title' => $this->module->l('Free Shipping'),
                'align' => 'center',
                'search' => false,
                'callback' => 'displayPrice'
            ],
            'cheapest' => [
                'title' => $this->module->l('Price method'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!cheapest',
                'search' => false,
                'list' => [
                    $this->module->l('Fastest'),
                    $this->module->l('Cheapest'),
                ],
                'callback' => 'fastestOrCheapest'
            ],
            'active' => [
                'type' => 'bool',
                'title' => $this->module->l('Active'),
                'active' => 'status',
                'search' => false,
                'align' => 'center',
            ],
        ];

        $this->actions = ['edit'];
    }

    protected function carrierList()
    {
        $this->fields_list = [
            'name' => [
                'title' => $this->module->l('Name'),
                'align' => 'text-center',
                'filter_key' => 'c!name'
            ],
            'services' => [
                'type' => 'text',
                'title' => $this->module->l('Services'),
                'align' => 'center',
                'filter_key' => 'services',
                'havingFilter' => true
            ],
            'price_type' => [
                'title' => $this->module->l('Price Type'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!price_type',
                'list' => $this->price_type_trans,
                'callback' => 'transPriceType'
            ],
            'price' => [
                'title' => $this->module->l('Price'),
                'align' => 'center',
                'callback' => 'displayPriceType'
            ],
            'tax' => [
                'title' => $this->module->l('Tax'),
                'align' => 'center',
            ],
            'free_shipping' => [
                'type' => 'number',
                'title' => $this->module->l('Free Shipping'),
                'align' => 'center',
                'callback' => 'displayPrice'
            ],
            'cheapest' => [
                'title' => $this->module->l('Price method'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!cheapest',
                'list' => [
                    $this->module->l('Fastest'),
                    $this->module->l('Cheapest'),
                ],
                'callback' => 'fastestOrCheapest'
            ],
            'active' => [
                'type' => 'bool',
                'title' => $this->module->l('Active'),
                'active' => 'status',
                'align' => 'center',
            ],
            'id_1' => [
                'title' => '',
                'align' => 'text-center',
                'search' => false,
                'orderby' => false,
                'callback' => 'printCallCarrierBtn',
            ]
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

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['rate_cache'] = [
            'href' => self::$currentIndex . '&rate_cache=1&token=' . $this->token,
            'desc' => $this->module->l('Clear Rate Cache'),
            'imgclass' => 'delete',
        ];
        parent::initPageHeaderToolbar();
    }


    public function initToolbar()
    {
        if(!OmnivaIntService::getCount() || Tools::getValue('submitManageCountries'))
        {
            if(!Tools::getValue('submitManageCountries'))
                $this->errors[] = $this->module->l('You must download services before you can add carriers');
            $this->toolbar_btn['bogus'] = [
                'href' => '#',
                'desc' => $this->module->l('Back to list'),
            ];
        }
        else
        {
            parent::initToolbar();
        }
    }


    public function postProcess()
    {
        parent::postProcess();
        if(Tools::getValue('rate_cache'))
        {
            $result = Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_. OmnivaIntRateCache::$definition['table']);
            if($result)
                $this->confirmations[] = $this->module->l('Successfully deleted rate cache.');
            else
                $this->errors[] = $this->module->l('Failed to clear the rate cache.');
        }
    }

    public function processStatus()
    {
        parent::processStatus();
        $this->redirect_after = $this->context->link->getAdminLink("AdminOmnivaIntCarriers", true, [], [
            'id' => $this->object->id_carrier,
            'submitManageCountries' => 1,
            'conf' => 5
        ]);
    }

    public function fastestOrCheapest($cheapest)
    {
        if($cheapest)
            return $this->module->l('Cheapest');
        else
            return $this->module->l('Fastest');
    }

    public function renderForm()
    {
        if (Tools::isSubmit('updateomniva_int_carrier_country'))
        {
            $this->renderCarrierCountriesEditForm();
        }
        else
        {
            $this->renderCarrierEditForm();
        }
        return parent::renderForm();
    }

    public function renderCarrierCountriesEditForm()
    {
        $this->table = 'omniva_int_carrier_country';
        $this->identifier = 'id';

        $switcher_values = [
            [
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Omniva Carrier Country'),
                'icon' => 'icon-truck',
            ],
            'input' => [
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Price'),
                    'name' => 'price_type',
                    'values' => $this->price_types,
                    'class' => 'col-xs-2'
                ],
                [
                    'type' => 'text',
                    'name' => 'price',
                    'label' => '',
                    'col' => '2',
                ],
                [
                    'type' => 'text',
                    'name' => 'free_shipping',
                    'label' => 'Free Shipping',
                    'col' => '2',
                    'prefix' => '€'
                ],
                [
                    'type' => 'text',
                    'name' => 'tax',
                    'label' => $this->module->l('Tax'),
                    'col' => '2',
                    'suffix' => '%'
                ],
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Delivery type'),
                    'name' => 'cheapest',
                    'values' => $this->delivery_types,
                    'class' => 'col-xs-2'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Active'),
                    'name' => 'active',
                    'values' => $switcher_values
                ]
            ],
        ];

        $this->fields_form['submit'] = [
            'title' => $this->module->l('Save'),
        ];
    }

    public function renderCarrierEditForm()
    {
        $this->table = 'omniva_int_carrier';
        $this->identifier = 'id';

        $switcher_values = [
            [
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Omniva International Carrier'),
                'icon' => 'icon-truck',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Carrier Name'),
                    'name' => 'carrier_name',
                    'required' => true,
                    'col' => '3',
                ],
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Price'),
                    'name' => 'price_type',
                    'values' => $this->price_types,
                    'class' => 'col-xs-2'
                ],
                [
                    'type' => 'text',
                    'name' => 'price',
                    'label' => '',
                    'col' => '2',
                ],
                [
                    'type' => 'text',
                    'name' => 'free_shipping',
                    'label' => 'Free Shipping',
                    'col' => '2',
                    'prefix' => '€'
                ],
                [
                    'type' => 'text',
                    'name' => 'tax',
                    'label' => $this->module->l('Tax'),
                    'col' => '2',
                    'suffix' => '%'
                ],
                [
                    'type' => 'swap',
                    'label' => $this->module->l('Services'),
                    'name' => 'services',
                    'multiple' => true,
                    'default_value' => $this->module->l('Multiple select'),
                    'options' => [
                        'query' => OmnivaIntService::getServices(true),
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $this->module->l('Select all services which will be used by this carrier'),
                ],
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Delivery type'),
                    'name' => 'cheapest',
                    'values' => $this->delivery_types,
                    'class' => 'col-xs-2'
                ],
                [
                    'type' => 'text',
                    'name' => 'radius',
                    'label' => 'Radius',
                    'col' => '3',
                    'suffix' => 'km'
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

        if(Tools::isSubmit('updateomniva_int_carrier'))
        {
            $this->fields_form['input'][] = [
                    'type' => 'switch',
                    'label' => $this->module->l('Active'),
                    'name' => 'active',
                    'values' => $switcher_values
            ];
            $prestaCarrier = Carrier::getCarrierByReference($this->object->id_reference);
            $this->fields_value = 
            [
                'carrier_name' => $prestaCarrier->name,
                'services' => OmnivaIntCarrierService::getCarrierServices($this->object->id)
            ];
        }
        else
        {
            $this->warnings[] = $this->module->l('Note: if seleceted service supports pickup terminals, then two carriers will be created in Prestashop: 1. "Carrier Name" and 2. "Carrier Name Terminals"');
            $this->fields_value = 
            [
                'services' => []
            ];
        }
    }

    public function processAdd()
    {
        if(Tools::getValue('submitAddomniva_int_carrier'))
        {
            $carrier = new Carrier();
            if($this->adding_terminal_carrier)
                $carrier->name = Tools::getValue('carrier_name', '') . ' Terminal';
            else
                $carrier->name = Tools::getValue('carrier_name', '');
            $carrier->delay[Configuration::get('PS_LANG_DEFAULT')] = '1-2 business days';
            $carrier->is_module = true;
            $carrier->external_module_name = $this->module->name;
            $carrier->shipping_external = true;
            $carrier->shipping_handling = false;
            $carrier->need_range = true;
            
            if (!$carrier->add()) {
                $this->errors[] = $this->module->l('Could not add carrier.');
            }
            else
            {
                $groups = array_map(function ($group) { return $group['id_group']; }, Group::getGroups(true));
                $carrier->setGroups($groups);
                if(file_exists(_PS_MODULE_DIR_ . $this->module->name . '/views/img/carrier_miniature.jpg'))
                {
                    $image_path = _PS_MODULE_DIR_ . $this->module->name . '/views/img/carrier_miniature.jpg';
                }
                else
                {
                    $image_path = _PS_MODULE_DIR_ . $this->module->name . '/logo.png';
                }
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

                // If found any terminal service, repeat the adding process to add terminal carrier.
                if($this->adding_terminal_carrier)
                {
                    $this->processAdd();
                }
            }
        }
    }

    public function createOmnivaCarrier($carrier)
    {
        // First - the actual OmnivaIntCarrier, which will be linked to international services.
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
        $cheapest = (bool) Tools::getValue('cheapest', false);
        $radius = (int) Tools::getValue('radius', 100);
        $tax = (int) Tools::getValue('tax');

        if(!Validate::isCarrierName($carrier_name))
        {
            $this->errors[] = $this->module->l('Carrier name is invalid.');
        }

        // Filter out non-existing services, if user is playing around.
        $final_services = [];
        if(is_array($services) && !empty($services))
        {
            $pickup_services = 0;
            foreach($services as $id_service)
            {
                $service = new OmnivaIntService($id_service);
                if(Validate::isLoadedObject($service))
                {
                    // Additionally, if adding pickup carrier, check if "delivery_to_address" is false and "parcel_terminal_type" is set.
                    if($this->adding_terminal_carrier && !$service->delivery_to_address && $service->parcel_terminal_type)
                        $final_services[] = $id_service;
                    else if(!$this->adding_terminal_carrier)
                        $final_services[] = $id_service; 

                    // If this is not pickup carrier creation process, we need to determine, if we'll do it later.
                    if(!$service->delivery_to_address && $service->parcel_terminal_type)
                    {
                        $pickup_services++;
                    }
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
        $omnivaCarrier->id_reference = $carrier->id;
        $omnivaCarrier->price_type = $price_type;
        $omnivaCarrier->price = $price;
        $omnivaCarrier->free_shipping = $free_shipping;
        $omnivaCarrier->cheapest = $cheapest;
        $omnivaCarrier->tax = $tax;
        $omnivaCarrier->radius = $radius;

        if($this->adding_terminal_carrier)
            $omnivaCarrier->type = 'terminal';
        else
            $omnivaCarrier->type = 'courier';

        if($pickup_services > 0 && !$this->adding_terminal_carrier)
        {
            $this->adding_terminal_carrier = true;
        }
        else
        {
            $this->adding_terminal_carrier = false;
        }

        $result = $omnivaCarrier->add();
        if(!$result)
        {
            $this->errors[] = $this->module->l('Could not create Omniva Carrier.');
            $carrier->delete();
            return;
        }
        // If carrier was created successfully, we proceed to adding services.
        else
        {
            $this->addCarrierCountries($omnivaCarrier);
            $this->createOmnivaCarrierServices($omnivaCarrier, $final_services);
        }
    }

    public function addCarrierCountries($omnivaCarrier)
    {
        $countries = Country::getCountries($this->context->language->id, true);
        foreach ($countries as $country)
        {
            $omnivaCarrierCountry = new OmnivaIntCarrierCountry();
            $omnivaCarrierCountry->id_carrier = $omnivaCarrier->id;
            $omnivaCarrierCountry->id_country = $country['id_country'];
            $omnivaCarrierCountry->price_type = $omnivaCarrier->price_type;
            $omnivaCarrierCountry->price = $omnivaCarrier->price;
            $omnivaCarrierCountry->free_shipping = $omnivaCarrier->free_shipping;
            $omnivaCarrierCountry->cheapest = $omnivaCarrier->cheapest;
            $omnivaCarrierCountry->tax = $omnivaCarrier->tax;
            $omnivaCarrierCountry->active = 1;
            $omnivaCarrierCountry->add();
        }
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

        $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
    }

    public function displayPrice($price)
    {
        return Tools::displayPrice($price);
    }

    public function displayPriceType($price, $tr)
    {
        switch($tr['price_type'])
        {
            case 'fixed':
                return Tools::displayPrice($price);
            case 'surcharge-percent':
                return '+ ' . $price . ' %';
            case 'surcharge-fixed':
                return '+ ' . $price . ' EUR';
            default:
                return $price;
        }
    }

    public function displayTaxName($tax, $tr)
    {
        if($tax)
            return $tax;
        return $this->trans('No tax', [], 'Admin.Global');
    }

    public function transPriceType($price_type)
    {
        return isset($this->price_type_trans[$price_type]) ? $this->price_type_trans[$price_type] : $this->module->l("Price type not determined.");
    }

    public function processUpdate()
    {
        // Core can handle OmnivaIntCarrier fields.
        parent::processUpdate();
        if(Tools::getValue('submitAddomniva_int_carrier_country'))
        {
            $this->redirect_after = $this->redirect_after = $this->context->link->getAdminLink("AdminOmnivaIntCarriers", true, [], [
                'id' => $this->object->id_carrier,
                'submitManageCountries' => 1,
                'conf' => 4
            ]);
        }
        if(Tools::getValue('submitAddomniva_int_carrier'))
        {
            // Extra handling for core Carrier and new carrier services, or removed ones.
            $carrier_name = Tools::getValue('carrier_name', '');

            if(!Validate::isCarrierName($carrier_name))
            {
                $this->errors[] = $this->module->l('Carrier name is invalid.');
            }
            else
            {
                $carrier = Carrier::getCarrierByReference($this->object->id_reference);
                $carrier->name = $carrier_name;
                if(!$carrier->save())
                    $this->errors[] = $this->module->l('Couldn\'t update the carrier.');
            }

            // Handle services. If there are new one(s) selected - add them. If any were removed - delete them.
            $current_services = OmnivaIntCarrierService::getCarrierServices($this->object->id);
            $services_selected = Tools::getValue('services_selected');

            $selected_services_new = array_diff($services_selected, $current_services);
            $deleted_services = array_diff($current_services, $services_selected);

            foreach($selected_services_new as $service)
            {
                $omnivaCarrierService = new OmnivaIntCarrierService();
                $omnivaCarrierService->id_carrier = $this->object->id;
                $omnivaCarrierService->id_service = $service;
                $omnivaCarrierService->add();
            }
            foreach($deleted_services as $service)
            {
                $omnivaCarrierServiceId = OmnivaIntCarrierService::getCarrierService($this->object->id, $service);
                if((int)$omnivaCarrierServiceId > 0)
                {
                    $omnivaCarrierService = new OmnivaIntCarrierService($omnivaCarrierServiceId);
                    if(Validate::isLoadedObject($omnivaCarrierService))
                    {
                        $omnivaCarrierService->delete();
                    }
                }
            }
        }
    }

    public function processDelete()
    {
        // Let core delete the OmnivaIntCarrier
        parent::processDelete();

        // Delete the associated core carrier and carrier services.
        $this->deleteCarrierAndServices($this->object);
    }

    protected function processBulkDelete()
    {
        foreach ($this->boxes as $id)
        {
            $omnivaCarrier = new OmnivaIntCarrier($id);

            if(Validate::isLoadedObject($omnivaCarrier))
            {
                $this->deleteCarrierAndServices($omnivaCarrier);
            }
            else
            {
                $this->errors[] = $this->module->l('Couldn\'t load Omniva carrier.') . " id " . $omnivaCarrier->id;
            }
        }

        parent::processBulkDelete();
    }

    public function deleteCarrierAndServices($omnivaCarrier)
    {
        $carrier = Carrier::getCarrierByReference($omnivaCarrier->id_reference);
        if(!$carrier->delete())
            $this->errors[] = $this->module->l('Couldn\'t delete Prestashop carrier.') . " id_reference " . $omnivaCarrier->id_reference;

        $carrier_services = OmnivaIntCarrierService::getCarrierServices($omnivaCarrier->id);
        foreach($carrier_services as $service)
        {
            $omnivaCarrierServiceId = OmnivaIntCarrierService::getCarrierService($omnivaCarrier->id, $service);
            if((int)$omnivaCarrierServiceId > 0)
            {
                $omnivaCarrierService = new OmnivaIntCarrierService($omnivaCarrierServiceId);
                if(Validate::isLoadedObject($omnivaCarrierService))
                {
                    $omnivaCarrierService->delete();
                }
            }
        }
    }

    public function printCallCarrierBtn($id_carrier)
    {
        $this->context->smarty->assign('data_button', [
            'icon' => 'icon-flag',
            'title' => $this->module->l('Manage Countries'),
            'href' => self::$currentIndex . '&submitManageCountries=1&token=' . $this->token . '&id=' . $id_carrier,
        ]);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/action_button.tpl');
    }
}