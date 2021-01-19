<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Import\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package SQLi\Import\Helper
 */
class Data extends AbstractHelper
{

    /**
     * Autogenerated placeholder
     */
    const AUTOGENERATED = 'autogenerated';
    /**
     * XML Path of Anonymous Enable
     */
    const XML_PATH_IMPORT_CUSTOMER_ANONYMOUS = 'import/customer/anonymous';
    /**
     * XML Path of Anonymous Characters Max Length for First Name and Last Name
     */
    const XML_PATH_IMPORT_CUSTOMER_ANONYMOUS_MAX_LENGTH = 'import/customer/anonymous_max_length';

    /**
     * Anonymous constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Return if anonymous mode is enabled
     * @param null $storeId
     * @return int
     */
    public function isAnonymous($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IMPORT_CUSTOMER_ANONYMOUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return max characters length to keep for anonymous mode
     * @param null $storeId
     * @return int
     */
    public function getAnonymousCharMaxLength($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IMPORT_CUSTOMER_ANONYMOUS_MAX_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Suffix an email with autogenerated domain
     * @param $key
     * @return string
     */
    public function getAutogeneratedEmail($key)
    {
        $email = sprintf("%s@%s%s", $key, self::AUTOGENERATED, '.com');
        return $email;
    }

    /**
     * Anonymousing a string
     * @param $string
     * @return string
     */
    public function anonymise($string, $storeId = null)
    {
        return substr($string, 0, $this->getAnonymousCharMaxLength($storeId));
    }

}
