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
        $this->section_id = 'API';
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
                'title' => $this->module->l('API Settings'),
                'icon' => 'icon-cog',
            ],
            'input' => 
            [
                [
                    'type' => 'text',
                    'label' => $this->module->l('API Token'),
                    'name' => $this->module->getConfigKey('token', $this->section_id),
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Test Mode'),
                    'name' => $this->module->getConfigKey('test_mode', $this->section_id),
                    'desc' => $this->module->l('Use test mode if you have test token to test your integration.'),
                    'values' => $switcher_values
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'name'=>'submitSettings',
            ]
        ];

        $config_keys = $this->module->_configKeys[$this->section_id];
        array_walk($config_keys, function(&$item, $key) {
            $this->fields_value[$item] = Configuration::get($item);    
        });
    }

}