<?php
/**
 * SQLi_Export extension.
 *
 * @category   SQLi
 * @package    SQLi_Export
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Export\Model\Entity;

use DateTime;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHP_CodeSniffer\Tokenizers\PHP;
use SQLi\Export\Helper\Config;

/**
 * Class Order.
 *
 * @package SQLi\Export\Model\Entity
 */
class Order
{
    /**
     * Header colums used in the order export.
     */
    const HEADER_COLUMN = [
        'ID',
        'Twint Order ID',
        'Purchase Point ID',
        'Date',
        'Time',
        'Customer Name',
        'Customer Email',
        'Nespresso Club Member ID',
        'SKU',
        'Qty Purchased',
        'Grand Total (Purchased)',
        'Status',
        'Product Multiple'
    ];

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Order constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager,
        Config $configHelper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
    }

    /**
     * Return Search Criteria object with dates and status (optional) filter.
     *
     * @param string|null $status
     *
     * @return \Magento\Framework\Api\SearchCriteria
     *
     */
    public function getSearchCriteria($status = null)
    {
        $this->searchCriteriaBuilder->addFilter('exported_at', true, 'null');

        if ($status) {
            $this->searchCriteriaBuilder->addFilter('status', $status, 'eq');
        }
        $this->searchCriteriaBuilder->setPageSize(500);
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Get orders.
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     *
     * @throws \Exception
     */
    public function getOrders()
    {
        return $this->orderRepository->getList($this->getSearchCriteria('complete'));
    }

    /**
     * Get orders from dates provided.
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     *
     * @throws \Exception
     */
    public function getOrdersFromDate($begin, $end, $status)
    {
        $beginDate = DateTime::createFromFormat('m/d/Y', $begin);

        if ($end) {
            $endDate = DateTime::createFromFormat('m/d/Y', $end);
        } else {
            $endDate = new \DateTime('now', new \DateTimeZone($this->configHelper->getLocal()));
        }

        echo "Recovering orders from " . $beginDate->format('Y-M-d') . ' 00:00:00' . ' to ' . $endDate->format('Y-M-d') . ' 23:59:59' . PHP_EOL;

        $this->searchCriteriaBuilder->addFilter('exported_at', true, 'null');
        $this->searchCriteriaBuilder->addFilter('created_at', $beginDate->format('Y-m-d') . ' 00:00:00', 'gt')
            ->addFilter('created_at', $endDate->format('Y-m-d') . ' 23:59:59', 'lteq');
        if ($status) {
            $this->searchCriteriaBuilder->addFilter('status', $status, 'eq');
        }
        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * Get orders from TWINT orderId provided.
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     *
     * @throws \Exception
     */
    public function getOrdersFromTwintOrderId($orderId, $status)
    {
        $orderId =  explode(",", $orderId[0]);
        $this->searchCriteriaBuilder->addFilter('exported_at', true, 'null');
        $this->searchCriteriaBuilder->addFilter('twint_order_id', $orderId, 'in');
        if ($status) {
            $this->searchCriteriaBuilder->addFilter('status', $status, 'eq');
        }
        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * Get header.
     *
     * @return array
     */
    public function getHeader()
    {
        return self::HEADER_COLUMN;
    }

    /**
     * Get formatted row data.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getRow($order)
    {
        $dateTime =  new \DateTime($order->getCreatedAt());

        return [
            $order->getIncrementId(),
            $order->getTwintOrderId(),
            $order->getNespressoPurchasePointId(),
            $dateTime->format('d.m.Y'),
            $dateTime->format('H:i'),
            $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
            $order->getCustomerEmail(),
            $order->getNespressoClubMemberId(),
            $this->getSkusFromOrder($order),
            $order->getTotalQtyOrdered(),
            $order->getGrandTotal(),
            $order->getStatus(),
            $this->getProductMultipleFromOrder($order)
        ];
    }

    /**
     * Retrieve string with ordered skus.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return string
     */
    private function getSkusFromOrder($order)
    {
        $sku = [];
        foreach ($order->getItems() as $item) {
            $sku[] = $item->getSku();
        }

        return implode(';', $sku);
    }

    /**
     * Retrieve string with product multiple attribute.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return string
     */
    private function getProductMultipleFromOrder($order)
    {
        $productMultiple = [];
        foreach ($order->getItems() as $item) {
            if ($item->getProduct()) {
                if ($item->getProduct()->getMultipleProduct()) {
                    $productMultiple[] = $item->getProduct()->getMultipleProduct();
                }
            }
        }

        return implode(';', $productMultiple);
    }
}
