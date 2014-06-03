<?php

/**
 * Class Shopware_Controllers_Frontend_SitewardsB2B
 * Shows the standard message on successful registration
 */
class Shopware_Controllers_Frontend_SitewardsB2B extends Enlight_Controller_Action
{
    /**
     * initializes the controller
     */
    public function init()
    {
        $this->View()->addTemplateDir(dirname(__FILE__) . '/../../Views/');
    }

    /**
     * displays the message
     */
    public function registrationAction()
    {
        $this->View()->loadTemplate('frontend/registration.tpl');
    }
}