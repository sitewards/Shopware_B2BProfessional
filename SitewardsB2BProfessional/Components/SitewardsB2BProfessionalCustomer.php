<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalCustomer
 * Basic helper functionality for customer handling
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SitewardsB2BProfessionalCustomer
{
    /**
     * returns the customer repository
     *
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getCustomerRepository(\Shopware\Components\Model\ModelManager $oModelManager)
    {
        return $oModelManager->getRepository('Shopware\\Models\\Customer\\Customer');
    }

    /**
     * returns query builder for a customer by his id
     *
     * @param int $iCustomerId
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return \Shopware\Components\Model\QueryBuilder|\Doctrine\ORM\QueryBuilder
     */
    protected function getCustomerQueryBuilder($iCustomerId, \Shopware\Components\Model\ModelManager $oModelManager)
    {
        /** @var \Shopware\Components\Model\ModelRepository $oCustomerRepository */
        $oCustomerRepository = $this->getCustomerRepository($oModelManager);
        return $oCustomerRepository->getCustomerDetailQueryBuilder($iCustomerId);
    }

    /**
     * retrieves a customer by email address
     *
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @param Enlight_Components_Session_Namespace $oSession
     * @return \Shopware\Models\Customer\Customer
     */
    public function getLoggedInCustomer(
        \Shopware\Components\Model\ModelManager $oModelManager,
        Enlight_Components_Session_Namespace $oSession
    )
    {
        /** @var \Shopware\Components\Model\ModelRepository $oCustomerRepository */
        $oCustomerRepository = $this->getCustomerRepository($oModelManager);

        /** @var \Shopware\Models\Customer\Customer $oCustomer */
        $oCustomer = $oCustomerRepository->findOneBy(
            array(
                'id' => $oSession->sUserId
            )
        );

        return $oCustomer;
    }

    /**
     * deactivates a customer
     *
     * @param \Shopware\Models\Customer\Customer $oCustomer
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     */
    public function deactivateCustomer($oCustomer, \Shopware\Components\Model\ModelManager $oModelManager)
    {
        /** @var \Shopware\Components\Model\QueryBuilder $oCustomerQueryBuilder */
        $oCustomerQueryBuilder = $this->getCustomerQueryBuilder($oCustomer->getId(), $oModelManager);

        $oCustomerQueryBuilder->update();
        $oCustomerQueryBuilder->set('customer.active', 0);
        $oCustomerQueryBuilder->getQuery();
        $oCustomerQueryBuilder->execute();
    }

    /**
     * checks if the customer is logged in
     * #SHOPWARE-1 refactor this method if the check for logged in customer is refactored in the core
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return Shopware()->Modules()->Admin()->sCheckUser();
    }
}