<?php 

abstract class AdminOmnivaIntBaseController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function processSave()
    {
        // Multi form with several sections.
        if(isset($this->section_id) && is_array($this->section_id))
        {
            $res = true;
            foreach($this->section_id as $section)
            {
                if(isset($this->module->_configKeys[$section]))
                {
                    $config_keys = $this->module->_configKeys[$section];
                    $res &= $this->saveConfig($config_keys);
                }
            }
            if($res)
                $this->confirmations[] = $this->module->l('Update Sucessful', array(), 'Admin.Notifications.Error');
            else
                $this->errors[] = $this->module->l('Updating Settings failed.', array(), 'Admin.Notifications.Error');
        }
        // Single form
        elseif(isset($this->section_id) && isset($this->module->_configKeys[$this->section_id]))
        {
            $config_keys = $this->module->_configKeys[$this->section_id];
            $res = $this->saveConfig($config_keys);
            if($res)
                $this->confirmations[] = $this->module->l('Update Sucessful', array(), 'Admin.Notifications.Error');
            else
                $this->errors[] = $this->module->l('Updating Settings failed.', array(), 'Admin.Notifications.Error');
        }
        else
            parent::processSave();
    }

    public function saveConfig($config_keys)
    {
        $res = true;
        foreach(Tools::getAllValues() as $key => $value)
        {
            if(in_array($key, $config_keys))
            {
                $res &= Configuration::updateValue($key, $value);
                if($res) 
                    $this->fields_value[$key] = $value;
            }
        }
        return $res;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia();
        if(version_compare(_PS_VERSION_, '1.7.8', '>'))
            $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin.css');
        $this->addJS('modules/' . $this->module->name . '/views/js/admin.js');
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_btn['back_to_modules'] = [
            'href' => $this->context->link->getAdminLink("AdminModules"),
            'desc' => $this->module->l('Back to list'),
            'imgclass' => 'back'
        ];
        $hook_link = 'index.php?tab=AdminModulesPositions&token=' . Tools::getAdminTokenLite('AdminModulesPositions') . '&show_modules=' . (int) $this->module->id;
        $this->page_header_toolbar_btn['hook'] = [
            'href' => $hook_link,
            'desc' => $this->module->l('Manage hooks'),
            'imgclass' => 'anchor'
        ];
        $this->page_header_toolbar_btn['trans'] = [
            'modal_target' => '#moduleTradLangSelect',
            'href' => '#',
            'desc' => $this->module->l('Translate'),
            'imgclass' => 'flag'
        ];
    }

    public function initModal()
    {
        parent::initModal();

        if(version_compare(_PS_VERSION_, '1.7', '<'))
            return;
        $languages = Language::getLanguages(false);
        $translateLinks = [];

        $module = $this->module;

        if (false === $module) {
            return;
        }

        $isNewTranslateSystem = $module->isUsingNewTranslationSystem();
        $link = Context::getContext()->link;
        foreach ($languages as $lang) {
            if ($isNewTranslateSystem) {
                $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslationSf', true, [
                    'lang' => $lang['iso_code'],
                    'type' => 'modules',
                    'selected' => $module->name,
                    'locale' => $lang['locale'],
                ]);
            } else {
                $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslations', true, [], [
                    'type' => 'modules',
                    'module' => $module->name,
                    'lang' => $lang['iso_code'],
                ]);
            }
        }

        $this->context->smarty->assign([
            'trad_link' => 'index.php?tab=AdminTranslations&token=' . Tools::getAdminTokenLite('AdminTranslations') . '&type=modules&module=' . $module->name . '&lang=',
            'module_languages' => $languages,
            'module_name' => $module->name,
            'translateLinks' => $translateLinks,
        ]);

        $modal_content = $this->context->smarty->fetch('controllers/modules/modal_translation.tpl');
        $this->modals[] = [
            'modal_id' => 'moduleTradLangSelect',
            'modal_class' => 'modal-sm',
            'modal_title' => $this->module->l('Translate this module'),
            'modal_content' => $modal_content,
        ];

        $modal_content = $this->context->smarty->fetch('controllers/modules/' . (($this->context->mode == Context::MODE_HOST) ? 'modal_not_trusted_blocked.tpl' : 'modal_not_trusted.tpl'));
        $this->modals[] = [
            'modal_id' => 'moduleNotTrusted',
            'modal_class' => 'modal-lg',
            'modal_title' => ($this->context->mode == Context::MODE_HOST) ? $this->module->l('This module cannot be installed') : $this->module->l('Important Notice'),
            'modal_content' => $modal_content,
        ];

        $modal_content = $this->context->smarty->fetch('controllers/modules/modal_not_trusted_country.tpl');
        $this->modals[] = [
            'modal_id' => 'moduleNotTrustedCountry',
            'modal_class' => 'modal-lg',
            'modal_title' => $this->module->l('This module is Untrusted for your country', [], 'Admin.Modules.Feature'),
            'modal_content' => $modal_content,
        ];
    }

        /**
     * This function sets various display options for helper list.
     *
     * @param Helper $helper
     */
    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);
        if(isset($this->title_icon))
            $this->helper->title_icon = $this->title_icon;
    }
}