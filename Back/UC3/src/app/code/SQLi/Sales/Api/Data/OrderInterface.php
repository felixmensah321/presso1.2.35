<?php

namespace SQLi\Sales\Api\Data;

/**
 * Order interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 */
interface OrderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * State.
     */
    const STATE = 'state';
    /*
     * Status.
     */
    const STATUS = 'status';
    /*
     * Store ID.
     */
    const STORE_ID = 'store_id';
    /*
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Total due.
     */
    const TOTAL_DUE = 'total_due';
    /*
     * Items.
     */
    const ITEMS = 'items';
    /*
     * Billing address.
     */
    const BILLING_ADDRESS = 'billing_address';
    /*
     * Payment.
     */
    const PAYMENT = 'payment';
    /*
     * Status histories.
     */
    const STATUS_HISTORIES = 'status_histories';

    /**
     * Gets the state for the order.
     *
     * @return string|null State.
     */
    public function getState();

    /**
     * Sets the state for the order.
     *
     * @param string $state
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setState($state);

    /**
     * Gets the status for the order.
     *
     * @return string|null Status.
     */
    public function getStatus();

    /**
     * Sets the status for the order.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Gets the store ID for the order.
     *
     * @return int|null Store ID.
     */
    public function getStoreId();

    /**
     * Sets the store ID for the order.
     *
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Gets the total due for the order.
     *
     * @return float|null Total due.
     */
    public function getTotalDue();

    /**
     * Sets the total due for the order.
     *
     * @param float $totalDue
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setTotalDue($totalDue);

    /**
     * Gets items for the order.
     *
     * @return \SQLi\Sales\Api\Data\OrderItemInterface[] Array of items.
     */
    public function getItems();

    /**
     * Sets items for the order.
     *
     * @param \SQLi\Sales\Api\Data\OrderItemInterface[] $items
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setItems($items);

    /**
     * Gets the billing address, if any, for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null Billing address. Otherwise, null.
     */
    public function getBillingAddress();

    /**
     * Sets the billing address, if any, for the order.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setBillingAddress(\Magento\Sales\Api\Data\OrderAddressInterface $billingAddress = null);

    /**
     * Gets status histories for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryInterface[]|null Array of status histories.
     */
    public function getStatusHistories();

    /**
     * Sets status histories for the order.
     *
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface[] $statusHistories
     * @return $this
     */
    public function setStatusHistories(array $statusHistories = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\OrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\OrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderExtensionInterface $extensionAttributes);

    /**
     * Gets the created-at timestamp for the order.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the order.
     *
     * @param string $createdAt timestamp
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the updated-at timestamp for the order.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Sets the updated-at timestamp for the order.
     *
     * @param string $timestamp
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setUpdatedAt($timestamp);
}
