<?php
/**
 * SQLi_Sales extension.
 *
 * @category   SQLi
 * @package    SQLi_Sales
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Sales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order;
use SQLi\Export\Helper\ConstRegistry;

/**
 * Class Data
 * @package SQLi\Customer\Helper
 */
class Data extends AbstractHelper
{

    /**
     * Purchase point Mode : UC1 or UC3
     */
    const MODE_PURCHASE_POINT = 'purchase_point';
    /**
     * Online Mode : UC2
     */
    const MODE_ONLINE = 'online';
    /**
     * List of values associated to the Delivery mode (UC2)
     */
    const DELIVERY_PURCHASE_POINT_ID = ['default / online', 'online', 'default'];
    /**
     * fake email address used for guest user
     */
    const ANONYMOUS_EMAIL = 'anonymous@nespresso.ch';

    /**
     * Returns if the provided nespresso purchase point is a physical point of sale
     *
     * @return bool
     */
    public function isNespressoPurchasePoint($nespressoPurchasePointId)
    {
        if (!empty($nespressoPurchasePointId) && !$this->isOnlineDelivery($nespressoPurchasePointId)) {
            return true;
        }
        return false;
    }

    /**
     * Returns if the provided nespresso purchase point is for online delivery (default mode)
     *
     * @return bool
     */
    public function isOnlineDelivery($nespressoPurchasePointId)
    {
        return in_array($nespressoPurchasePointId, self::DELIVERY_PURCHASE_POINT_ID);
    }

    /**
     * Return order mode from order data
     * @param Order $order
     */
    public function getOrderMode($order)
    {
        $nespressoPurchasePointId = $order->getNespressoPurchasePointId();
        if (empty($nespressoPurchasePointId)) {
            return false;
        }
        if ($this->isNespressoPurchasePointId($nespressoPurchasePointId)) {
            return self::MODE_PURCHASE_POINT;
        } else if ($this->isOnlineDelivery($nespressoPurchasePointId)) {
            return self::MODE_ONLINE;
        }
        return false;
    }

    /**
     * Return order customer identification mode
     * @param Order $order
     * @return string
     */
    public function getCustomerIdentificationMode($order)
    {
        $isAnonymous = $this->isAnonymousOrder($order);
        $nespressoClubMemberId = $order->getNespressoClubMemberId();
        if ($isAnonymous) {
            $identificationMode = ConstRegistry::IS_TRADE;
        } else if (!$isAnonymous && !empty($nespressoClubMemberId)) {
            $identificationMode = ConstRegistry::IS_REGISTERED;
        } else if (!$isAnonymous && empty($nespressoClubMemberId)) {
            $identificationMode = ConstRegistry::IS_NEW;
        } else {
            $identificationMode = ConstRegistry::IS_GUEST;
        }
        return $identificationMode;
    }

    /**
     * Return if order is anonymous
     * @param Order $order
     */
    public function isAnonymousOrder($order)
    {
        $orderCustomerEmail = trim($order->getCustomerEmail());
        if ($orderCustomerEmail == self::ANONYMOUS_EMAIL) {
            return true;
        }
        return false;
    }

}
