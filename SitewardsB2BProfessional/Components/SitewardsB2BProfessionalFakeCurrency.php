<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalFakeCurrency
 * Replacement for currency model to display a standard message in
 * the frontend if customer is not logged in
 */
class Shopware_Components_SitewardsB2BProfessionalFakeCurrency extends Zend_Currency
{

    /** @var string */
    private $sPriceReplacementMessage = '';

    /**
     * constructor
     * sets a message to be displayed instead of price
     *
     * @param string $sPriceReplacementMessage
     */
    public function __construct($sPriceReplacementMessage)
    {
        $this->sPriceReplacementMessage = $sPriceReplacementMessage;
    }

    /**
     * returns a message instead of formatted price
     * overrides the original Zend_Currency method
     *
     * @param string|null $sValue
     * @param array $oOptions
     * @return string
     */
    public function toCurrency($sValue = null, array $oOptions = array())
    {
        return $this->sPriceReplacementMessage;
    }
}