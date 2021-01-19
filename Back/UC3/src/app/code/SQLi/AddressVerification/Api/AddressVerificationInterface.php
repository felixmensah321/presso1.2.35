<?php
/**
 * SQLi_AddressVerification.
 *
 * @category   SQLi
 * @package    SQLi_AddressVerification
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\AddressVerification\Api;

interface AddressVerificationInterface
{
    /**
     * Retrieve access token and return to frontend.
     **
     * @return string
     *
     */
    public function getAccessToken();
}
