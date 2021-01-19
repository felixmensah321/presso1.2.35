<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Import\Model\Parser;

use SQLi\Import\Logger\ImportLogger;
use SQLi\Import\Model\Mapper\Customer as CustomerMapper;
use SQLi\Import\Model\Service\Customer as CustomerService;

/**
 * Class Csv.
 *
 * @package SQLi\Import\Model\Parser
 */
class Csv
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
     * Csv delimiter.
     */
    const DELIMITER = ';';

    /**
     * Csv enclosure.
     */
    const ENCLOSURE = '"';

    /**
     * Csv lineLength.
     */
    const LINELENGTH = 0;

    /**
     * Traitment size
     */
    const BLOC_SIZE = 20000;

    /**
     * Required columns to parse the file.
     *
     * @var array
     */
    protected $requiredColumns = [
        "Third Code",
        "Address Nr (Main Adr Last)",
        "Lang (Main Adr Last)",
        "Company (Main Adr Last)",
        "Civility (Main Adr Last)",
        "Name (Main Adr Last)",
        "First Name (Main Adr Last)",
        "Contact (Main Adr Last)",
        "Post Code (Main Adr Last)",
        "City (Main Adr Last)",
        "Address Line 1 (Main Adr Last)",
        "Address Line 2 (Main Adr Last)",
        "Email"
    ];

    /**
     * This will contain the data of the file.
     *
     * @var array
     */
    protected $csvData = [];

    /**
     * This will contain the data of the file.
     *
     * @var array
     */
    protected $columnRow = [];

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessorFactory;

    /**
     * @var ImportLogger
     */
    protected $logger;

    /**
     * @var null
     */
    protected $fileName = null;

    /**
     * Csv constructor.
     *
     * @param \Magento\Framework\File\CsvFactory $csvProcessorFactory
     * @param ImportLogger $logger
     */
    public function __construct(
        \Magento\Framework\File\CsvFactory $csvProcessorFactory,
        ImportLogger $logger
    )
    {
        $this->csvProcessorFactory = $csvProcessorFactory;
        $this->logger = $logger;
    }

    public function setCustomerServiceAndMappier($customerService, $customerMapper)
    {
        $this->customerMapper = $customerMapper;
        $this->customerService = $customerService;
    }

    /**
     * Set filename.
     *
     * @param $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Read csv file.
     *
     * @throws \Exception
     */
    public function readFile()
    {
        if (!$this->fileName) {
            $this->logger->debug('[CUSTOMER] PARSER - filename is null');
            return false;
        }
        if (!file_exists($this->fileName)) {
            $this->logger->debug('[CUSTOMER] PARSER - filename does not exist');
            return false;
        }
        $this->theregetData($this->fileName);
    }

    /**
     * Retrieve CSV file data as array
     *
     * @param string $file
     * @return  array
     * @throws \Exception
     */
    public function theregetData($file)
    {
        $data = [];
        if (!file_exists($file)) {
            throw new \Exception('File "' . $file . '" does not exist');
        }

        $index = 0;
        $blocknumber = 0;
        $this->customerService->initCustomerEntityEmailMapping();

        $fh = fopen($file, 'r');
        $this->customerService->setMapper($this->customerMapper);
        while ($rowData = fgetcsv($fh, self::LINELENGTH, self::DELIMITER, self::ENCLOSURE)) {
            if ($index == 0) {
                $this->columnRow[] = $rowData;
            } elseif ($index == self::BLOC_SIZE) {
                $data[] = $rowData;
                $index = 0;
                $blocknumber++;
                $this->customerService->detectNewOrUpdateCustomer($rowData);
                $this->logger->debug("NewBlock");
                $this->customerService->insertOrUpdateCustomerList();
                $this->customerService->clearData();
                $data = array();
            } else {
                $this->customerService->detectNewOrUpdateCustomer($rowData);
            }
            $index++;
        }
        $this->customerService->insertOrUpdateCustomerList();
        fclose($fh);
        return $data;
    }

    /**
     * Check if the file got the required columns.
     *
     * @return bool
     */
    public function checkColumns()
    {
        $this->logger->debug('[CUSTOMER] PARSER - START');
        try {
            $this->readFile();
        } catch (\Exception $e) {
            $this->logger->debug('[CUSTOMER] PARSER - Error during parsing the file, exception below');
            $this->logger->debug($e->getMessage());
        }

        if (!isset($this->columnRow[0])) {
            return false;
        }

        foreach ($this->columnRow[0] as $key => $value) {
            if (array_search($value, $this->requiredColumns) === false) {
                $this->logger->debug('[CUSTOMER] PARSER - required column not found : ' . $value);
                return false;
            }
        }
        $this->logger->debug('[CUSTOMER] PARSER - END');
        return true;
    }
}
