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
 * Class MonitorOrder.
 *
 * @package SQLi\Twint\Model
 */
class MonitorOrder
{
    /**
     * Success code when the payment is succesful.
     */
    const SUCCESS_CODE = 'SUCCESS';

    /**
     * Error code when the payment is aborted.
     */
    const ABORT_CODE = 'FAILURE';

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
     * @var SendEmail
     */
    private $sendEmail;

    /**
     * MonitorOrder constructor.
     *
     * @param Config $config
     * @param TwintLogger $logger
     * @param TwintSoapClient $soapClient
     * @param DBSessionManagement $dbSessionManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param SendEmail $sendEmail
     */
    public function __construct(
        TwintConfig $config,
        TwintLogger $logger,
        TwintSoapClient $soapClient,
        DBSessionManagement $dbSessionManagement,
        OrderRepositoryInterface $orderRepository,
        SendEmail $sendEmail
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->soapClient = $soapClient;
        $this->dbSessionManagement = $dbSessionManagement;
        $this->orderRepository = $orderRepository;
        $this->sendEmail = $sendEmail;
    }

    /**
     * @param string $orderUuid
     *
     * @return array
     */
    public function buildRequest($orderUuid)
    {
        $structure = [
            'MerchantInformation' => [
                'MerchantUuid' => $this->config->getMerchantId(),
                'CashRegisterId' => $this->config->getCashRegisterId()
            ],
            'OrderUuid' => $orderUuid,
            'WaitForResponse' => 1,
        ];

        return $structure;
    }

    /**
     * Send Request to the Twint API.
     *
     * @param string $orderUuid
     *
     * @return array|bool|string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendRequest($orderUuid)
    {
        $client = $this->soapClient->getSoapClient();

        $request = $this->buildRequest($orderUuid);

        $this->logger->debug(
            '[TwintMonitorOrder] Send Request with twint order id '
            . $orderUuid
        );

        $client->addSoapInputHeader($this->soapClient->createSoapHeader());
        $client->monitorOrder($request);

        $response = $client->getLastResponse();

        if ($response) {
            $response = $this->soapClient->parseResponse($response);
            $this->logger->debug('[TwintMonitorOrder] Response : ');
            $this->logger->debug(json_encode($response));
            $returnValue = $this->paymentStatusFromResponse($response);
            $this->logger->debug('[TwintMonitorOrder] Response return value : ' . $returnValue);
            return $returnValue;
        }
        return false;
    }

    /**
     * Returns 1 if the payment is OK for Twint , -1 if Failed and 0 if pending.
     *
     * @param array $response
     *
     * @return int
     */
    public function paymentStatusFromResponse($response)
    {
        if (!isset($response['soap:Envelope']['soap:Body'])) {
            return 0;
        }

        $response = $response['soap:Envelope']['soap:Body'];
        if (!isset($response['ns2:MonitorOrderResponseElement'])) {
            return 0;
        }

        $response = $response['ns2:MonitorOrderResponseElement'];

        if (!isset($response['ns2:Order']) && !isset($response['ns2:Order']['_value'])) {
            return 0;
        }

        $response = $response['ns2:Order']['_value'];

        if (!isset($response['ns2:Status']) && !isset($response['ns2:Status']['ns2:Status'])) {
            return 0;
        }

        $response = $response['ns2:Status']['ns2:Status'];

        switch ($response['_value']) {
            case self::SUCCESS_CODE:
                $this->logger->debug("[TwintMonitorOrder] Recover response : " . self::SUCCESS_CODE);
                return 1;
            case self::ABORT_CODE:
                $this->logger->debug("[TwintMonitorOrder] Recover response : " . self::ABORT_CODE);
                return -1;
            default:
                return 0;
        }
    }

    /**
     * Update status vendor/magento/module-sales/Model/Order/Email/Sender/OrderSender.phpof the Magento order according to the Twint response.
     *
     * @param bool $status
     * @param int $orderId
     *
     * @return bool
     */
    public function updateOrderStatus($status, $orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            return false;
        }

        if ($status) {
            //Payment successful
            $this->logger->debug('[TwintMonitorOrder] Response Status : SUCCESS');
            $order->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE)
                ->setState(\Magento\Sales\Model\Order::STATE_COMPLETE);
            $this->sendEmail->sendEmail($order);
        } else {
            //Payment failed
            $this->logger->debug('[TwintMonitorOrder] Response Status : FAILED');
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED)
                ->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        }

        $this->orderRepository->save($order);
    }
}
