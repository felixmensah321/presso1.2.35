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

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config.
 *
 * @package SQLi\Import\Model
 */
class Config
{
    /**
     * XML Paths.
     */
    const XML_PATH_IMPORT_FOLDER = 'import/customer/import_folder';

    const XML_PATH_IMPORT_FILENAME = 'import/customer/filename';

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
     * Retrieve import filename.
     *
     * @return mixed
     */
    public function getImportFilename()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IMPORT_FILENAME
        );
    }

    /**
     * Retrieve import folder.
     *
     * @return mixed
     */
    public function getImportFolder()
    {
        $importFolder = $this->scopeConfig->getValue(
            self::XML_PATH_IMPORT_FOLDER
        );
        if ($importFolder == null) {
            $importFolder = 'customers_import/';
        }
        return  $importFolder;
    }
}
