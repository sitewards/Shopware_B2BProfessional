<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalCustomer
 * Basic helper functionality for customer handling
 */
class Shopware_Components_SitewardsB2BProfessionalCustomer
{
    /**
     * returns the customer repository
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getCustomerRepository()
    {
        return Shopware()->Models()
            ->getRepository('Shopware\\Models\\Customer\\Customer');
    }

    /**
     * returns query builder for a customer by his id
     *
     * @param $iCustomerId
     * @return Shopware\Components\Model\QueryBuilder|\Doctrine\ORM\QueryBuilder
     */
    protected function getCustomerQueryBuilder($iCustomerId)
    {
        return $this->getCustomerRepository()
            ->getCustomerDetailQueryBuilder($iCustomerId);
    }

    /**
     * retrieves a customer by email address
     *
     * @return \Shopware\Models\Customer\Customer
     */
    public function getLoggedInCustomer()
    {
        /** @var \Shopware\Components\Model\ModelRepository $oCustomerRepository */
        $oCustomerRepository = $this->getCustomerRepository();

        /** @var Shopware\Models\Customer\Customer $oCustomer */
        $oCustomer = $oCustomerRepository->findOneBy(
            array(
                'id' => Shopware()->Session()->sUserId
            )
        );

        return $oCustomer;
    }

    /**
     * deactivates a customer
     *
     * @param \Shopware\Models\Customer\Customer $oCustomer
     */
    public function deactivateCustomer($oCustomer)
    {
        /** @var Shopware\Components\Model\QueryBuilder $oCustomerQueryBuilder */
        $oCustomerQueryBuilder = $this->getCustomerQueryBuilder($oCustomer->getId());

        $oCustomerQueryBuilder->update()
            ->set('customer.active', 0)
            ->getQuery()
            ->execute();
    }
}