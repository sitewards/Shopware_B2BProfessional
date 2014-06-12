<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalOrder
 * Basic helper functionality for order handling
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SitewardsB2BProfessionalOrder
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
     * @return \Shopware\Models\Attribute\Order|null
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
        /** @var \Shopware\Models\Attribute\Order $oOrderAttributes */
        $oOrderAttributes = $this->getOrderAttributesByOrderNumber($iOrderNumber, $oModelManager);

        if ($sDeliveryDate && $oOrderAttributes instanceof \Shopware\Models\Attribute\Order) {
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
        $oQueryBuilder->leftJoin('orders.documents', 'documents')
            ->leftJoin('documents.type', 'documentType')
            ->leftJoin('documents.attribute', 'documentAttribute')
            ->leftJoin('orders.details', 'details')
            ->leftJoin('details.attribute', 'detailAttribute')
            ->leftJoin('orders.customer', 'customer')
            ->leftJoin('customer.debit', 'debit')
            ->leftJoin('orders.paymentInstances', 'paymentInstances')
            ->leftJoin('orders.shipping', 'shipping')
            ->leftJoin('shipping.attribute', 'shippingAttribute')
            ->leftJoin('shipping.country', 'shippingCountry')
            ->leftJoin('orders.languageSubShop', 'subShop')
            ->leftJoin('subShop.locale', 'locale')
            ->leftJoin('orders.attribute', 'orderAttributes');

        $oQueryBuilder->where('orders.number = :orderNumber');
        $oQueryBuilder->setParameter('orderNumber', $iOrderNumber);

        $oQuery = $oQueryBuilder->getQuery();

        return $oQuery;

    }

}