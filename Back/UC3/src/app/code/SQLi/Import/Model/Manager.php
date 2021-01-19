<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Import\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use SQLi\Import\Model\Mapper\Customer as CustomerMapper;
use SQLi\Import\Model\Parser\Csv as CsvParser;
use SQLi\Import\Model\Service\Customer as CustomerService;

/**
 * Class Manager.
 *
 * @package SQLi\Import\Model
 */
class Manager
{
    /**
     * @var CustomerMapper
     */
    protected $customerMapper;

    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * @var CsvParser
     */
    protected $csvParser;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var IndexerInterfaceFactory
     */
    protected $indexerFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Manager constructor.
     *
     * @param CustomerMapper $customerMapper
     * @param CustomerService $customerService
     * @param CsvParser $csvParser
     * @param Config $config
     * @param DirectoryList $directoryList
     * @param IndexerInterfaceFactory $indexerFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        CustomerMapper $customerMapper,
        CustomerService $customerService,
        CsvParser $csvParser,
        Config $config,
        DirectoryList $directoryList,
        IndexerInterfaceFactory $indexerFactory,
        Filesystem $filesystem
    ) {
        $this->customerMapper = $customerMapper;
        $this->customerService = $customerService;
        $this->csvParser = $csvParser;
        $this->config = $config;
        $this->directoryList = $directoryList;
        $this->indexerFactory = $indexerFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Launch Customer Import.
     *
     * @param array|null $files
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function launchCustomerImport($files = null)
    {
        $baseDirectory = $this->directoryList->getPath('var');
        $importDir = $baseDirectory . '/import/' . $this->config->getImportFolder();
        $fileNameToLoop = array();
        if ($files != null) {
            $fileNameToLoop = $files;
        } else {
            $fileNameToLoop = $this->retrieveFileToImport($importDir);
            asort($fileNameToLoop);
        }
        if (!empty($fileNameToLoop)) {
            foreach($fileNameToLoop as $name){
                $file = $importDir . $name;
                echo $file . PHP_EOL;
                $this->csvParser->setCustomerServiceAndMappier($this->customerService, $this->customerMapper);
                $this->csvParser->setFileName($file);
                $this->csvParser->checkColumns();
                $this->moveFileToArchiveFolder($file);
            }
            $this->reindexCustomerGrid();
            return true;
        }
    }

    /**
     * Retrieve filepath + filename to import.
     *
     * @return array
     */
    public function retrieveFileToImport($base)
    {
        return array_diff(scandir($base), array('..', '.'));
    }

    /**
     * Move to archive folder at the end of the import.
     *
     * @param $filename
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function moveFileToArchiveFolder($filename)
    {
        $backupFolder = $this->directoryList->getPath('var') . '/import/history';

        if (!is_dir($backupFolder)) {
            mkdir($backupFolder, 0777, true);
        }

        $fileArchive = $backupFolder . '/' . 'customer_archive_' . time() . '.csv';
        rename($filename, $fileArchive);
    }

    /**
     * Reindex customer grid.
     *
     * @throws \Exception
     */
    public function reindexCustomerGrid()
    {
        $indexer = $this->indexerFactory->create()->load('customer_grid');
        $indexer->reindexAll();
    }
}


