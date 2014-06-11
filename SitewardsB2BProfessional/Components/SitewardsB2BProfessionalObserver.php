<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalObserver
 * Observer for events catched in the bootstrap
 */
class Shopware_Components_SitewardsB2BProfessionalObserver
    implements Shopware_Components_SitewardsB2BProfessionalInterface
{
    /** @var Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap */
    private $oBootstrap;

    /**
     * sets the bootstrap object
     *
     * @param Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap $oBootstrap
     */
    public function setBootstrap(Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap $oBootstrap)
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
     * @return bool
     */
    public function processUserRegistration(Enlight_Hook_HookArgs $oArguments)
    {
        $bCustomerActivationRequired = $this->getBootstrap()->getConfigValue(
            Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap::S_CONFIG_FLAG_CUSTOMER_ACTIVATION_REQUIRED,
            $this->getBootstrap()->sConfigFlagCustomerActivationRequiredDefault
        );

        if (!$bCustomerActivationRequired) {
            return true;
        }

        /** @var Shopware_Components_SitewardsB2BProfessionalCustomer $oCustomerComponent */
        $oCustomerComponent = $this->getBootstrap()->getComponentFactory()->getComponent('Customer');

        /** @var \Shopware\Models\Customer\Customer $oCustomer */
        $oCustomer = $oCustomerComponent->getLoggedInCustomer();

        if (!$oCustomer) {
            return true;
        }

        $oCustomerComponent->deactivateCustomer($oCustomer);

        /** @var Shopware_Components_SitewardsB2BProfessionalSession $oSessionComponent */
        $oSessionComponent = $this->getBootstrap()->getComponentFactory()->getComponent('Session');

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
     * registers the frontend controller path
     *
     * @param Enlight_Event_EventArgs $oArguments
     * @return string
     */
    public function registerB2BProfessionalController(Enlight_Event_EventArgs $oArguments)
    {
        return $this->getBootstrap()->Path() . 'Controllers/Frontend/SitewardsB2BController.php';
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
            $oFakeCurrencyComponent = $this->getBootstrap()->getComponentFactory()->getComponent(
                'FakeCurrency',
                array(
                    'setPriceReplacementMessage' => array(
                        $this->getBootstrap()->getConfigValue(
                            Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap::S_CONFIG_FLAG_LOGIN_REQUIRED_HINT,
                            $this->getBootstrap()->sConfigFlagLoginRequiredHintDefault
                        )
                    )
                )
            );

            $this->getBootstrap()->Application()->Bootstrap()->registerResource('Currency', $oFakeCurrencyComponent);
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
    public function addDeliveryDateField(Enlight_Event_EventArgs $oArguments)
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
     * @return bool
     */
    public function saveDeliveryDate(Enlight_Hook_HookArgs $oArguments)
    {
        $iOrderNumber = $oArguments->getReturn();
        $sDeliveryDate = Shopware()->Front()->Request()
            ->getParam(Shopware_Plugins_Backend_SitewardsB2BProfessional_Bootstrap::S_ATTRIBUTE_NAME_DELIVERY_DATE, '');

        if ($iOrderNumber && $sDeliveryDate) {
            $this->getBootstrap()->getComponentFactory()->getComponent('Order')
                ->saveDeliveryDate($iOrderNumber, $sDeliveryDate);
        }

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

        $oQuery = $this->getBootstrap()->getComponentFactory()->getComponent('Order')
            ->getBackendAdditionalOrderDataQuery($iOrderNumber);

        $oArguments->setReturn($oQuery);
    }

}