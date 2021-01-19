<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;

/**
 * Class Twint.
 *
 * @package SQLi\Twint\Logger\Handler
 */
class Twint extends Base
{
    const FILENAME = '/var/log/twint.log';

    /**
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;
}
