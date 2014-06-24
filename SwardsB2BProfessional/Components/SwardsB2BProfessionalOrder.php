<?php

/**
 * Class Shopware_Components_SwardsB2BProfessionalOrder
 * Basic helper functionality for order handling
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SwardsB2BProfessionalOrder
{

    /**
     * returns the order repository
     *
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getOrderRepository(\Shopware\Components\Model\ModelManager $oModelManager)
    {
        return $oModelManager->getRepository('Shopware\\Models\\Order\\Order');
    }

    /**
     * returns the order attribute repository
     *
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getOrderAttributeRepository(\Shopware\Components\Model\ModelManager $oModelManager)
    {
        return $oModelManager->getRepository('Shopware\\Models\\Attribute\\Order');
    }

    /**
     * retrieves an order by order number
     *
     * @param int $iOrderNumber
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return \Shopware\Models\Order\Order
     */
    protected function getOrderByNumber(
        $iOrderNumber,
        \Shopware\Components\Model\ModelManager $oModelManager
    )
    {
        /** @var \Shopware\Components\Model\ModelRepository $oOrderRepository */
        $oOrderRepository = $this->getOrderRepository($oModelManager);

        /** @var \Shopware\Models\Order\Order $oOrder */
        $oOrder = $oOrderRepository->findOneBy(
            array(
                'number' => $iOrderNumber
            )
        );

        return $oOrder;
    }

    /**
     * returns order attributes by order number
     *
     * @param int $iOrderNumber
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @throws Shopware_Components_SwardsB2BProfessionalOrderAttributeNotFoundException
     * @return \Shopware\Models\Attribute\Order
     */
    protected function getOrderAttributesByOrderNumber(
        $iOrderNumber,
        \Shopware\Components\Model\ModelManager $oModelManager
    )
    {
        /** @var \Shopware\Models\Order\Order $oOrder */
        $oOrder = $this->getOrderByNumber($iOrderNumber, $oModelManager);

        /** @var \Shopware\Components\Model\ModelRepository $oOrderAttributeRepository */
        $oOrderAttributeRepository = $this->getOrderAttributeRepository($oModelManager);

        /** @var \Shopware\Models\Attribute\Order $oOrderAttribute */
        $oOrderAttribute = $oOrderAttributeRepository->findOneBy(
            array(
                'orderId' => $oOrder->getId()
            )
        );

        if (!$oOrderAttribute) {
            throw new Shopware_Components_SwardsB2BProfessionalOrderAttributeNotFoundException(
                'Order attribute not found for order number ' . $iOrderNumber
            );
        }

        return $oOrderAttribute;
    }

    /**
     * saves the delivery date of an order
     *
     * @param int $iOrderNumber
     * @param string $sDeliveryDate
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     */
    public function saveDeliveryDate(
        $iOrderNumber,
        $sDeliveryDate,
        \Shopware\Components\Model\ModelManager $oModelManager
    )
    {
        try {
            /** @var \Shopware\Models\Attribute\Order $oOrderAttributes */
            $oOrderAttributes = $this->getOrderAttributesByOrderNumber($iOrderNumber, $oModelManager);
        } catch (Shopware_Components_SwardsB2BProfessionalOrderAttributeNotFoundException $oException) {
            // attributes were not found, we can stop here
            return;
        }

        if ($sDeliveryDate) {
            $oOrderAttributes->setB2bprofessionalDeliveryDate($sDeliveryDate);
            $oModelManager->persist($oOrderAttributes);
            $oModelManager->flush();
        }
    }

    /**
     * creates a query used for orders' backend list generation
     *
     * @param int $iOrderNumber
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     * @return \Doctrine\ORM\Query
     */
    public function getBackendAdditionalOrderDataQuery(
        $iOrderNumber,
        \Shopware\Components\Model\ModelManager $oModelManager
    )
    {
        /** @var \Shopware\Components\Model\ModelRepository $oOrderRepository */
        $oOrderRepository = $this->getOrderRepository($oModelManager);
        /** @var \Doctrine\ORM\QueryBuilder $oQueryBuilder */
        $oQueryBuilder = $oOrderRepository->createQueryBuilder('orders');

        $oQueryBuilder->select(array(
            'orders',
            'details',
            'detailAttribute',
            'documents',
            'documentType',
            'documentAttribute',
            'customer',
            'paymentInstances',
            'debit',
            'shipping',
            'shippingAttribute',
            'shippingCountry',
            'subShop',
            'locale',
            'orderAttributes'
        ));
        $oQueryBuilder->leftJoin('orders.documents', 'documents');
        $oQueryBuilder->leftJoin('documents.type', 'documentType');
        $oQueryBuilder->leftJoin('documents.attribute', 'documentAttribute');
        $oQueryBuilder->leftJoin('orders.details', 'details');
        $oQueryBuilder->leftJoin('details.attribute', 'detailAttribute');
        $oQueryBuilder->leftJoin('orders.customer', 'customer');
        $oQueryBuilder->leftJoin('customer.debit', 'debit');
        $oQueryBuilder->leftJoin('orders.paymentInstances', 'paymentInstances');
        $oQueryBuilder->leftJoin('orders.shipping', 'shipping');
        $oQueryBuilder->leftJoin('shipping.attribute', 'shippingAttribute');
        $oQueryBuilder->leftJoin('shipping.country', 'shippingCountry');
        $oQueryBuilder->leftJoin('orders.languageSubShop', 'subShop');
        $oQueryBuilder->leftJoin('subShop.locale', 'locale');
        $oQueryBuilder->leftJoin('orders.attribute', 'orderAttributes');

        $oQueryBuilder->where('orders.number = :orderNumber');
        $oQueryBuilder->setParameter('orderNumber', $iOrderNumber);

        $oQuery = $oQueryBuilder->getQuery();

        return $oQuery;

    }
}