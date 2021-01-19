<?php
/**
 * SQLi_Sales extension.
 *
 * @category   SQLi
 * @package    SQLi_Sales
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Sales\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use SQLi\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Metadata;
use SQLi\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;

/**
 * Class OrderRepository.
 *
 * @package SQLi\Sales\Model\OrderRepository
 */
class OrderRepository implements OrderRepositoryInterface
{

    /**
     * @var OrderInterface[]
     */
    protected $registry = [];
    /**
     * @var Metadata
     */
    protected $metadata;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var OrderTaxManagementInterface
     */
    protected $orderTaxManagement;

    /**
     * OrderRepository constructor.
     * @param Metadata $metadata
     * @param OrderFactory $orderFactory
     * @param OrderTaxManagementInterface $orderTaxManagement
     */
    public function __construct(
        Metadata $metadata,
        OrderFactory $orderFactory,
        OrderTaxManagementInterface $orderTaxManagement
    ) {
        $this->metadata = $metadata;
        $this->orderFactory = $orderFactory;
        $this->orderTaxManagement = $orderTaxManagement;
    }

    /**
     * Load entity (return less data than native)
     * @param int $id
     * @return OrderInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get($orderId)
    {
        if (!$orderId) {
            throw new InputException(__('An ID is needed. Set the ID and try again.'));
        }
        if (!isset($this->registry[$orderId])) {
            /** @var OrderInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($orderId);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(
                    __("The entity that was requested doesn't exist. Verify the entity and try again.")
                );
            }
            $this->registry[$orderId] = $entity;
        }
        return $this->registry[$orderId];
    }


}
