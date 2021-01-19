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

use SQLi\Twint\Logger\TwintLogger;
use SQLi\Twint\Model\Config as TwintConfig;
use SQLi\Twint\Model\Soap\Client as TwintSoapClient;

/**
 * Class RequestCheckin.
 *
 * @package SQLi\Twint\Model
 */
class RequestCheckin
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TwintLogger
     */
    protected $logger;

    /**
     * @var TwintSoapClient
     */
    protected $soapClient;

    /**
     * @var \SQLi\Twint\Model\DBSessionManagement
     */
    protected $dbSessionManagement;

    public function __construct(
      TwintConfig $config,
      TwintLogger $logger,
      TwintSoapClient $soapClient,
      DBSessionManagement $dbSessionManagement
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->soapClient = $soapClient;
        $this->dbSessionManagement = $dbSessionManagement;
    }

    /**
     * Build the request structure.
     *
     * @param string $sessionId
     *
     * @return array
     */
    public function buildRequest($sessionId)
    {
        $structure = [
            'MerchantInformation' => [
                'MerchantUuid' => $this->config->getMerchantId(),
                'CashRegisterId' => $this->config->getCashRegisterId()
            ],
            'QRCodeRendering' => 1,
            'CustomerRelationUuid' => $this->dbSessionManagement->getCustomerRelationIdBySessionId($sessionId),
        ];

        return $structure;
    }

    /**
     * Send the request to the Twint Api and returns PairingUuid and token.
     *
     * @param $sessionId
     *
     * @return array|bool
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendRequest($sessionId)
    {
        $client = $this->soapClient->getSoapClient();

        $request = $this->buildRequest($sessionId);

        $this->logger->debug('[TwintRequestCheckin] Send Request with session id ' . $sessionId);
        $this->logger->debug('[TwintRequestCheckin] And with request: ' . json_encode($request));
        $client->addSoapInputHeader($this->soapClient->createSoapHeader());
        $client->RequestCheckIn($request);
        $this->logger->debug('[TwintRequestCheckin] And with request: ' . json_encode($client->getLastRequest()));
        $response = $client->getLastResponse();

        $token = false;
        if ($response) {
            $response = $this->soapClient->parseResponse($response);
            $this->logger->debug('[TwintRequestCheckin] Response : ');
            $this->logger->debug(json_encode($response));
            if (!isset($response['soap:Envelope']['soap:Body'])) {
                return false;
            }

            $response = $response['soap:Envelope']['soap:Body'];
            if (!isset($response['ns2:RequestCheckInResponseElement'])) {
                return false;
            }

            $response = $response['ns2:RequestCheckInResponseElement'];

            if (isset($response['ns2:Token'])) {
                $token = $response['ns2:Token'];
            }

            if (isset($response['ns2:CheckInNotification'])) {
                $response = $response['ns2:CheckInNotification'];
                if (isset($response['ns2:PairingUuid']) && isset($response['ns2:PairingStatus'])) {
                    $this->dbSessionManagement->savePairingUidAndStatusBySessionId(
                        $sessionId,
                        $response['ns2:PairingUuid'],
                        $response['ns2:PairingStatus']
                    );
                } else {
                    return false;
                }
            }

            if ($token) {
                return [
                    'token' => $token,
                    'pairingUuid' => $response['ns2:PairingUuid'],
                ];
            }

            return false;
        }

        return false;
    }
}
