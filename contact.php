<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Contact extends Module
{
    public function __construct()
    {
        $this->name = 'contact';
        $this->tab = 'front_office_features';
        $this->version = '1';
        $this->author = 'Agnieszka Kurzajewska';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('Contact');
        $this->description = $this->l('Contact box on the front office - contains e-mail address and phone number');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');


        if (!Configuration::get('CONTACT')) {
            $this->warning = $this->l('No name provided');

        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
    
        return parent::install() &&
            $this->registerHook('leftColumn') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            Configuration::updateValue('PHONE', '+48 000 000 000') &&
            Configuration::updateValue('EMAIL', 'example@email.com');
    }
    
    public function uninstall()
{
    if (!parent::uninstall() ||
        !Configuration::deleteByName('PHONE') ||
        !Configuration::deleteByName('EMAIL')
    ) {
        return false;
    }

    return true;
}


    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $phone = strval(Tools::getValue('PHONE'));
            $email = strval(Tools::getValue('EMAIL'));

            if (
                !$phone ||
                empty($phone) ||
                !Validate::isGenericName($phone)
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('PHONE', $phone);
                Configuration::updateValue('EMAIL', $email);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }
    


    public function displayForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Data'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('E-mail'),
                    'name' => 'EMAIL',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Phone number'),
                    'name' => 'PHONE',
                    'size' => 20,
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
        

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];
        
        $helper->fields_value['PHONE'] = Tools::getValue('PHONE', Configuration::get('PHONE'));
        $helper->fields_value['EMAIL'] = Tools::getValue('EMAIL', Configuration::get('EMAIL'));

        return $helper->generateForm($fieldsForm);
        
    }

    public function hookDisplayLeftColumn($params)
    {
        $this->context->smarty->assign([
            'PHONE' => Configuration::get('PHONE'),
            'EMAIL'=> Configuration::get('EMAIL'),
            'my_module_link' => $this->context->link->getModuleLink('contact', 'display')
        ]);

        return $this->display(__FILE__, 'displayData.tpl');
    }

    // public function hookActionFrontControllerSetMedia()
    // {
    //     $this->context->controller->registerStylesheet(
    //         'contact-style',
    //         $this->_path.'views/css/contact.css',
    //         [
    //             'media' => 'all',
    //             'priority' => 1000,
    //         ]
    //     );
    // }
}