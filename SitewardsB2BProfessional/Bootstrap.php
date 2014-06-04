<?php

/**
 * Class Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap
 * Bootstrapping the main functionality of the B2BProfessional extension
 */
class Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    const S_PLUGIN_NAME    = 'Sitewards B2BProfessional';
    const S_PLUGIN_VERSION = '1.0.5';
    const S_PLUGIN_VENDOR = 'Sitewards GmbH';
    const S_PLUGIN_VENDOR_URL = 'http://www.sitewards.com';
    const S_PLUGIN_VENDOR_EMAIL = 'shopware@sitewards.com';
    const S_PLUGIN_DESCRIPTION = 'The extension offers some basic B2B functionality';

    const S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED = 'customer_activation_required';
    const S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED_DEFAULT = 0;

    const S_CONFIG_FLAG_LOGIN_REQUIRED_HINT = 'customer_login_required_hint';
    const S_CONFIG_FLAG_LOGIN_REQUIRED_HINT_DEFAULT = 'Bitte einloggen';

    /** @var Shopware_Components_SitewardsB2BProfessionalCustomer */
    private $oCustomerComponent;
    /** @var Shopware_Components_SitewardsB2BProfessionalSession */
    private $oSessionComponent;
    /** @var Shopware_Components_SitewardsB2BProfessionalSnippet */
    private $oSnippetComponent;

    /**
     * constructor
     *
     * @param string $name
     * @param Enlight_Config|null $info
     */
    public function __construct($name, $info = null)
    {
        parent::__construct($name, $info);
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
        return static::S_PLUGIN_NAME;
    }

    /**
     * returns the version of the extension
     *
     * @return string
     */
    public function getVersion()
    {
        return static::S_PLUGIN_VERSION;
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
            'supplier'    => static::S_PLUGIN_VENDOR,
            'description' => static::S_PLUGIN_DESCRIPTION,
            'support'     => static::S_PLUGIN_VENDOR_EMAIL,
            'link'        => static::S_PLUGIN_VENDOR_URL
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
            $this->createTranslations();
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
     * creates frontend translations
     */
    protected function createTranslations()
    {
        $oSnippetComponent = $this->getSnippetComponent();

        $oSnippetComponent->addTranslation(
            'engine/Shopware/Plugins/Community/Backend/SitewardsB2BProfessional/Views/frontend/registration',
            'B2BRegistrationConfirmation',
            1,
            2,
            'Thank you for the registration. We will check your data and activate your account as soon as possible');
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
            static::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED,
            array(
                'label' => 'Activation required for customers\' login',
                'value' => FALSE,
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $oForm->setElement(
            'html',
            static::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
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
            'onFrontendAccountSaveRegisterAfter'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SitewardsB2B',
            'onGetSitewardsB2BPathFrontend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing',
            'onPostDispatchFrontendProduct'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'onPostDispatchFrontendProduct'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Note',
            'onPostDispatchFrontendProduct'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Index',
            'onPostDispatchFrontendProduct'
        );
    }

    /**
     * registers the frontend controller path
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return string
     */
    public function onGetSitewardsB2BPathFrontend(Enlight_Event_EventArgs $oArguments)
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
     * disables price information in the frontend
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function onPostDispatchFrontendProduct(Enlight_Event_EventArgs $oArguments)
    {
        try {
            /** @var Shopware_Controllers_Frontend_Listing $oController */
            $oController = $oArguments->getSubject();
            /** @var Enlight_View_Default $oView */
            $oView = $oController->View();
            /** @var Enlight_Controller_Request_RequestHttp $oRequest */
            $oRequest = $oController->Request();

            $bIsFrontend = $oRequest->getModuleName() === 'frontend';
            $bTemplateExists = $oView->hasTemplate();
            $bUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();

            if (!$bIsFrontend || !$bTemplateExists || $bUserLoggedIn) {
                return true;
            }

            $this->registerTemplateDir($oView);
            $oView->extendsTemplate('frontend/detail/detail_price.tpl');
            $oView->extendsTemplate('frontend/detail/similar_price.tpl');
            $oView->extendsTemplate('frontend/listing/listing_price.tpl');
            $oView->extendsTemplate('frontend/note/item_price.tpl');

            $oView->assign(
                'login_hint',
                $this->getConfigValue(
                    static::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
                    static::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT_DEFAULT
                )
            );

            return true;
        } catch (Exception $oException) {
            return true;
        }
    }

    /**
     * handles the user registration
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function onFrontendAccountSaveRegisterAfter(Enlight_Hook_HookArgs $oArguments)
    {
        $bExtensionActivated = $this->getConfigValue(
            static::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED,
            static::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED_DEFAULT
        );

        if (!$bExtensionActivated) {
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