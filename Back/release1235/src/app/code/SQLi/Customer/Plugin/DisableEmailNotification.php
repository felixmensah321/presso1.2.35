<?php
/**
 * SQLi_Customer extension.
 *
 * @author SQLI Dev Team
 * @copyright Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Customer\Plugin;

use Magento\Customer\Model\EmailNotification;

/**
 * Class DisableCompanyCreationEmail
 */
class DisableEmailNotification
{
    /**
     * disable notification email for new accounts
     *
     * @param EmailNotification $subject
     * @param $customer
     * @param $redirectUrl
     * @return EmailNotification
     */
    public function aroundNewAccount(EmailNotification $subject, $customer, $redirectUrl)
    {
        return $subject;
    }
}
