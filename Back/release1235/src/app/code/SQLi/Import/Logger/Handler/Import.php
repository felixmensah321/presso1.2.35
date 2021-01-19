<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Import\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;

/**
 * Class Import.
 *
 * @package SQLi\Import\Logger\Handler
 */
class Import extends Base
{
    const FILENAME = '/var/log/nespresso_import.log';

    /**
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;
}
