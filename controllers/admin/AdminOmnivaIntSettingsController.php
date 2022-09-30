<?php

require_once "AdminOmnivaIntBaseController.php";

class AdminOmnivaIntSettingsController extends AdminOmnivaIntBaseController
{
    public function __construct()
    {
        $this->display = 'edit';
        $this->show_form_cancel_button = false;
        $this->submit_action = 'submitAddconfigurationAndStay';
        parent::__construct();
        $this->toolbar_title = $this->module->l('Omniva Settings');
        $this->multiple_fieldsets = true;
        $this->section_id = ['API', 'SHOP'];

        // Map active countries to Omniva countries IDs 
        $countries = Country::getCountries($this->context->language->id, true);
        array_walk($countries, function(&$item, $key) {
            if($k = OmnivaIntCountry::getCountryIdByIso($item['iso_code']))
            {
                $item['id_country'] = $k;
            }
        });
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
            [
                'form' => [
                    'legend' => [
                        'title' => $this->module->l('API Settings'),
                        'icon' => 'icon-cog',
                    ],
                    'input' => 
                    [
                        [
                            'type' => 'text',
                            'label' => $this->module->l('API Token'),
                            'name' => $this->module->getConfigKey('token', $this->section_id[0]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'switch',
                            'label' => $this->module->l('Test Mode'),
                            'name' => $this->module->getConfigKey('test_mode', $this->section_id[0]),
                            'desc' => $this->module->l('Use test mode if you have test token to test your integration.'),
                            'values' => $switcher_values
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                        'name'=>'submitSettings',
                    ]
                ]
            ],
            [
                'form' => [
                    'legend' => [
                        'title' => $this->module->l('Sender Settings'),
                        'icon' => 'icon-cog',
                    ],
                    'input' => 
                    [
                        [
                            'type' => 'text',
                            'label' => $this->l('Sender Name'),
                            'name' => $this->module->getConfigKey('sender_name', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Company code'),
                            'name' => $this->module->getConfigKey('company_code', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Address'),
                            'name' => $this->module->getConfigKey('shop_address', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('City'),
                            'name' => $this->module->getConfigKey('shop_city', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'select',
                            'label' => $this->l('Country Code'),
                            'name' => $this->module->getConfigKey('shop_country_code', $this->section_id[1]),
                            'options' => [
                                'query' => $countries,
                                'id' => 'id_country',
                                'name' => 'name'
                            ],
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Postcode'),
                            'name' => $this->module->getConfigKey('shop_postcode', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Contact person'),
                            'name' => $this->module->getConfigKey('shop_contact', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Mob. Phone'),
                            'name' => $this->module->getConfigKey('shop_phone', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Email'),
                            'name' => $this->module->getConfigKey('shop_email', $this->section_id[1]),
                            'size' => 20,
                            'required' => true
                        ],
                        [
                            'type' => 'switch',
                            'label' => $this->module->l('Consolidation'),
                            'name' => $this->module->getConfigKey('consolidation', $this->section_id[1]),
                            'desc' => $this->module->l('If enabled, all order products will be put into one package.'),
                            'values' => $switcher_values
                        ],

                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                        'name'=>'submitSettings',
                    ]
                ]
            ],
        ];

        $config_keys = array_merge($this->module->_configKeys[$this->section_id[0]], $this->module->_configKeys[$this->section_id[1]]);
        array_walk($config_keys, function(&$item, $key) {
            $this->fields_value[$item] = Configuration::get($item);    
        });
    }

}