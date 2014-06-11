<?php

/**
 * Class Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap
 * Bootstrapping the main functionality of the B2BProfessional extension
 */
class Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    const S_PLUGIN_NAME    = 'Sitewards B2BProfessional';
    const S_PLUGIN_VENDOR = 'Sitewards GmbH';
    const S_PLUGIN_VENDOR_URL = 'http://www.sitewards.com';
    const S_PLUGIN_VENDOR_EMAIL = 'shopware@sitewards.com';
    const S_PLUGIN_DESCRIPTION = 'The extension offers some basic B2B functionality';
    protected $sPluginVersion = '1.0.31';

    const S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED = 'customer_activation_required';
    protected $sConfigFlagCustomerActivationRequiredDefault = 0;

    const S_CONFIG_FLAG_LOGIN_REQUIRED_HINT = 'customer_login_required_hint';
    protected $sConfigFlagLoginRequiredHintDefault = 'Bitte einloggen';

    const S_ATTRIBUTE_NAME_DELIVERY_DATE = 'delivery_date';

    /** @var \Shopware_Components_SitewardsB2BProfessionalFactory */
    private $oComponentFactory;

    /**
     * constructor
     *
     * @param string $sName
     * @param Enlight_Config|null $oInfo
     */
    public function __construct($sName, $oInfo = null)
    {
        parent::__construct($sName, $oInfo);
        $this->registerNamespaceComponents();
        $this->oComponentFactory = new Shopware_Components_SitewardsB2BProfessionalFactory();
    }

    /**
     * returns the component factory
     *
     * @return Shopware_Components_SitewardsB2BProfessionalFactory
     */
    protected function getComponentFactory()
    {
        return $this->oComponentFactory;
    }

    /**
     * registers template directory
     *
     * @param Enlight_View_Default $oView
     */
    protected function registerTemplateDir(Enlight_View_Default $oView)
    {
        $oView->addTemplateDir($this->Path() . 'Views/');
    }

    /**
     * registers components namespace
     */
    protected function registerNamespaceComponents()
    {
        Shopware()->Loader()->registerNamespace('Shopware_Components', $this->Path() . 'Components/');
    }

    /**
     * registers the snippets directory
     */
    protected function registerSnippetDir()
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
    }

    /**
     * returns a config value for the extension
     *
     * @param string $sConfigFlag
     * @param mixed $mDefault
     * @return mixed
     */
    protected function getConfigValue($sConfigFlag, $mDefault)
    {
        return $this->Config()->get($sConfigFlag, $mDefault);
    }

    /**
     * returns the capabilities of the extension
     *
     * @return bool[]
     */
    public function getCapabilities()
    {
        return array(
            'install' => TRUE,
            'update'  => TRUE,
            'enable'  => TRUE
        );
    }

    /**
     * returns the label of the extension
     *
     * @return string
     */
    public function getLabel()
    {
        return self::S_PLUGIN_NAME;
    }

    /**
     * returns the version of the extension
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->sPluginVersion;
    }

    /**
     * returns the overall information about the extension
     *
     * @return string[]
     */
    public function getInfo()
    {
        return array(
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
            'supplier'    => self::S_PLUGIN_VENDOR,
            'description' => self::S_PLUGIN_DESCRIPTION,
            'support'     => self::S_PLUGIN_VENDOR_EMAIL,
            'link'        => self::S_PLUGIN_VENDOR_URL
        );
    }

    /**
     * installation method
     *
     * @return bool|array
     */
    public function install()
    {
        try {
            $this->subscribeEvents();
            $this->createConfigurationForm();
            $this->addModelAttributes();
            return array(
                'success' => TRUE,
                'invalidateCache' => array(
                    'backend',
                    'frontend',
                    'proxy'
                )
            );
        } catch (\Exception $oException) {
            return array(
                'success' => FALSE,
                'message' => $oException->getMessage()
            );
        }
    }

    /**
     * creates a form for extension configuration
     */
    public function createConfigurationForm()
    {
        $oForm = $this->Form();
        $oParent = $this->Forms()->findOneBy(
            array(
                'name' => 'Backend'
            )
        );
        $oForm->setParent($oParent);

        $oForm->setElement(
            'checkbox',
            self::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED,
            array(
                'label' => 'Activation required for customers\' login',
                'value' => FALSE,
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $oForm->setElement(
            'html',
            self::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
            array(
                'label' => 'Hint to be shown instead of prices if customer is not logged in',
                'value' => 'Bitte einloggen',
                'scope' => Shopware\Models\Config\Element::SCOPE_LOCALE
            )
        );
    }

    /**
     * subscribes all events necessary for the extension
     */
    protected function subscribeEvents()
    {
        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Register::saveRegister::after',
            'processUserRegistration'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SitewardsB2B',
            'registerB2BProfessionalController'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing',
            'processProductDisplaying'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'processProductDisplaying'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Note',
            'processProductDisplaying'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Index',
            'processProductDisplaying'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
            'addDeliveryDateField'
        );

        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after',
            'saveDeliveryDate'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Order',
            'addDeliveryDateInformation'
        );

        $this->subscribeEvent(
            'Shopware\\Models\\Order\\Repository::getBackendAdditionalOrderDataQuery::replace',
            'addAttributesToOrderList'
        );
    }

    /**
     * adds attributes to existing models
     */
    protected function addModelAttributes()
    {
        $this->getComponentFactory()->getComponent('Installer')
            ->addAttribute(
                's_order_attributes',
                self::S_ATTRIBUTE_NAME_DELIVERY_DATE,
                'varchar(255)'
            );
    }

    /**
     * registers the frontend controller path
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return string
     */
    public function registerB2BProfessionalController(Enlight_Event_EventArgs $oArguments)
    {
        return $this->Path() . 'Controllers/Frontend/SitewardsB2BController.php';
    }

    /**
     * adds delivery date attribute to the orders' list query
     *
     * @param Enlight_Hook_HookArgs $oArguments
     */
    public function addAttributesToOrderList(Enlight_Hook_HookArgs $oArguments)
    {
        $aParams = $oArguments->getArgs();
        $iOrderNumber = $aParams[0];

        $oQuery = $this->getComponentFactory()->getComponent('Order')
            ->getBackendAdditionalOrderDataQuery($iOrderNumber);

        $oArguments->setReturn($oQuery);
    }

    /**
     * adds new template for the delivery date on checkout confirmation
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function addDeliveryDateField(Enlight_Event_EventArgs $oArguments)
    {
        $oView = $oArguments->getSubject()->View();

        $this->extendTemplates(
            $oView,
            array('frontend/checkout/confirmation_delivery_date.tpl')
        );

        return true;
    }

    /**
     * adds information about delivery date to the backend view of an order
     *
     * @param Enlight_Event_EventArgs $oArguments
     */
    public function addDeliveryDateInformation(Enlight_Event_EventArgs $oArguments)
    {
        /** @var Enlight_View_Default $oView */
        $oView = $oArguments->getSubject()->View();

        $this->registerSnippetDir();

        $this->registerTemplateDir($oView);

        if ($oArguments->getRequest()->getActionName() === 'load') {

            $this->extendTemplates(
                $oView,
                array(
                    'backend/b2bprofessional/order/model/order.js',
                    'backend/b2bprofessional/order/view/list/list.js',
                    'backend/b2bprofessional/order/view/detail/overview.js',
                )
            );
        }
    }

    /**
     * disables price information in the frontend
     * and hide the add-to-cart button
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function processProductDisplaying(Enlight_Event_EventArgs $oArguments)
    {
        $bUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();

        if (!$bUserLoggedIn) {

            /** @var Shopware_Components_SitewardsB2BProfessionalFakeCurrency $oFakeCurrencyComponent */
            $oFakeCurrencyComponent = $this->getComponentFactory()->getComponent(
                'FakeCurrency',
                array(
                    'setPriceReplacementMessage' => array(
                        $this->getConfigValue(
                            self::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
                            $this->sConfigFlagLoginRequiredHintDefault
                        )
                    )
                )
            );

            $this->Application()->Bootstrap()->registerResource('Currency', $oFakeCurrencyComponent);
        }

        /** @var Shopware_Controllers_Frontend_Listing $oController */
        $oController = $oArguments->getSubject();

        try {
            /** @var Enlight_View_Default $oView */
            $oView = $oController->View();
        } catch (Exception $oException) {
            // we have no view, we are done here
            return true;
        }

        /** @var Enlight_Controller_Request_RequestHttp $oRequest */
        $oRequest = $oController->Request();

        $bIsFrontend = $oRequest->getModuleName() === 'frontend';
        $bTemplateExists = $oView->hasTemplate();

        if (!$bIsFrontend || !$bTemplateExists) {
            return true;
        }

        $this->extendTemplates(
            $oView,
            array(
                'frontend/detail/detail_addtocart_button.tpl',
                'frontend/listing/listing_addtocart_button.tpl',
                'frontend/header/cart_section.tpl'
            )
        );

        return true;
    }

    /**
     * extends a view with the given templates 
     *
     * @param Enlight_View_Default $oView
     * @param string[] $aTemplates
     */
    protected function extendTemplates($oView, $aTemplates = array())
    {
        $this->registerTemplateDir($oView);
        foreach ($aTemplates as $sTemplate) {
            $oView->extendsTemplate($sTemplate);
        }
    }

    /**
     * handles the user registration
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function processUserRegistration(Enlight_Hook_HookArgs $oArguments)
    {
        $bCustomerActivationRequired = $this->getConfigValue(
            self::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED,
            $this->sConfigFlagCustomerActivationRequiredDefault
        );

        if (!$bCustomerActivationRequired) {
            return true;
        }

        /** @var Shopware_Components_SitewardsB2BProfessionalCustomer $oCustomerComponent */
        $oCustomerComponent = $this->getComponentFactory()->getComponent('Customer');

        /** @var \Shopware\Models\Customer\Customer $oCustomer */
        $oCustomer = $oCustomerComponent->getLoggedInCustomer();

        if (!$oCustomer) {
            return true;
        }

        $oCustomerComponent->deactivateCustomer($oCustomer);

        /** @var Shopware_Components_SitewardsB2BProfessionalSession $oSessionComponent */
        $oSessionComponent = $this->getComponentFactory()->getComponent('Session');

        $oSessionComponent->logoutCustomer();

        $oArguments->getSubject()->redirect(
            array(
                'controller' => 'SitewardsB2B',
                'action'     => 'registration'
            ),
            array(
                'code' => 302
            )
        );

        return true;

    }

    /**
     * saves the delivery date of the newly created order
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function saveDeliveryDate(Enlight_Hook_HookArgs $oArguments)
    {
        $iOrderNumber = $oArguments->getReturn();
        $sDeliveryDate = Shopware()->Front()->Request()
            ->getParam(self::S_ATTRIBUTE_NAME_DELIVERY_DATE, '');

        if ($iOrderNumber && $sDeliveryDate) {
            $this->getComponentFactory()->getComponent('Order')
                ->saveDeliveryDate($iOrderNumber, $sDeliveryDate);
        }

    }

}