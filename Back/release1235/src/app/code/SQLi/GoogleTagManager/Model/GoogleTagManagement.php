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

use SQLi\GoogleTagManager\Api\GoogleTagManagementInterface;

/**
 * Class GoogleTagManagement.
 *
 * @package SQLi\GoogleTagManager\Model
 */
class GoogleTagManagement implements GoogleTagManagementInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * GoogleTagManagement constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getContainerId($websiteId)
    {
        $containerId = $this->config->getContainerIdByWebsite($websiteId);

        if ($containerId) {
            return $containerId;
        }

        return [];
    }
}
