<?php
/**
 * SQLi_GoogleTagManager extension.
 *
 * @category   SQLi
 * @package    SQLi_GoogleTagManager
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\GoogleTagManager\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GoogleTagManager\Helper\Data as GtmHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config.
 *
 * @package SQLi\GoogleTagManager\Model
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
      ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get container identifier by website id.
     *
     * @param $websiteId
     *
     * @return mixed
     */
    public function getContainerIdByWebsite($websiteId)
    {
        return $this->scopeConfig->getValue(
            GtmHelper::XML_PATH_CONTAINER_ID,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }
}

