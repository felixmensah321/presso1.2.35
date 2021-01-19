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

/**
 * Class QRCode.
 *
 * @package SQLi\Twint\Model
 */
class QRCode
{
    /**
     * @var TwintLogger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    const CATEGORY_ID = "categoryId";

    /**
     * QRCode constructor.
     *
     * @param TwintLogger $logger
     * @param Config $config
     */
    public function __construct(
        TwintLogger $logger,
        TwintConfig $config
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get custom parameters of the QR code.
     * It is added at the end of the beacon init string.
     *
     * @return array|boolean
     */
    public function getCustomParameters($qrCode)
    {
        if (strpos($qrCode, $this->config->getBeaconInitString()) === false) {
            $this->logger->debug('Beacon init string not found in the QR code');
            return false;
        }

        $explodedCode = explode('#', $qrCode);

        //there is no custom parameters
        if (!isset($explodedCode[2])) {
            return [];
        }

        $customParams = explode('&', $explodedCode[2]);

        $paramsToReturn = [];
        $hasCategory = false;

        foreach ($customParams as $customParam) {
            $param = explode('=', $customParam);
            if (isset($param[0]) && isset($param[1])) {
                if (strcmp($param[0], self::CATEGORY_ID) === 0) {
                    $hasCategory = true;
                }
                $paramsToReturn[$param[0]] = $param[1];
            }
        }

        if (!$hasCategory) {
            $paramsToReturn[self::CATEGORY_ID] = $this->config->getDefaultCategory();
        }

        return $paramsToReturn;
    }

    /**
     * Generate random session id (will be available for 15min).
     *
     * @return int
     */
    public function generateSessionId()
    {
        return rand(pow(10, 13), pow(10, 16) - 1);
    }
}
