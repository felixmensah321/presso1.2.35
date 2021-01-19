<?php
/**
 * SQLi_GoogleTagManager extension.
 *
 * @category   SQLi
 * @package    SQLi_GoogleTagManager
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\GoogleTagManager\Api;

/**
 * GoogleTagManagement CRUD interface.
 * @api
 * @since 100.0.2
 */
interface GoogleTagManagementInterface
{
    /**
     * Get container identifier by website id.
     *
     * @param integer $websiteId
     *
     * @return mixed
     */
    public function getContainerId($websiteId);
}
