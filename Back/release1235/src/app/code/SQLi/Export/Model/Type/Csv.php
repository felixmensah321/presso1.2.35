<?php
/**
 * SQLi_Export extension.
 *
 * @category   SQLi
 * @package    SQLi_Export
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Export\Model\Type;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use SQLi\Export\Helper\Config;
use SQLi\Export\Model\Provider;

/**
 * Class Csv.
 *
 * @package SQLi\Export\Model\Type
 */
class Csv
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * Csv constructor.
     *
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Config $configHelper
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->configHelper = $configHelper;
    }

    /**
     * Create csv file from provider.
     *
     * @param Provider $provider
     *
     * @return array|bool
     *
     * @throws FileSystemException
     */
    public function createCsvFileFromProvider($provider)
    {
        if ($provider->getResults()->getTotalCount() < 0) {
            return false;
        }

        $exportFolder = $this->configHelper->getExportFolder();
        $file = 'export/' . $exportFolder . $provider->getFilename() . '_' . date('Ymd') . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($provider->getHeader());
        $items = $provider->getResults()->getItems();

        foreach ($items as $item) {
            $stream->writeCsv($provider->getRowData($item));
        }

        $stream->unlock();
        $stream->close();

        return [
            'value' => $file,
        ];
    }
}
