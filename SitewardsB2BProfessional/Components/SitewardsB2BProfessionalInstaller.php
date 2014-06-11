<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalInstaller
 * Basic helper functionality for installation procedures
 */
class Shopware_Components_SitewardsB2BProfessionalInstaller
    implements Shopware_Components_SitewardsB2BProfessionalInterface
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