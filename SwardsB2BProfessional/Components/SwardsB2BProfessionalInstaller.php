<?php

/**
 * Class Shopware_Components_SwardsB2BProfessionalInstaller
 * Basic helper functionality for installation procedures
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SwardsB2BProfessionalInstaller
{

    const S_ATTRIBUTE_PREFIX = 'b2bprofessional';

    /** @var \Shopware\Components\Model\ModelManager */
    private $oModelManager;

    /**
     * constructor
     *
     * @param \Shopware\Components\Model\ModelManager $oModelManager
     */
    public function __construct(\Shopware\Components\Model\ModelManager $oModelManager)
    {
        $this->oModelManager = $oModelManager;
    }

    /**
     * returns the model manager
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getModelManager()
    {
        return $this->oModelManager;
    }

    /**
     * returns the metadata cache
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function getMetadataCache()
    {
        return $this->getModelManager()->getConfiguration()->getMetadataCacheImpl();
    }

    /**
     * deletes the metadata cache
     */
    protected function deleteMetadataCache()
    {
        /** @var Doctrine\Common\Cache\Cache $oMetadataCache */
        $oMetadataCache = $this->getMetadataCache();
        $oMetadataCache->deleteAll();
    }

    /**
     * @param string $sModelName
     * @param string $sAttributeName
     * @param string $sAttributeType
     */
    public function addAttribute($sModelName, $sAttributeName, $sAttributeType)
    {
        $oModelManager = $this->getModelManager();

        $oModelManager->addAttribute(
            $sModelName,
            self::S_ATTRIBUTE_PREFIX,
            $sAttributeName,
            $sAttributeType
        );

        $this->deleteMetadataCache();

        $oModelManager->generateAttributeModels(
            array(
                $sModelName
            )
        );
    }
}