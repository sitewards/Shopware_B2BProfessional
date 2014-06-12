<?php

/**
 * Class Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap
 * Bootstrapping the main functionality of the B2BProfessional extension
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    const S_PLUGIN_NAME         = 'Sitewards B2BProfessional';
    const S_PLUGIN_VENDOR       = 'Sitewards GmbH';
    const S_PLUGIN_VENDOR_URL   = 'http://www.sitewards.com';
    const S_PLUGIN_VENDOR_EMAIL = 'shopware@sitewards.com';
    const S_PLUGIN_DESCRIPTION  = 'The extension offers some basic B2B functionality';
    protected $sPluginVersion   = '1.0.31';

    const S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED     = 'customer_activation_required';
    public $sConfigFlagCustomerActivationRequiredDefault = 0;

    const S_CONFIG_FLAG_LOGIN_REQUIRED_HINT     = 'customer_login_required_hint';
    public $sConfigFlagLoginRequiredHintDefault = 'Please log in';

    const S_ATTRIBUTE_NAME_DELIVERY_DATE = 'delivery_date';

    /** @var \Shopware_Components_SitewardsB2BProfessionalObserver */
    private $oObserver;

    const S_FRONTEND_MODULE_NAME = 'frontend';

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
        // init the observer component
        $this->oObserver = new Shopware_Components_SitewardsB2BProfessionalObserver($this);
    }

    /**
     * returns the observer object
     *
     * @return Shopware_Components_SitewardsB2BProfessionalObserver
     */
    public function getObserver()
    {
        return $this->oObserver;
    }

    /**
     * registers template directory
     *
     * @param Enlight_View_Default $oView
     */
    public function registerTemplateDir(Enlight_View_Default $oView)
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
    public function registerSnippetDir()
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
     * @return array<string,boolean>
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'update'  => true,
            'enable'  => true
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
     * @return array<string,string>
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
     * @return array
     */
    public function install()
    {
        try {
            $this->subscribeEvents();
            $this->createConfigurationForm();
            $this->addModelAttributes();
            return array(
                'success' => true,
                'invalidateCache' => array(
                    'backend',
                    'frontend',
                    'proxy'
                )
            );
        } catch (\Exception $oException) {
            return array(
                'success' => false,
                'message' => $oException->getMessage()
            );
        }
    }

    /**
     * creates a form for extension configuration
     */
    public function createConfigurationForm()
    {
        $oForm   = $this->Form();
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
                'value' => false,
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
     * registers the frontend controller path
     *
     * @return string
     */
    public function registerB2BProfessionalController()
    {
        return $this->getObserver()->registerB2BProfessionalController();
    }

    /**
     * adds delivery date attribute to the orders' list query
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function addAttributesToOrderList(Enlight_Hook_HookArgs $oArguments)
    {
        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();
        return $this->getObserver()->addAttributesToOrderList($oArguments, $oModelManager);
    }

    /**
     * adds new template for the delivery date on checkout confirmation
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function addDeliveryDateField(Enlight_Event_EventArgs $oArguments)
    {
        return $this->getObserver()->addDeliveryDateField($oArguments);
    }

    /**
     * adds information about delivery date to the backend view of an order
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function addDeliveryDateInformation(Enlight_Event_EventArgs $oArguments)
    {
        return $this->getObserver()->addDeliveryDateInformation($oArguments);
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
        $sPriceReplacementMessage = $this->getConfigValue(
            self::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
            $this->sConfigFlagLoginRequiredHintDefault
        );

        $oCustomerComponent = new Shopware_Components_SitewardsB2BProfessionalCustomer();
        $bCustomerLoggedIn = $oCustomerComponent->isCustomerLoggedIn();

        return $this->getObserver()->processProductDisplaying(
            $oArguments,
            $sPriceReplacementMessage,
            $bCustomerLoggedIn,
            self::S_FRONTEND_MODULE_NAME
        );
    }

    /**
     * saves the delivery date of the newly created order
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function saveDeliveryDate(Enlight_Hook_HookArgs $oArguments)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $oSubject */
        $oSubject = $oArguments->getSubject();
        /** @var Enlight_Controller_Request_RequestHttp $oRequest */
        $oRequest = $oSubject->Request();

        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();

        $sDeliveryDate = $oRequest->getParam(
            self::S_ATTRIBUTE_NAME_DELIVERY_DATE,
            ''
        );

        if (!Zend_Date::isDate($sDeliveryDate)) {
            $sDeliveryDate = '';
        }

        return $this->getObserver()->saveDeliveryDate($oArguments, $sDeliveryDate, $oModelManager);
    }

    /**
     * adds attributes to existing models
     */
    protected function addModelAttributes()
    {
        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();

        $oInstaller = new Shopware_Components_SitewardsB2BProfessionalInstaller($oModelManager);
        $oInstaller->addAttribute(
            's_order_attributes',
            self::S_ATTRIBUTE_NAME_DELIVERY_DATE,
            'varchar(255)'
        );
    }

    /**
     * extends a view with the given templates 
     *
     * @param Enlight_View_Default $oView
     * @param string[] $aTemplates
     */
    public function extendTemplates($oView, $aTemplates = array())
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

        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();

        /** @var Enlight_Components_Session_Namespace $oSession */
        $oSession = $this->getSession();

        return $this->getObserver()->processUserRegistration(
            $oArguments,
            $bCustomerActivationRequired,
            $oModelManager,
            $oSession
        );
    }

    /**
     * returns the model manager
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getModelManager()
    {
        return Shopware()->Models();
    }

    /**
     * returns the session
     *
     * @return Enlight_Components_Session_Namespace
     */
    protected function getSession()
    {
        return Shopware()->Session();
    }

}