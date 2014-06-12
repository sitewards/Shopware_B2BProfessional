<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalObserver
 * Observer for events catched in the bootstrap
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SitewardsB2BProfessionalObserver
{
    /** @var Shopware_Components_Plugin_Bootstrap|Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap */
    private $oBootstrap;

    /**
     * constructor
     *
     * @param Shopware_Components_Plugin_Bootstrap $oBootstrap|Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap
     */
    public function __construct(Shopware_Components_Plugin_Bootstrap $oBootstrap)
    {
        $this->oBootstrap = $oBootstrap;
    }

    /**
     * returns the bootstrap object
     *
     * @return Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap
     */
    public function getBootstrap()
    {
        return $this->oBootstrap;
    }

    /**
     * handles the user registration
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @param bool $bCustomerActivationRequired
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @param Enlight_Components_Session_Namespace $oSession
     * @return bool
     */
    public function setUserInactiveOnRegistration(
        Enlight_Hook_HookArgs $oArguments,
        $bCustomerActivationRequired,
        \Shopware\Components\Model\ModelManager $oModelManager,
        Enlight_Components_Session_Namespace $oSession
    )
    {
        if (!$bCustomerActivationRequired) {
            return true;
        }

        /** @var Shopware_Components_SitewardsB2BProfessionalCustomer $oCustomerComponent */
        $oCustomerComponent = new Shopware_Components_SitewardsB2BProfessionalCustomer();

        /** @var \Shopware\Models\Customer\Customer $oCustomer */
        $oCustomer = $oCustomerComponent->getLoggedInCustomer($oModelManager, $oSession);

        if (!$oCustomer) {
            return true;
        }

        $oCustomerComponent->deactivateCustomer($oCustomer, $oModelManager);

        /** @var Shopware_Components_SitewardsB2BProfessionalSession $oSessionComponent */
        $oSessionComponent = new Shopware_Components_SitewardsB2BProfessionalSession();

        $oSessionComponent->logoutCustomer($oSession);

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
     * registers the frontend controller path
     *
     * @return string
     */
    public function getB2bProfessionalControllerPath()
    {
        return $this->getBootstrap()->Path() . 'Controllers/Frontend/SitewardsB2BController.php';
    }

    /**
     * replaces prices with a predefined message
     *
     * @param string $sPriceReplacementMessage
     */
    public function setPriceReplacement($sPriceReplacementMessage)
    {
        /** @var Shopware_Components_SitewardsB2BProfessionalFakeCurrency $oFakeCurrencyComponent */
        $oFakeCurrencyComponent = new Shopware_Components_SitewardsB2BProfessionalFakeCurrency($sPriceReplacementMessage);
        $this->getBootstrap()->Application()->Bootstrap()->registerResource('Currency', $oFakeCurrencyComponent);
    }


    /**
     * disables price information in the frontend
     * and hide the add-to-cart button
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @param string $sPriceReplacementMessage
     * @param string $sFrontendModuleName
     * @return bool
     */
    public function setB2bProfessionalLayoutUpdates(
        Enlight_Event_EventArgs $oArguments,
        $sPriceReplacementMessage,
        $sFrontendModuleName
    )
    {
        /** @var Shopware_Components_SitewardsB2BProfessionalFakeCurrency $oFakeCurrencyComponent */
        $oFakeCurrencyComponent = new Shopware_Components_SitewardsB2BProfessionalFakeCurrency($sPriceReplacementMessage);

        $this->getBootstrap()->Application()->Bootstrap()->registerResource('Currency', $oFakeCurrencyComponent);

        /** @var Enlight_Controller_Action $oController */
        $oController = $oArguments->getSubject();

        /** @var Enlight_View_Default $oView */
        $oView = $oController->View();

        /** @var Enlight_Controller_Request_RequestHttp $oRequest */
        $oRequest = $oController->Request();

        /** @var Enlight_Controller_Response_ResponseHttp $oResponse */
        $oResponse = $oController->Response();

        $bIsFrontend     = $oRequest->getModuleName() === $sFrontendModuleName;
        $bTemplateExists = $oView->hasTemplate();
        $bIsDispatched   = $oRequest->isDispatched();
        $bIsException    = $oResponse->isException();

        if (!($bIsFrontend && $bTemplateExists && $bIsDispatched && !$bIsException)) {
            return true;
        }

        $this->getBootstrap()->extendTemplates(
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
     * adds new template for the delivery date on checkout confirmation
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function setDeliveryDateField(Enlight_Event_EventArgs $oArguments)
    {
        $oView = $oArguments->getSubject()->View();

        $this->getBootstrap()->extendTemplates(
            $oView,
            array('frontend/checkout/confirmation_delivery_date.tpl')
        );

        return true;
    }

    /**
     * saves the delivery date of the newly created order
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @param string $sDeliveryDate
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return bool
     */
    public function persistDeliveryDate(
        Enlight_Hook_HookArgs $oArguments,
        $sDeliveryDate,
        \Shopware\Components\Model\ModelManager $oModelManager
    )
    {
        $iOrderNumber = $oArguments->getReturn();

        if ($iOrderNumber && $sDeliveryDate) {
            $oOrderComponent = new Shopware_Components_SitewardsB2BProfessionalOrder();
            $oOrderComponent->saveDeliveryDate($iOrderNumber, $sDeliveryDate, $oModelManager);
        }

        return true;
    }

    /**
     * adds information about delivery date to the backend view of an order
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return bool
     */
    public function setDeliveryDateInformation(Enlight_Event_EventArgs $oArguments)
    {
        /** @var Enlight_View_Default $oView */
        $oView = $oArguments->getSubject()->View();

        $this->getBootstrap()->registerSnippetDir();

        $this->getBootstrap()->registerTemplateDir($oView);

        if ($oArguments->getRequest()->getActionName() === 'load') {

            $this->getBootstrap()->extendTemplates(
                $oView,
                array(
                    'backend/b2bprofessional/order/model/order.js',
                    'backend/b2bprofessional/order/view/list/list.js',
                    'backend/b2bprofessional/order/view/detail/overview.js',
                )
            );
        }

        return true;
    }

    /**
     * adds delivery date attribute to the orders' list query
     *
     * @param Enlight_Hook_HookArgs $oArguments
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return bool
     */
    public function setDeliveryDateOnOrderList(
        Enlight_Hook_HookArgs $oArguments,
        \Shopware\Components\Model\ModelManager $oModelManager
    )
    {
        $aParams      = $oArguments->getArgs();
        $iOrderNumber = $aParams[0];

        $oOrderComponent = new Shopware_Components_SitewardsB2BProfessionalOrder();
        $oQuery          = $oOrderComponent->getBackendAdditionalOrderDataQuery($iOrderNumber, $oModelManager);

        $oArguments->setReturn($oQuery);

        return true;
    }
}