<?php
/**
 * SQLi_Import extension.
 *
 * @category SQLi
 * @author SQLI Dev Team
 * @copyright Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Import\Model\Plugins;


class DisableWelcomeEmail
{
    /**
     * disable notification email for new accounts
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
