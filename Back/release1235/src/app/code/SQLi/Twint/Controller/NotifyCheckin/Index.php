<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Twint\Controller\NotifyCheckin;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Authentication;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use SQLi\Twint\Logger\TwintLogger;
use SQLi\Twint\Model\Config as TwintConfig;
use SQLi\Twint\Model\DBSessionManagement;
use SQLi\Twint\Model\QRCode;

/**
 * Class Index.
 *
 * @package SQLi\Twint\Controller\NotifyCheckin
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Authentication
     */
    private $httpAuthentication;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var TwintLogger
     */
    private $twintLogger;

    /**
     * @var TwintConfig
     */
    private $twintConfig;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var QRCode
     */
    private $qrCode;

    /**
     * @var DBSessionManagement
     */
    private $dbSessionManagement;

    /**
     * @var mixed
     */
    private $utmParams;


    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Authentication $httpAuthentication
     * @param RemoteAddress $remoteAddress
     * @param TwintLogger $twintLogger
     * @param TwintConfig $twintConfig
     * @param Json $jsonSerializer
     * @param QRCode $qrCode
     * @param DBSessionManagement $dbSessionManagement
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Authentication $httpAuthentication,
        RemoteAddress $remoteAddress,
        TwintLogger $twintLogger,
        TwintConfig $twintConfig,
        Json $jsonSerializer,
        QRCode $qrCode,
        DBSessionManagement $dbSessionManagement
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->httpAuthentication = $httpAuthentication;
        $this->remoteAddress = $remoteAddress;
        $this->twintLogger = $twintLogger;
        $this->twintConfig = $twintConfig;
        $this->jsonSerializer = $jsonSerializer;
        $this->qrCode = $qrCode;
        $this->dbSessionManagement = $dbSessionManagement;
    }

    /**
     * Twint notify checkin, return callback URL of the front.
     *
     * @return array|bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $this->logInformation('New checkin attempt made by IP ' . $this->remoteAddress->getRemoteAddress());
        $result = $this->checkAuthentication();
        if ($result !== true) {
            return $result;
        }
        $result = $this->checkTerminalId($this->_request->getPathInfo());
        if ($result !== true) {
            return $result;
        }
        $content = $this->_request->getContent();

        if (!$content) {
            $this->logInformation('The request has no content.');
            return false;
        }
        $sessionId = $this->parseAndStoreRequestBody($content);
        if (!$sessionId) {
            return false;
        }

        $result = [];
        $result['checkInUrl'] = $this->twintConfig->getCallbackUrl() . '?sessionId=' . $sessionId . $this->utmParams;
        $this->logInformation('The login url is: ' . $result['checkInUrl'] . PHP_EOL);
        $jsonResult = $this->jsonFactory->create();
        $jsonResult->setData($result);
        $jsonResult->setHttpResponseCode(200);
        return $jsonResult;
    }

    /**
     * Check HTTP authentication.
     *
     * @return bool|\Magento\Framework\Controller\Result\Json
     */
    private function checkAuthentication()
    {
        $user = $this->twintConfig->getUsername();
        $pw = $this->twintConfig->getPassword();

        $credentials = $this->httpAuthentication->getCredentials();

        if (isset($credentials[0]) && isset($credentials[1])) {
            if ($credentials[0] == $user && $credentials[1] == $pw) {
                return true;
            }
        }

        $this->logInformation('Wrong credentials given');

        $jsonResult = $this->jsonFactory->create();
        $jsonResult->setHttpResponseCode(401);

        return $jsonResult;
    }

    /**
     * Check terminal identifier on the request path.
     *
     * @param string $pathInfo
     *
     * @return bool
     */
    private function checkTerminalId($pathInfo)
    {
        $terminalId = $this->twintConfig->getTerminalId();
        $pathExploded = explode('/', $pathInfo);

        if (isset($pathExploded[4]) && $pathExploded[4] == $terminalId) {
            return true;
        }

        $this->logInformation('Wrong terminal id given ' . $pathExploded[2]);

        return false;
    }

    /**
     * Useful function to log message.
     *
     * @param string|array $message
     */
    private function logInformation($message)
    {
        if (!is_string($message)) {
            $this->twintLogger->debug('[TwintNotifyCheckin] ' . implode('<=>', $message));
        } else {
            $this->twintLogger->debug('[TwintNotifyCheckin] ' . $message);
        }
    }

    /**
     * Parse request body and store useful informations for session.
     *
     * @param $content
     *
     * @return bool|int
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function parseAndStoreRequestBody($content)
    {
        $this->logInformation('Twint send: ' . json_encode($content));
        $decodedBody = $this->jsonSerializer->unserialize($content);

        //we dont have a qr code
        if (!isset($decodedBody['code'])) {
            return false;
        }

        $customParams = $this->qrCode->getCustomParameters($decodedBody['code']);

        // add nespressoId to the custom params
        $nespressoId = $this->getLoyaltyCardNespressoIdFromParsedBody($decodedBody);
        if ($nespressoId) {
            $this->logInformation('NespressoId found: ' . $nespressoId);
            $customParams['nespressoId'] = $nespressoId;
        }

        $sessionId = $this->qrCode->generateSessionId();

        $customerRelationId = null;
        if (isset($decodedBody['customerRelationUuid'])) {
            $customerRelationId = $decodedBody['customerRelationUuid'];
            $this->logInformation('The customerRelationId is:' . $customerRelationId);
        }

        $this->utmParams = '';
        if (isset($customParams['utm_source'])) {
            $this->utmParams = $this->utmParams . '&utm_source=' . $customParams['utm_source'];
        }

        if (isset($customParams['utm_medium'])) {
            $this->utmParams = $this->utmParams.  '&utm_medium=' . $customParams['utm_medium'];
        }

        if (isset($customParams['utm_campaign'])) {
            $this->utmParams = $this->utmParams . '&utm_campaign=' . $customParams['utm_campaign'];
        }

        $this->dbSessionManagement->saveSessionRow($sessionId, $customParams, $customerRelationId);

        return $sessionId;
    }

    /**
     * Get nespressoId from the loyalty cards object.
     *
     * @param array $decodedBody
     *
     * @return string|bool
     */
    private function getLoyaltyCardNespressoIdFromParsedBody($decodedBody)
    {
        if (isset($decodedBody) && !empty($decodedBody['loyalty'])) {
            $loyaltyCards = $decodedBody['loyalty'];

            foreach ($loyaltyCards as $loyaltyCard) {
                if (!empty($loyaltyCard['program']) && $loyaltyCard['program'] == $this->twintConfig->getProgramName()) {
                    return !empty($loyaltyCard['reference']) ? $loyaltyCard['reference'] : false;
                }
            }
        }
        $this->logInformation('No loyalty param found.');
        return false;
    }
}
