<?php
/**
 * SQLi_Customer extension.
 *
 * @category   SQLi
 * @package    SQLi_Customer
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class RecurringData
 * Fix Reindex if necessary only
 * @package SQLi\Customer\Setup
 */
class RecurringData extends \Magento\Customer\Setup\RecurringData
{

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * RecurringData constructor.
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($this->isNeedToDoReindex($setup)) {
            $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
            $indexer->reindexAll();
        }
    }

    /**
     * Check is re-index needed
     *
     * @param ModuleDataSetupInterface $setup
     * @return bool
     */
    protected function isNeedToDoReindex(ModuleDataSetupInterface $setup) : bool
    {
        return !$setup->tableExists('customer_grid_flat')
            || $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID)
                ->getState()
                ->getStatus() == StateInterface::STATUS_INVALID;
    }

}
