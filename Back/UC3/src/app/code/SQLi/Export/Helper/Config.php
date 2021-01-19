<?php
/**
 * SQLi_Export extension.
 *
 * @category   SQLi
 * @package    SQLi_Export
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Export\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config.
 *
 * @package SQLi\Export\Helper
 */
class Config
{
    /**
     * XML Paths.
     */
    const XML_PATH_FILENAME_PREFIX = 'export/order/filename_prefix';
    const XML_PATH_EXPORT_FOLDER = 'export/order/export_folder';

    const EXPORT_SALES_MOVEMENT_CODE_PURCHASE_POINT = 'export/xml_purchase_point/export_sales_movement_code_purchase_point';
    const EXPORT_ORDER_SOURCE_PURCHASE_POINT = 'export/xml_purchase_point/export_order_source_purchase_point';
    const EXPORT_STOCK_PURCHASE_POINT = 'export/xml_purchase_point/export_stock_purchase_point';
    const EXPORT_DELIVERY_MODE_PURCHASE_POINT = 'export/xml_purchase_point/export_delivery_mode_purchase_point';
    const EXPORT_PAYMENT_MODE_PURCHASE_POINT = 'export/xml_purchase_point/export_payment_mode_purchase_point';
    const EXPORT_SALES_MOVEMENT_CODE_DELIVERY = 'export/xml_delivery/export_sales_movement_code_delivery';
    const EXPORT_ORDER_SOURCE_DELIVERY = 'export/xml_delivery/export_order_source_delivery';
    const EXPORT_STOCK_DELIVERY = 'export/xml_delivery/export_stock_delivery';
    const EXPORT_DELIVERY_MODE_DELIVERY = 'export/xml_delivery/export_delivery_mode_delivery';
    const EXPORT_DELIVERY_MODE_WEEKEND_DELIVERY = 'export/xml_delivery/export_delivery_mode_weekend_delivery';
    const EXPORT_PAYMENT_MODE_DELIVERY = 'export/xml_delivery/export_payment_mode_delivery';
    const STORE_LOCALE = 'general/locale/timezone';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Retrieve filename prefix.
     *
     * @return mixed
     */
    public function getFileNamePrefix()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FILENAME_PREFIX
        );
    }

    /**
     * Retrieve export folder.
     *
     * @return mixed
     */
    public function getExportFolder()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EXPORT_FOLDER
        );
    }

    /**
     * @return mixed
     */
    public function getMovementCode0(){
        return $this->scopeConfig->getValue(
            self::EXPORT_SALES_MOVEMENT_CODE_PURCHASE_POINT
        );
    }

    /**
     * @return mixed
     */
    public function getOrderSource0(){
        return $this->scopeConfig->getValue(
            self::EXPORT_ORDER_SOURCE_PURCHASE_POINT
        );
    }

    /**
     * @return mixed
     */
    public function getStock0(){
        return $this->scopeConfig->getValue(
            self::EXPORT_STOCK_PURCHASE_POINT
        );
    }

    /**
     * @return mixed
     */
    public function getDeliveryMode0(){
        return $this->scopeConfig->getValue(
            self::EXPORT_DELIVERY_MODE_PURCHASE_POINT
        );
    }

    /**
     * @return mixed
     */
    public function getPaymentMode0(){
        return $this->scopeConfig->getValue(
            self::EXPORT_PAYMENT_MODE_PURCHASE_POINT
        );
    }

    /**
     * @return mixed
     */
    public function getMovementCode1(){
        return $this->scopeConfig->getValue(
            self::EXPORT_SALES_MOVEMENT_CODE_DELIVERY
        );
    }

    /**
     * @return mixed
     */
    public function getLocal(){
        return $this->scopeConfig->getValue(
            self::STORE_LOCALE
        );
    }

    /**
     * @return mixed
     */
    public function getOrderSource1(){
        return $this->scopeConfig->getValue(
            self::EXPORT_ORDER_SOURCE_DELIVERY
        );
    }

    /**
     * @return mixed
     */
    public function getStock1(){
        return $this->scopeConfig->getValue(
            self::EXPORT_STOCK_DELIVERY
        );
    }

    /**
     * @return mixed
     */
    public function getDeliveryMode1(){
        return $this->scopeConfig->getValue(
            self::EXPORT_DELIVERY_MODE_DELIVERY
        );
    }

    /**
     * @return mixed
     */
    public function getDeliveryModeWeekend1(){
        return $this->scopeConfig->getValue(
            self::EXPORT_DELIVERY_MODE_WEEKEND_DELIVERY
        );
    }

    /**
     * @return mixed
     */
    public function getPaymentMode1(){
        return $this->scopeConfig->getValue(
            self::EXPORT_PAYMENT_MODE_DELIVERY
        );
    }
}
