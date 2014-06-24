<?php

/**
 * Class Shopware_Plugins_Backend_SwardsB2BProfessional_Bootstrap
 * Bootstrapping the main functionality of the B2BProfessional extension
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Plugins_Backend_SwardsB2BProfessional_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    const S_PLUGIN_NAME         = 'Sitewards B2BProfessional';
    const S_PLUGIN_VENDOR       = 'Sitewards GmbH';
    const S_PLUGIN_VENDOR_URL   = 'http://www.sitewards.com';
    const S_PLUGIN_VENDOR_EMAIL = 'shopware@sitewards.com';
    const S_PLUGIN_DESCRIPTION  = 'The extension offers some basic B2B functionality';
    protected $sPluginVersion   = '1.0.34';

    const S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED     = 'customer_activation_required';
    public $bConfigFlagCustomerActivationRequiredDefault = false;

    const S_CONFIG_FLAG_LOGIN_REQUIRED_HINT     = 'customer_login_required_hint';
    public $sConfigFlagLoginRequiredHintDefault = 'Please log in';

    const S_CONFIG_FLAG_SHOW_DELIVERY_DATE     = 'show_delivery_date';
    public $bConfigFlagShowDeliveryDateDefault = false;

    const S_ATTRIBUTE_NAME_DELIVERY_DATE = 'delivery_date';

    /** @var \Shopware_Components_SwardsB2BProfessionalObserver */
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
        $this->oObserver = new Shopware_Components_SwardsB2BProfessionalObserver($this);
    }

    /**
     * returns the observer object
     *
     * @return Shopware_Components_SwardsB2BProfessionalObserver
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
     * returns a string config value for the extension
     *
     * @param string $sConfigFlag
     * @param string $sDefault
     * @return string
     */
    protected function getConfigValueString($sConfigFlag, $sDefault)
    {
        return (string)$this->Config()->get($sConfigFlag, $sDefault);
    }

    /**
     * returns a boolean config value for the extension
     *
     * @param string $sConfigFlag
     * @param boolean $bDefault
     * @return boolean
     */
    protected function getConfigValueBoolean($sConfigFlag, $bDefault)
    {
        return (boolean)$this->Config()->get($sConfigFlag, $bDefault);
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
            'link'        => self::S_PLUGIN_VENDOR_URL,
            'author'      => self::S_PLUGIN_VENDOR
        );
    }

    /**
     * installation method
     *
     * @return array<string,boolean|string,array<string>|string,string>
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

        $oForm->setElement(
            'checkbox',
            self::S_CONFIG_FLAG_SHOW_DELIVERY_DATE,
            array(
                'label' => 'Show delivery date input on the last checkout step',
                'value' => $this->bConfigFlagShowDeliveryDateDefault,
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
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
            'setUserInactiveOnRegistration'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SwardsB2B',
            'getB2bProfessionalControllerPath'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing',
            'setB2bProfessionalLayoutUpdates'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'setB2bProfessionalLayoutUpdates'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Note',
            'setB2bProfessionalLayoutUpdates'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Index',
            'setB2bProfessionalLayoutUpdates'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Compare',
            'setB2bProfessionalLayoutUpdates'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
            'setDeliveryDateField'
        );

        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after',
            'persistDeliveryDate'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Order',
            'setDeliveryDateInformation'
        );

        $this->subscribeEvent(
            'Shopware\\Models\\Order\\Repository::getBackendAdditionalOrderDataQuery::replace',
            'setDeliveryDateOnOrderList'
        );
    }

    /**
     * registers the frontend controller path
     *
     * @return string
     */
    public function getB2bProfessionalControllerPath()
    {
        return $this->getObserver()->getB2bProfessionalControllerPath();
    }

    /**
     * adds delivery date attribute to the orders' list query
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function setDeliveryDateOnOrderList(Enlight_Hook_HookArgs $oArguments)
    {
        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();
        return $this->getObserver()->setDeliveryDateOnOrderList($oArguments, $oModelManager);
    }

    /**
     * adds new template for the delivery date on checkout confirmation
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function setDeliveryDateField(Enlight_Event_EventArgs $oArguments)
    {
        $bDeliveryDateEnabled = $this->getConfigValueBoolean(
            self::S_CONFIG_FLAG_SHOW_DELIVERY_DATE,
            $this->bConfigFlagShowDeliveryDateDefault
        );

        if (!$bDeliveryDateEnabled) {
            return true;
        }

        return $this->getObserver()->setDeliveryDateField($oArguments);
    }

    /**
     * adds information about delivery date to the backend view of an order
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function setDeliveryDateInformation(Enlight_Event_EventArgs $oArguments)
    {
        return $this->getObserver()->setDeliveryDateInformation($oArguments);
    }

    /**
     * disables price information in the frontend
     * and hides the add-to-cart buttons
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function setB2bProfessionalLayoutUpdates(Enlight_Event_EventArgs $oArguments)
    {
        $sPriceReplacementMessage = $this->getConfigValueString(
            self::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
            $this->sConfigFlagLoginRequiredHintDefault
        );

        $oCustomerComponent = new Shopware_Components_SwardsB2BProfessionalCustomer();
        $bCustomerLoggedIn  = $oCustomerComponent->isCustomerLoggedIn();

        if ($bCustomerLoggedIn) {
            return true;
        }

        $this->getObserver()->setPriceReplacement($sPriceReplacementMessage);

        return $this->getObserver()->setB2bProfessionalLayoutUpdates(
            $oArguments,
            $sPriceReplacementMessage,
            self::S_FRONTEND_MODULE_NAME
        );
    }

    /**
     * saves the delivery date of the newly created order
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @return bool
     */
    public function persistDeliveryDate(Enlight_Hook_HookArgs $oArguments)
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

        return $this->getObserver()->persistDeliveryDate($oArguments, $sDeliveryDate, $oModelManager);
    }

    /**
     * adds attributes to existing models
     */
    protected function addModelAttributes()
    {
        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();

        $oInstaller = new Shopware_Components_SwardsB2BProfessionalInstaller($oModelManager);
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
    public function setUserInactiveOnRegistration(Enlight_Hook_HookArgs $oArguments)
    {
        $bCustomerActivationRequired = $this->getConfigValueBoolean(
            self::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED,
            $this->bConfigFlagCustomerActivationRequiredDefault
        );

        /** @var \Shopware\Components\Model\ModelManager $oModelManager */
        $oModelManager = $this->getModelManager();

        /** @var Enlight_Components_Session_Namespace $oSession */
        $oSession = $this->getSession();

        return $this->getObserver()->setUserInactiveOnRegistration(
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