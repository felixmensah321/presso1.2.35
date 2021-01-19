<?php

namespace SQLi\Sales\Api\Data;

/**
 * Order item interface.
 *
 * @api
 */
interface OrderItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * SKU
     */
    const SKU = 'sku';
    /**
     * Name of the product
     */
    const NAME = 'name';
    /**
     * Qty Ordered
     */
    const QTY_ORDERED = 'qty_ordered';
    /**
     * Price including taxes
     */
    const PRICE_INCL_TAX = 'price_incl_tax';
    /**
     * Row total including taxes
     */
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';

    /**
     * Gets the SKU for the order item.
     *
     * @return string SKU.
     */
    public function getSku();

    /**
     * Sets the SKU for the order item.
     *
     * @param string $sku
     * @return \SQLi\Sales\Api\Data\OrderItemInterface
     */
    public function setSku($sku);

    /**
     * Gets the name for the order item.
     *
     * @return string|null Name.
     */
    public function getName();

    /**
     * Sets the name for the order item.
     *
     * @param string $name
     * @return \SQLi\Sales\Api\Data\OrderItemInterface
     */
    public function setName($name);

    /**
     * Gets the quantity ordered for the order item.
     *
     * @return float|null Quantity ordered.
     */
    public function getQtyOrdered();

    /**
     * Sets the quantity ordered for the order item.
     *
     * @param float $qtyOrdered
     * @return \SQLi\Sales\Api\Data\OrderItemInterface
     */
    public function setQtyOrdered($qtyOrdered);

    /**
     * Gets the price including tax for the order item.
     *
     * @return float|null Price including tax.
     */
    public function getPriceInclTax();

    /**
     * Sets the price including tax for the order item.
     *
     * @param float $amount
     * @return \SQLi\Sales\Api\Data\OrderItemInterface
     */
    public function setPriceInclTax($amount);

    /**
     * Gets the row total including tax for the order item.
     *
     * @return float|null Row total including tax.
     */
    public function getRowTotalInclTax();

    /**
     * Sets the row total including tax for the order item.
     *
     * @param float $amount
     * @return \SQLi\Sales\Api\Data\OrderItemInterface
     */
    public function setRowTotalInclTax($amount);

}
