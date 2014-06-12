<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalSession
 * Basic helper functionality for session handling
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SitewardsB2BProfessionalSession
{

    /**
     * resets the session and logs out the customer
     *
     * @param Enlight_Components_Session_Namespace $oSession
     */
    public function logoutCustomer(Enlight_Components_Session_Namespace $oSession)
    {
        $oSession->unsetAll();
    }
}