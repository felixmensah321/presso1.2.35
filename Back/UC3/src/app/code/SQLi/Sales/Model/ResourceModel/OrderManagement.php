<?php
/**
 * SQLi_Sales extension.
 *
 * @category   SQLi
 * @package    SQLi_Sales
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Sales\Model\ResourceModel;

use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;
use SQLi\Sales\Api\OrderManagementInterface;
use SQLi\Sales\Model\Quote;
use SQLi\Sales\Model\Order;

/**
 * Class OrderManagement.
 *
 * @package SQLi\Sales\Model\ResourceModel
 */
class OrderManagement implements OrderManagementInterface
{
    /**
     * Mandatory params in order to be able to create order.
     *
     * @var array
     */
    protected $mandatoryParams = ['customer_id', 'store_id', 'products', 'address', 'session_id', 'purchase_point_id'];

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $twintLogger;

    /**
     * OrderManagement constructor.
     *
     * @param Quote $quote
     * @param Request $request
     * @param Order $order
     * @param LoggerInterface $logger
     */
    public function __construct(
        Quote $quote,
        Request $request,
        Order $order,
        LoggerInterface $logger
    ) {
        $this->quote = $quote;
        $this->request = $request;
        $this->order = $order;
        $this->logger = $logger;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/twint.log');
        $this->twintLogger = new \Zend\Log\Logger();
        $this->twintLogger->addWriter($writer);
    }

    /**
     * Create order.
     *
     * @return array|int|mixed|null
     */
    public function createOrder()
    {
        $token = null;
        $params = $this->validParams();

        if (!$params) {
            return null;
        }

        try {
            $this->myLogger("[OrderManagment] Create Quote");
                $quote = $this->quote->createQuote(
                    $params['customer_id'],
                    $params['products'],
                    $params['address'],
                    $params['store_id'],
                    $params['purchase_point_id']
                );
            $quoteId = $quote->getId();
            $this->myLogger("[OrderManagment] QuoteId: " . print_r($quote->getLastName(),1));
            $this->myLogger("[OrderManagment] QuoteId: " . $quoteId);
            $this->myLogger("[OrderManagment] Create an order from quote");
            $orderInfos = $this->order->createOrderFromQuoteId($quoteId, $params['session_id']);
            if ($orderInfos) {
                return $orderInfos;
            }
        } catch (\Exception $e) {
            $this->myLogger("[OrderManagment] An exception occurs: " . $e->getMessage());
            $this->myLogger("[OrderManagment] Trace: " . $e->getTraceAsString());
            $this->logger->error('unable to create quote or order, exception below');
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }

        return null;
    }

    /**
     * Valid Request params.
     *
     * @return array|bool
     */
    protected function validParams()
    {
        $bodyParams = $this->request->getBodyParams();
        foreach ($this->mandatoryParams as $param) {
            if (!isset($bodyParams[$param])) {
                return false;
            }
        }

        return $bodyParams;
    }
    /**
     * Log anythings
     * @param $arg
     */
    private function myLogger($arg){
        $message = print_r($arg, true);
        $this->twintLogger->info($message);
    }

}
