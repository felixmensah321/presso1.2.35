<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config.
 *
 * @package SQLi\Twint\Model
 */
class Config
{
    /**
     * Mock Mode config for Twint API
     */
    const XML_PATH_MOCK_MODE = 'twint/mock_mode/mock_mode';
    /**
     *  Twint API Username
     */
    const XML_PATH_USERNAME = 'twint/notify_checkin/username';
    /**
     * Twint API Password
     */
    const XML_PATH_PASSWORD = 'twint/notify_checkin/password';
    /**
     * Twint API Terminal ID
     */
    const XML_PATH_TERMINAL_ID = 'twint/notify_checkin/terminal_id';
    /**
     * Twint API callback
     */
    const XML_PATH_CALLBACK_URL = 'twint/notify_checkin/callback_url';
    /**
     *  Twint API Beacon Init
     */
    const XML_PATH_BEACON_INIT_STRING = 'twint/notify_checkin/beacon_string';
    /**
     * Twint SOAP WSDL
     */
    const XML_PATH_SOAP_WSDL = 'twint/soap/wsdl';
    /**
     * Twint SOAP Fallback flag
     */
    const XML_PATH_SOAP_WSDL_USES_FALLBACK = 'twint/soap/wsdl_uses_fallback';
    /**
     * Twint SOAP Https Certificate
     */
    const XML_PATH_SOAP_CERTIFICATE = 'twint/soap/https_certificate';
    /**
     * Twint SOAP Certificate Passcode
     */
    const XML_PATH_SOAP_PASSPHRASE = 'twint/soap/https_certificate_passphrase';
    /**
     * Twint SOAP Merchant ID
     */
    const XML_PATH_SOAP_MERCHANT_ID = 'twint/soap/merchant_id';
    /**
     * Twint SOAP Cash Register ID
     */
    const XML_PATH_SOAP_CASH_ID = 'twint/soap/cash_register_id';
    /**
     * Front Default Category ID
     */
    const XML_PATH_DEFAULT_CATEGORY_ID = 'front/default_params/category';
    /**
     * Nespresso loyalty card program name.
     */
    const XML_NESPRESSO_LOYALTY_CARD_PROGRAM_NAME = 'twint/notify_checkin/loyalty';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     */
    public function __construct(
      ScopeConfigInterface $scopeConfig,
      StoreManagerInterface $storeManager,
      Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * Get notify checkin endpoint.
     *
     * @return string
     */
    public function getNotifyCheckinEndpoint()
    {
        return '/terminals/' . $this->getTerminalId() . '/checkin';
    }

    /**
     * Retrieve Mock Mode
     *
     * @return mixed
     */
    public function getMockMode()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MOCK_MODE
        );
    }

    /**
     * Retrieve HTTP Auth username.
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USERNAME
        );
    }

    /**
     * Retrieve default category id.
     *
     * @return mixed
     */
    public function getDefaultCategory()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_CATEGORY_ID
        );
    }

    /**
     * Retrieve HTTP Auth password.
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PASSWORD
        );
    }

    /**
     * Retrieve HTTP Auth terminal id.
     *
     * @return mixed
     */
    public function getTerminalId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TERMINAL_ID
        );
    }

    /**
     * Retrieve Callback Url.
     *
     * @return mixed
     */
    public function getCallbackUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALLBACK_URL
        );
    }

    /**
     * Get WSDL Url.
     *
     * @return mixed
     */
    public function getWsdlUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOAP_WSDL
        );
    }

    /**
     * Get WSDL use local fallback.
     *
     * @return mixed
     */
    public function getWsdlUseFallBack()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOAP_WSDL_USES_FALLBACK
        );
    }

    /**
     * Get HTTPS certificate path.
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHttpsCertificateUrl()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $certificateName = $this->scopeConfig->getValue(
        self::XML_PATH_SOAP_CERTIFICATE
        );

        if ($certificateName) {
            return $mediaPath . 'certificate/' . $certificateName;
        }

        return '';
    }

    /**
     * Get the certificate passphrase.
     *
     * @return mixed
     */
    public function getHttpsCertificatePassphrase()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOAP_PASSPHRASE
        );
    }

    /**
     * Get the merchant identifier.
     *
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOAP_MERCHANT_ID
        );
    }

    /**
     * Get the cash register identifier.
     *
     * @return mixed
     */
    public function getCashRegisterId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOAP_CASH_ID
        );
    }

    /**
     * Get the beacon init string.
     *
     * @return mixed
     */
    public function getBeaconInitString()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BEACON_INIT_STRING
        );
    }

    /**
     * Retrieve loyalty program name.
     *
     * @return mixed
     */
    public function getProgramName()
    {
        return $this->scopeConfig->getValue(
            self::XML_NESPRESSO_LOYALTY_CARD_PROGRAM_NAME
        );
    }
}

