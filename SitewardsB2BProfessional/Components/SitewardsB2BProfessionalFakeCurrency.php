<?php

class Shopware_Components_SitewardsB2BProfessionalFakeCurrency
    extends Zend_Currency
    implements Shopware_Components_SitewardsB2BProfessionalInterface
{

    /** @var string */
    private $sPriceReplacementMessage = '';

    /**
     * sets a message to be displayed instead of price
     *
     * @param string $sPriceReplacementMessage
     */
    public function setPriceReplacementMessage($sPriceReplacementMessage)
    {
        $this->sPriceReplacementMessage = $sPriceReplacementMessage;
    }

    /**
     * returns a message instead of formatted price
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