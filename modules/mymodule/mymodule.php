<?php

use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyModule extends Module
{

    public function __construct()
    {
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'R A';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Module min max range');
        $this->description = $this->l('Description of module');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_MIN') || !Configuration::get('MYMODULE_MAX')) {
            $this->warning = $this->l('No name provided');
        }

    }

    public function install()
    {

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() || !$this->registerHook('footer') || !$this->registerHook('header') || !Configuration::updateValue('MYMODULE_MIN', 0) || !Configuration::updateValue('MYMODULE_MAX', 0)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('MYMODULE_MIN') || !Configuration::deleteByName('MYMODULE_MAX')) {
            return false;
        }

        return true;
    }


    public function hookDisplayFooter($params)
    {

        $minRange = Configuration::get('MYMODULE_MIN');
        $maxRange = Configuration::get('MYMODULE_MAX');

        $query = "SELECT count(*) FROM ". _DB_PREFIX_ ."product as p WHERE p.price > $minRange AND p.price < $maxRange ";
        $data = Db::getInstance()->getValue($query);

        $tmp = new ProductSearchQuery();
        $tmp->getQueryType();


        $this->context->smarty->assign([
            'data' => $data,
            'minRange' => $minRange,
            'maxRange' => $maxRange,
        ]);
        return $this->display(__FILE__, 'footer.tpl');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path.'css/module.css', 'all');
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $myModuleMin = strval(Tools::getValue('MYMODULE_MIN'));
            $myModuleMax = strval(Tools::getValue('MYMODULE_MAX'));
            if (!$myModuleMin || empty($myModuleMin) || !$myModuleMax || empty($myModuleMax) || !Validate::isGenericName($myModuleMin) || !Validate::isGenericName($myModuleMax)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('MYMODULE_MIN', $myModuleMin);
                Configuration::updateValue('MYMODULE_MAX', $myModuleMax);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $feildsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l("Min value"),
                    'name' => 'MYMODULE_MIN',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l("Max value"),
                    'name' => 'MYMODULE_MAX',
                    'size' => 10,
                    'required' => true
                ]

            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value
        $helper->fields_value['MYMODULE_MIN'] = Configuration::get('MYMODULE_MIN');
        $helper->fields_value['MYMODULE_MAX'] = Configuration::get('MYMODULE_MAX');

        return $helper->generateForm($feildsForm);

    }

}