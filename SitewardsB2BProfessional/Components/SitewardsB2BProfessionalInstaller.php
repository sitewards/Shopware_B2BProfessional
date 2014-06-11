<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalInstaller
 * Basic helper functionality for installation procedures
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SitewardsB2BProfessionalInstaller
{

    const S_ATTRIBUTE_PREFIX = 'b2bprofessional';

    /**
     * @param string $sModelName
     * @param string $sAttributeName
     * @param string $sAttributeType
     */
    public function addAttribute($sModelName, $sAttributeName, $sAttributeType)
    {
        Shopware()->Models()->addAttribute(
            $sModelName,
            self::S_ATTRIBUTE_PREFIX,
            $sAttributeName,
            $sAttributeType
        );
        $oMetadataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $oMetadataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels(
            array(
                $sModelName
            )
        );
    }
}