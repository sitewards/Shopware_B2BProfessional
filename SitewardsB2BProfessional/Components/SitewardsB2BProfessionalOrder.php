<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalOrder
 * Basic helper functionality for order handling
 */
class Shopware_Components_SitewardsB2BProfessionalOrder
    implements Shopware_Components_SitewardsB2BProfessionalInterface
{
    /**
     * returns the order repository
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getOrderRepository()
    {
        return Shopware()->Models()
            ->getRepository('Shopware\\Models\\Order\\Order');
    }

    /**
     * returns the order attribute repository
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getOrderAttributeRepository()
    {
        return Shopware()->Models()
            ->getRepository('Shopware\\Models\\Attribute\\Order');
    }

    /**
     * returns query builder for an order by its id
     *
     * @param $iOrderId
     * @return Shopware\Components\Model\QueryBuilder|\Doctrine\ORM\QueryBuilder
     */
    protected function getOrderQueryBuilder($iOrderId)
    {
        return $this->getOrderRepository()
            ->getOrderDetailQueryBuilder($iOrderId);
    }

    /**
     * retrieves an order by order number
     *
     * @param $iOrderNumber
     * @return \Shopware\Models\Order\Order
     */
    public function getOrderByNumber($iOrderNumber)
    {
        /** @var \Shopware\Components\Model\ModelRepository $oOrderRepository */
        $oOrderRepository = $this->getOrderRepository();

        /** @var Shopware\Models\Order\Order $oOrder */
        $oOrder = $oOrderRepository->findOneBy(
            array(
                'number' => $iOrderNumber
            )
        );

        return $oOrder;
    }

    /**
     * returns order attribute by order number
     *
     * @param int $iOrderNumber
     * @return \Shopware\Models\Attribute\Order|null
     */
    public function getOrderAttributesByOrderNumber($iOrderNumber)
    {
        /** @var \Shopware\Models\Order\Order $oOrder */
        $oOrder = $this->getOrderByNumber($iOrderNumber);

        /** @var \Shopware\Components\Model\ModelRepository $oOrderAttributeRepository */
        $oOrderAttributeRepository = $this->getOrderAttributeRepository();

        /** @var Shopware\Models\Attribute\Order $oOrderAttribute */
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
     */
    public function saveDeliveryDate($iOrderNumber, $sDeliveryDate)
    {
        /** @var Shopware\Models\Attribute\Order $oOrderAttributes */
        $oOrderAttributes = $this->getOrderAttributesByOrderNumber($iOrderNumber);

        if ($sDeliveryDate && $oOrderAttributes instanceof Shopware\Models\Attribute\Order) {
            $oOrderAttributes->setB2bprofessionalDeliveryDate($sDeliveryDate);
            Shopware()->Models()->persist($oOrderAttributes);
            Shopware()->Models()->flush();
        }
    }

    /**
     * creates a query used for orders' backend list generation
     *
     * @param int $iOrderNumber
     * @return \Doctrine\ORM\Query
     */
    public function getBackendAdditionalOrderDataQuery($iOrderNumber)
    {
        $oBuilder = $this->getOrderRepository()->createQueryBuilder('orders');

        $oBuilder->select(array(
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
        $oBuilder->leftJoin('orders.documents', 'documents')
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

        $oBuilder->where('orders.number = :orderNumber');
        $oBuilder->setParameter('orderNumber', $iOrderNumber);

        $oQuery = $oBuilder->getQuery();

        return $oQuery;

    }

}