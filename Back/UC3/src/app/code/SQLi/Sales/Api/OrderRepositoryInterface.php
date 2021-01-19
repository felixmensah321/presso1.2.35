<?php
/**
 * SQLi_Sales extension.
 *
 * @category   SQLi
 * @package    SQLi_Sales
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Sales\Api;

use SQLi\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderRepositoryInterface.
 * @api
 * @since 100.0.2
 */
interface OrderRepositoryInterface
{
    /**
     * Get info about order by id
     *
     * @param int $orderId
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId);
}
