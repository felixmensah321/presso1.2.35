<?php
/**
 * SQLi_Export extension.
 *
 * @category   SQLi
 * @package    SQLi_Export
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Export\Model\Export;

use Psr\Log\LoggerInterface;
use SQLi\Export\Helper\Config;
use SQLi\Export\Model\Entity\Order as OrderEntity;
use SQLi\Export\Model\ProviderFactory;
use SQLi\Export\Model\Type\Csv;
use SQLi\Export\Model\Type\Xml;

/**
 * Class Export.
 *
 * @package SQLi\Export\Model\Order
 */
class Order
{
    /**
     * Entity class used to retrieve row.
     */
    const ENTITY_CLASS = 'SQLi\Export\Model\Entity\Order';

    /**
     * Custom file name for order from cli call
     */
    const CLIFILENAME = 'custom_export';

    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var OrderEntity
     */
    protected $orderEntity;

    /**
     * @var Csv
     */
    protected $csvCreator;
    protected $xmlCreator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * Export constructor.
     *
     * @param ProviderFactory $providerFactory
     * @param OrderEntity $orderEntity
     * @param Csv $csvCreator
     * @param LoggerInterface $logger
     * @param Config $configHelper
     */
    public function __construct(
        ProviderFactory $providerFactory,
        OrderEntity $orderEntity,
        Csv $csvCreator,
        Xml $xmlCreator,
        LoggerInterface $logger,
        Config $configHelper
    ) {
        $this->providerFactory = $providerFactory;
        $this->orderEntity = $orderEntity;
        $this->csvCreator = $csvCreator;
        $this->xmlCreator = $xmlCreator;
        $this->logger = $logger;
        $this->configHelper = $configHelper;
    }

    /**
     * Build and retrieve provider.
     *
     * @throws \Exception
     */
    private function getProvider()
    {
        $provider = $this->providerFactory->create();
        $orders = $this->orderEntity->getOrders();
        $provider->setResults($orders);
        $provider->setHeader($this->orderEntity->getHeader());
        $provider->setFilename($this->configHelper->getFileNamePrefix());
        $provider->setEntityTypeClass(self::ENTITY_CLASS);

        return $provider;
    }

    /**
     * Build and retrieve provider.
     *
     * @throws \Exception
     */
    private function getProviderFromCommandLine($type, $args)
    {
        $provider = $this->providerFactory->create();
        $orders = null;
        switch ($type) {
            case "date":
                    $orders = $this->orderEntity->getOrdersFromDate($args['beginDate'], $args['endDate'], $args['status']);
                break;
            default:
                    $orders = $this->orderEntity->getOrdersFromTwintOrderId($args['orderId'], $args['status']);
                break;
        }
        if ($orders) {
            $provider->setResults($orders);
            $provider->setHeader($this->orderEntity->getHeader());
            $provider->setFilename(self::CLIFILENAME);
            $provider->setEntityTypeClass(self::ENTITY_CLASS);
            return $provider;
        }
        return null;
    }

    /**
     * Create order export xml file.
     */
    public function createOrderExportFile()
    {
        try {
            $this->xmlCreator->createXmlFileFromProvider($this->getProvider(), false);
        } catch (\Exception $e) {
            $this->logger->debug('[SQLi][EXPORT] Exception during order export.');
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Create order export xml file.
     */
    public function createOrderExportFileWithParams($type, $args)
    {
        try {
            $provider = $this->getProviderFromCommandLine($type, $args);
            if ($provider) {
                $this->xmlCreator->createXmlFileFromProvider($provider, false, true);
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->debug('[SQLi][EXPORT] Exception during order export.');
            $this->logger->debug($e->getMessage());
            return false;
        }
    }
}
