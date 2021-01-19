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
 * Class MonitorCheckIn.
 *
 * @package SQLi\Twint\Model
 */
class MonitorCheckIn
{
    /**
     * Pairing active status.
     */
    const PAIRING_ACTIVE = 'PAIRING_ACTIVE';

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
     * MonitorCheckIn constructor.
     *
     * @param Config $config
     * @param TwintLogger $logger
     * @param TwintSoapClient $soapClient
     */
    public function __construct(
        TwintConfig $config,
        TwintLogger $logger,
        TwintSoapClient $soapClient
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->soapClient = $soapClient;
    }

    /**
     * Build the request structure.
     *
     * @param string $pairingUuid
     *
     * @return array
     */
    public function buildRequest($pairingUuid)
    {
        $structure = [
            'MerchantInformation' => [
                'MerchantUuid' => $this->config->getMerchantId(),
                'CashRegisterId' => $this->config->getCashRegisterId()
            ],
            'PairingUuid' => $pairingUuid,
        ];

        return $structure;
    }

    /**
     * Send the request to the Twint API.
     *
     * @param string $sessionId
     * @param string $pairingUuid
     *
     * @return array|bool|string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendRequest($sessionId, $pairingUuid)
    {
        $client = $this->soapClient->getSoapClient();

        $request = $this->buildRequest($pairingUuid);

        $this->logger->debug(
            '[TwintMonitorCheckin] Send Request with session id '
            . $sessionId . ' and pairingUuid ' . $pairingUuid
        );

        $client->addSoapInputHeader($this->soapClient->createSoapHeader());
        $client->monitorCheckin($request);

        $response = $client->getLastResponse();

        if ($response) {
            $response = $this->soapClient->parseResponse($response);
            $this->logger->debug('[TwintMonitorCheckin] Response : ');
            $this->logger->debug(json_encode($response));
            return $response;
        }

        return false;
    }

    /**
     * Extract Pairing Status from the response.
     *
     * @param array $response
     *
     * @return bool|string
     */
    public function getIsPairedFromResponse($response)
    {
        if (!isset($response['soap:Envelope']['soap:Body'])) {
            return false;
        }

        $response = $response['soap:Envelope']['soap:Body'];
        if (!isset($response['ns2:MonitorCheckInResponseElement'])) {
            return false;
        }

        $response = $response['ns2:MonitorCheckInResponseElement'];
        if (isset($response['ns2:CheckInNotification'])) {
            $response = $response['ns2:CheckInNotification'];
            if (isset($response['ns2:PairingUuid']) && isset($response['ns2:PairingStatus'])) {
                if ($response['ns2:PairingStatus'] == self::PAIRING_ACTIVE) {
                    $this->logger->debug(
                        '[TwintMonitorCheckin] Successfully paired with PairingUuid '
                        . $response['ns2:PairingUuid']
                    );
                    return true;
                }
            } else {
                return false;
            }
        }
    }
}
