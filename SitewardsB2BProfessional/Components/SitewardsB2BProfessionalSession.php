<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalSession
 * Basic helper functionality for session handling
 */
class Shopware_Components_SitewardsB2BProfessionalSession
{

    /**
     * resets the session and logs out the customer
     */
    public function logoutCustomer()
    {
        Shopware()->Session()->unsetAll();
    }
}