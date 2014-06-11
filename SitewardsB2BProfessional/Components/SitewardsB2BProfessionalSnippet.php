<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalCustomer
 * Basic helper functionality for customer handling
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     shopware@sitewards.com
 * @license     OSL-3.0
 */
class Shopware_Components_SitewardsB2BProfessionalSnippet
{

    const S_SNIPPET_TABLE_ALIAS = 'snippet';

    /**
     * returns the snippet repository
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getSnippetRepository()
    {
        return Shopware()->Models()
            ->getRepository('Shopware\\Models\\Snippet\\Snippet');
    }

    /**
     * returns the query builder for snippets
     *
     * @param string $sAlias
     * @return Shopware\Components\Model\QueryBuilder|\Doctrine\ORM\QueryBuilder
     */
    protected function getSnippetQueryBuilder($sAlias)
    {
        return $this->getSnippetRepository()->createQueryBuilder($sAlias);

    }

    /**
     * retrieves a snippet by namespace and localeId
     *
     * @param string $sNamespace
     * @param int $iLocaleId
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function getSnippetByNamespaceLocale($sNamespace, $iLocaleId)
    {
        /** @var \Shopware\Components\Model\ModelRepository $oSnippetRepository */
        $oSnippetRepository = $this->getSnippetRepository();

        /** @var Shopware\Models\Snippet\Snippet $oSnippet */
        $oSnippet = $oSnippetRepository->findOneBy(
            array(
                'namespace' => $sNamespace,
                'localeId'  => $iLocaleId
            )
        );

        return $oSnippet;
    }

    /**
     * saves a new snippet
     *
     * @param \Shopware\Models\Snippet\Snippet $oSnippet
     */
    protected function saveSnippet($oSnippet)
    {
        Shopware()->Models()->persist($oSnippet);
        Shopware()->Models()->flush();
    }

    /**
     * adds a new translation or updates an existing one
     * based on the namespace and localeId
     *
     * @param string $sNamespace
     * @param string $sName
     * @param int $iShopId
     * @param int $iLocaleId
     * @param string $sTranslation
     */
    public function addTranslation($sNamespace, $sName, $iShopId, $iLocaleId, $sTranslation)
    {
        /** @var \Shopware\Models\Snippet\Snippet $oSnippet */
        $oSnippet = $this->getSnippetByNamespaceLocale($sNamespace, $iLocaleId);

        /** @var Shopware\Components\Model\QueryBuilder $oQueryBuilder */
        $oQueryBuilder = $this->getSnippetQueryBuilder(static::S_SNIPPET_TABLE_ALIAS);

        if ($oSnippet) {
            $oQueryBuilder->update()
                ->set('snippet.value', '?1')
                ->where('snippet.id = ?2')
                ->setParameter(1, $sTranslation)
                ->setParameter(2, $oSnippet->getId())
                ->getQuery()
                ->execute();
        } else {
            $oSnippet = new \Shopware\Models\Snippet\Snippet();

            $oSnippet->setNamespace($sNamespace)
                ->setName($sName)
                ->setShopId($iShopId)
                ->setLocaleId($iLocaleId)
                ->setValue($sTranslation)
                ->setCreated('now')
                ->setUpdated('now');

            $this->saveSnippet($oSnippet);
        }
    }
}