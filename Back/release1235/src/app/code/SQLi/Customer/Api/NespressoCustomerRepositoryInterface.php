<?php
/**
 * SQLi_Customer extension.
 *
 * @category   SQLi
 * @package    SQLi_Customer
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Customer\Api;

/**
 * Nespresso Customer CRUD interface.
 * @api
 * @since 100.0.2
 */
interface NespressoCustomerRepositoryInterface
{
    /**
     * Retrieve customer by nespresso club member id.
     *
     * @param int $nesMemberId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerByNesMemberId($nesMemberId);

    /**
     * Authorize and retrieve customer by nespresso club member id and matching postalCode.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorizeCustomer();

    /**
     * Auto Authorize customer by session id and matching customer relation id.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function autoAuthorizeCustomer();

    /**
     * Create new customers.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer();
}
