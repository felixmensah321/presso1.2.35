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
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AuthorizationException;
use SQLi\AddressVerification\Helper\Oauth\ActivatorFactory;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Class TokenChecker
 * @package SQLi\AddressVerification\Helper
 */
class TokenChecker extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ActivatorFactory
     */
    protected $activatorFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * TokenChecker constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param ActivatorFactory $activatorFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Data $helperData,
        ActivatorFactory $activatorFactory
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->activatorFactory = $activatorFactory;
    }

    public function getConfig()
    {
        return $this->helperData->getAllConfig();
    }

    public function getActivatorObj($configData = null)
    {
        if($configData == null) {
            $configData = $this->getConfig();
        }
       return $this->activatorFactory->create(['params' => $configData]);
    }

    /**
     * @return bool|mixed
     * @throws GuzzleException
     */
    public function getToken()
    {
        $configData = $this->getConfig();
        if(!empty($configData['accessToken']) && !empty($configData['tokenExpiryTime'])) {
            $isExpired = $this->hasTokenExpired($configData['tokenExpiryTime']);
            if($isExpired) {
                return $this->getNewToken();
            } else {
                return $configData;
            }
        }
        return $this->getNewToken();
    }

    /**
     * @param $tokenExpiryTime
     * @return bool
     */
    public function hasTokenExpired($tokenExpiryTime)
    {
        if($tokenExpiryTime) {
            if (time() >= $tokenExpiryTime) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $refreshToken
     * @return bool | array
     * @throws GuzzleException
     */
    public function getNewToken()
    {
        try{
            $activatorObj = $this->getActivatorObj();
            $response = $activatorObj->fetchAccessTokens();
            if(isset($response['status'])) {
                if($response['status'] == 'unsuccessful') {
                    return $response['code'];
                }
            }
            if(isset($response['body'])) {
                $tokenArray = json_decode(json_encode($response['body']),true);
                $accessTokenExpiryTime = time() + $tokenArray['expires_in'];
                $this->helperData->setData(Data::ACCESS_TOKEN_FIELD_PATH, $tokenArray['access_token']);
                $this->helperData->setData(Data::TOKEN_EXPIRY_FIELD_PATH, $accessTokenExpiryTime);
                $tokenArray['accessToken'] = $tokenArray['access_token'];
                return $tokenArray;
            }
        } catch(\Exception $e) {
            $this->logger->error('Error message', ['exception' => $e->getMessage()]);
        }

        return false;
    }
}
