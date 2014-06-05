<?php

class Shopware_Components_SitewardsB2BProfessionalFakeCurrency extends Zend_Currency
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
     * @param string|null $value
     * @param array $options
     * @return string
     */
    public function toCurrency($value = null, array $options = array())
    {
        return $this->sPriceReplacementMessage;
    }
}