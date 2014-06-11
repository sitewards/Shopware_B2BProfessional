<?php

/**
 * Class Shopware_Components_SitewardsB2BProfessionalFactory
 * Factory for B2BProfessional components
 */
class Shopware_Components_SitewardsB2BProfessionalFactory
{

    /** @var Object[] */
    private $aComponents = array();

    /**
     * creates a new component and initializes it or just returns an existing one
     *
     * @param string $sComponentName
     * @param array $aInitMethods
     * @return Object
     */
    public function getComponent($sComponentName, $aInitMethods = array())
    {
        if (!isset($this->aComponents[$sComponentName])) {
            $sClassName = 'Shopware_Components_SitewardsB2BProfessional' . $sComponentName;
            $this->aComponents[$sComponentName] = new $sClassName;
            if (is_array($aInitMethods) && !empty($aInitMethods)) {
                foreach ($aInitMethods as $sMethodName => $aMethodParams) {
                    call_user_func_array(
                        array($this->aComponents[$sComponentName], $sMethodName),
                        $aMethodParams
                    );
                }
            }
        }
        return $this->aComponents[$sComponentName];
    }

}