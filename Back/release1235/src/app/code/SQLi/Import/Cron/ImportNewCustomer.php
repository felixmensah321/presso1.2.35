<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Import\Cron;

use SQLi\Import\Logger\ImportLogger;
use SQLi\Import\Model\Manager;

/**
 * Class ImportNewCustomer.
 *
 * @package SQLi\Import\Cron
 */
class ImportNewCustomer
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var ImportLogger
     */
    protected $logger;

    /**
     * ImportNewCustomer constructor.
     *
     * @param Manager $manager
     * @param ImportLogger $logger
     */
    public function __construct(
      Manager $manager,
      ImportLogger $logger
    ) {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    /**
     * Automatically import new customers.
     */
    public function execute()
    {
        try {
            $this->manager->launchCustomerImport();
        } catch (\Exception $e) {
            $this->logger->debug('[CUSTOMER] CRON - Exception during import');
            $this->logger->debug($e->getMessage());
        }
    }
}
