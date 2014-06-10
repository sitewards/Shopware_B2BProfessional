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
    protected $sPluginVersion = '1.0.11';

    const S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED = 'customer_activation_required';
    protected $sConfigFlagCustomerActivationRequiredDefault = 0;

    const S_CONFIG_FLAG_LOGIN_REQUIRED_HINT = 'customer_login_required_hint';
    protected $sConfigFlagLoginRequiredHintDefault = 'Bitte einloggen';

    /** @var Shopware_Components_SitewardsB2BProfessionalCustomer */
    private $oCustomerComponent;
    /** @var Shopware_Components_SitewardsB2BProfessionalSession */
    private $oSessionComponent;
    /** @var Shopware_Components_SitewardsB2BProfessionalSnippet */
    private $oSnippetComponent;
    /** @var Shopware_Components_SitewardsB2BProfessionalFakeCurrency */
    private $oFakeCurrencyComponent;

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
     * returns a new customer component
     *
     * @return Shopware_Components_SitewardsB2BProfessionalCustomer
     */
    protected function getCustomerComponent()
    {
        if (!$this->oCustomerComponent) {
            $this->oCustomerComponent = new Shopware_Components_SitewardsB2BProfessionalCustomer();
        }
        return $this->oCustomerComponent;
    }

    /**
     * returns a new session component
     *
     * @return Shopware_Components_SitewardsB2BProfessionalSession
     */
    protected function getSessionComponent()
    {
        if (!$this->oSessionComponent) {
            $this->oSessionComponent = new Shopware_Components_SitewardsB2BProfessionalSession();
        }
        return $this->oSessionComponent;
    }

    /**
     * returns a new snippet component
     *
     * @return Shopware_Components_SitewardsB2BProfessionalSnippet
     */
    protected function getSnippetComponent()
    {
        if (!$this->oSnippetComponent) {
            $this->oSnippetComponent = new Shopware_Components_SitewardsB2BProfessionalSnippet();
        }

        return $this->oSnippetComponent;
    }

    /**
     * returns a new fake currency component
     *
     * @return Shopware_Components_SitewardsB2BProfessionalFakeCurrency
     */
    protected function getFakeCurrencyComponent()
    {
        if (!$this->oFakeCurrencyComponent) {
            $this->oFakeCurrencyComponent = new Shopware_Components_SitewardsB2BProfessionalFakeCurrency();
            $this->oFakeCurrencyComponent->setPriceReplacementMessage(
                $this->getConfigValue(
                    self::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
                    $this->sConfigFlagLoginRequiredHintDefault
                )
            );
        }

        return $this->oFakeCurrencyComponent;
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
            $this->Application()->Bootstrap()->registerResource('Currency', $this->getFakeCurrencyComponent());
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

        $aParams = $oArguments->getSubject()->Request()->getParams();
        $aPersonalData = $aParams['register']['personal'];

        /** @var Shopware_Components_SitewardsB2BProfessionalCustomer $oCustomerComponent */
        $oCustomerComponent = $this->getCustomerComponent();

        /** @var \Shopware\Models\Customer\Customer $oCustomer */
        $oCustomer = $oCustomerComponent->getCustomerByEmail($aPersonalData['email']);

        if (!$oCustomer) {
            return true;
        }

        $oCustomerComponent->deactivateCustomer($oCustomer);

        /** @var Shopware_Components_SitewardsB2BProfessionalSession $oSessionComponent */
        $oSessionComponent = $this->getSessionComponent();

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

}