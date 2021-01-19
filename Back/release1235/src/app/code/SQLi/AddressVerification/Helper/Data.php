<?php
/**
 * SQLi_AddressVerification extension.
 *
 * @category   SQLi
 * @package    SQLi_AddressVerification
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\AddressVerification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package SQLi\AddressVerification\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Address Verification Config path
     */
    const XML_PATH_AV = 'av/';

    /**
     * SwissPost Client Id
     */
    const CLIENT_ID_FIELD_PATH = 'av/general/client_id';

    /**
     * SwissPost Client Secret
     */
    const CLIENT_SECRET_FIELD_PATH = 'av/general/client_secret';

    /**
     * SwissPost Client Scope
     */
    const CLIENT_SCOPE_FIELD_PATH = 'av/general/client_scope';

    /**
     * SwissPost OpenID Oauth Server Endpoint
     */
    const OAUTH_SERVER_FIELD_PATH = 'av/general/oauth_server';

    /**
     * SwissPost API URI Endpoint
     */
    const API_URI_FIELD_PATH = 'av/general/api_uri';

    /**
     * Address Verification Redirect URI
     */
    const REDIRECT_URI_FIELD_PATH = 'av/general/redirect_uri';

    /**
     * Address Verification Authorization Code
     */
    const AUTHORIZATION_CODE_FIELD_PATH = 'av/general/authorization_code';

    /**
     * Address Verification Access Token
     */
    const ACCESS_TOKEN_FIELD_PATH = 'av/general/access_token';

    /**
     * Address Verification Token Expiry Time
     */
    const TOKEN_EXPIRY_FIELD_PATH = 'av/general/token_expiry_time';

    /**
     * Address Verification Request Token
     */
    const REFRESH_TOKEN_FIELD_PATH = 'av/general/refresh_token';

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param TypeListInterface $cacheTypeList
     * @param StoreManagerInterface $storeManager
     * @param Json $serialize
     */
    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        TypeListInterface $cacheTypeList,
        StoreManagerInterface $storeManager,
        Json $serialize
    ) {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->_storeManager  = $storeManager;
        $this->serialize = $serialize;
    }

    /**
     * Retrieve SwissPost Client ID.
     *
     * @param null $store
     * @return mixed
     */
    public function getClientId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CLIENT_ID_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost Client Secret
     *
     * @param null $store
     * @return mixed
     */
    public function getClientSecret($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CLIENT_SECRET_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost Client Scope
     *
     * @param null $store
     * @return mixed
     */
    public function getClientScope($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CLIENT_SCOPE_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost OpenID OAuth Endpoint
     *
     * @param null $store
     * @return mixed
     */
    public function getOauthServer($store = null)
    {
        return $this->scopeConfig->getValue(
            self::OAUTH_SERVER_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost API Uri
     *
     * @param null $store
     * @return mixed
     */
    public function getApiUri($store = null)
    {
        return $this->scopeConfig->getValue(
            self::API_URI_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost OpenID Authorization Code
     *
     * @param null $store
     * @return mixed
     */
    public function getAuthorizationCode($store = null)
    {
        return $this->scopeConfig->getValue(
            self::AUTHORIZATION_CODE_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve AddressVerification Redirect Uri
     *
     * @param null $store
     * @return mixed
     */
    public function getRedirectUri($store = null)
    {
        return $this->scopeConfig->getValue(
            self::REDIRECT_URI_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost Api Access Token
     *
     * @param null $store
     * @return mixed
     */
    public function getClientAccessToken($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ACCESS_TOKEN_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost Api Token Expiry Time
     *
     * @param null $store
     * @return mixed
     */
    public function getTokenExpiryTime($store = null)
    {
        return $this->scopeConfig->getValue(
            self::TOKEN_EXPIRY_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Retrieve SwissPost Api Refresher Token
     *
     * @param null $store
     * @return mixed
     */
    public function getClientRefreshToken($store = null)
    {
        return $this->scopeConfig->getValue(
            self::REFRESH_TOKEN_FIELD_PATH, ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllConfig()
    {
       return [
            'clientId' => trim($this->getClientId()),
            'clientSecret' => trim($this->getClientSecret()),
            'clientScope' => trim($this->getClientScope()),
            'oauthServer' => trim($this->getOauthServer()),
            'apiUri' => trim($this->getApiUri()),
            'authorizationCode' => trim($this->getAuthorizationCode()),
            'redirectUri' => trim($this->getRedirectUri()),
            'accessToken' => trim($this->getClientAccessToken()),
            'tokenExpiryTime' => trim($this->getTokenExpiryTime()),
            'refreshToken' => trim($this->getClientRefreshToken())
        ];
    }

    /**
     * @param $path
     * @param $value
     */
    public function setData($path, $value)
    {
        $this->configWriter->save($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    }

}
