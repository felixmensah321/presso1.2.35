<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Api;

/**
 * Check and Invalidate CRUD interface.
 * @api
 * @since 100.0.2
 */
interface CheckAndInvalidateInterface
{
    /**
     * Check and invalidate the session, return the webshop URL or the error page.
     *
     * @param string $sessionId
     *
     * @return mixed
     */
    public function checkAndInvalidate($sessionId);
}
