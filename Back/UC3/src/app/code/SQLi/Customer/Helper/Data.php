<?php
/**
 * SQLi_Customer extension.
 *
 * @category   SQLi
 * @package    SQLi_Customer
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Customer\Helper;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package SQLi\Customer\Helper
 */
class Data extends AbstractHelper
{

    /**
     * Nespresso Club Member ID attribute code
     */
    const ATTRIBUTE_NESPRESSO_MEMBER_ID = "nespresso_club_member_id";
    /**
     * Nespresso Customer Relation ID attribute code
     */
    const ATTRIBUTE_NESPRESSO_CUSTOMER_RELATION_ID = "customer_relation_id";
    /**
     * Optin Newsletter attribute code
     */
    const ATTRIBUTE_NEWSLETTER_OPTIN = "newsletter_optin";

    /**
     * Returns if the customer is a Nespresso Customer
     * @return bool
     */
    public function isNespressoCustomer(Customer $customer)
    {
        $customerNespressoMemberId = $customer->getData(self::ATTRIBUTE_NESPRESSO_MEMBER_ID);
        if ($customerNespressoMemberId) {
            return true;
        }
        return false;
    }

}
