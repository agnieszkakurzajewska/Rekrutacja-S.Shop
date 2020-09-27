<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Contact extends Module
{
    public function __construct()
    {
        $this->name = 'contact';
        $this->tab = 'administration';
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
        return parent::install() &&
            $this->registerHook('leftColumn') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            Configuration::updateValue('email', 'example@email.com');
            Configuration::updateValue('phone_number', '+48 000 000 000');
    }

    public function getContent()
    {
        $output = null;
    
        if (Tools::isSubmit('submit'.$this->name)) {
            $contact = strval(Tools::getValue('CONTACT'));
    
            if (
                !$contact ||
                empty($contact) ||
                !Validate::isGenericName($contact)
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('CONTACT', $contact);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
    
        return $output.$this->displayForm();
    }
    
    public function displayForm()
    {
    $fieldsForm[0]['form'] = [
        'legend' => [
            'title' => $this->l('Data'),
        ],
        'input' => [
            [
                'type' => 'text',
                'label' => $this->l('E-mail'),
                'name' => 'email',
                'size' => 20,
                'required' => true
            ],
            [
                'type' => 'text',
                'label' => $this->l('Phone number'),
                'name' => 'phone_number',
                'size' => 20,
                'required' => true
            ],
        ],
        'submit' => [
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        ]
    ];
    $fieldsForm[1]['form'] = [
        'legend' => [
            'title' => $this->l('CSS settings'),
        ],
        'input' => [
            [
                'type' => 'text',
                'label' => $this->l('Kolor'),
                'name' => 'color',
                'size' => 20,
                'required' => true
            ],
            [
                'type' => 'text',
                'label' => $this->l('Dupa'),
                'name' => 'dupa',
                'size' => 20,
                'required' => true
            ],
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

    $helper->title = $this->displayName;
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

    $helper->fields_value['CONTACT'] = Tools::getValue('CONTACT', Configuration::get('CONTACT'));

    return $helper->generateForm($fieldsForm);
    }

    public function hookDisplayLeftColumn($params)
    {

        $email = Configuration::get('email');
        $phone_number = Configuration::get('phone_number');
        $this->context->smarty->assign([
            'contact_name' => Configuration::get('CONTACT'),
            'contact_link' => $this->context->link->getModuleLink('contact', 'display'),
            'email' => $this->l($email),
            'phone_number' => $this->l($phone_number),

        ]);

        return $this->display(__FILE__, 'displayData.tpl');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'contact-style',
            $this->_path.'views/css/contact.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'contact-javascript',
            $this->_path.'views/js/contact.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }
}