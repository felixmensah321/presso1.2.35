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

use Magento\Sales\Api\OrderRepositoryInterface;
use SQLi\Twint\Logger\TwintLogger;
use SQLi\Twint\Model\Config as TwintConfig;
use SQLi\Twint\Model\Soap\Client as TwintSoapClient;

/**
 * Class StartOrder.
 * @package SQLi\Twint\Model
 */
class StartOrder
{

    /**
     * Payment type.
     */
    const PAYMENT_TYPE = 'PAYMENT_IMMEDIATE';

    /**
     * Posting type.
     */
    const POSTING_TYPE = 'GOODS';

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

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * StartOrder constructor.
     *
     * @param Config $config
     * @param TwintLogger $logger
     * @param TwintSoapClient $soapClient
     * @param DBSessionManagement $dbSessionManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        TwintConfig $config,
        TwintLogger $logger,
        TwintSoapClient $soapClient,
        DBSessionManagement $dbSessionManagement,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->soapClient = $soapClient;
        $this->dbSessionManagement = $dbSessionManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $pairingUuid
     * @param $orderId
     *
     * @return array|bool
     */
    public function buildRequest($pairingUuid, $orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            return false;
        }

        $structure = [
            'MerchantInformation' => [
                'MerchantUuid' => $this->config->getMerchantId(),
                'CashRegisterId' => $this->config->getCashRegisterId()
            ],
            'Order' => [
                'type' => self::PAYMENT_TYPE,
                'PostingType' => self::POSTING_TYPE,
                'RequestedAmount' => [
                    'Amount' => number_format($order->getBaseGrandTotal(), 2),
                    'Currency' => $order->getBaseCurrencyCode()
                ],
                'MerchantTransactionReference' => '',
                'Link' => [
                    'MerchantTransactionReference' => '',
                ]
            ],
            'PairingUuid' => $pairingUuid,
        ];

        return $structure;
    }

    /**
     * Send Request to the Twint API.
     *
     * @param string $pairingUuid
     * @param string $orderId
     *
     * @return array|string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendRequest($pairingUuid, $orderId)
    {
        $client = $this->soapClient->getSoapClient();

        $request = $this->buildRequest($pairingUuid, $orderId);

        $this->logger->debug(
            '[TwintStartOrder] Send Request with pairing uuid '
            . $pairingUuid
        );

        $client->addSoapInputHeader($this->soapClient->createSoapHeader());
        $client->startOrder($request);

        $response = $client->getLastResponse();

        if ($response) {
            $response = $this->soapClient->parseResponse($response);
            $this->logger->debug('[TwintStartOrder] Response : ');
            $this->logger->debug(json_encode($response));
            return $this->extractTwintOrderIdFromResponse($response);
        }

        return null;
    }

    /**
     * Extract twint order id from order response.
     *
     * @param array $response
     *
     * @return string
     */
    public function extractTwintOrderIdFromResponse($response)
    {
        if (!isset($response['soap:Envelope']['soap:Body'])) {
            return false;
        }

        $response = $response['soap:Envelope']['soap:Body'];
        if (!isset($response['ns2:StartOrderResponseElement'])) {
            return false;
        }

        $response = $response['ns2:StartOrderResponseElement'];

        if (!isset($response['ns2:OrderUuid'])) {
            return false;
        }

        return $response['ns2:OrderUuid'];
    }
}

