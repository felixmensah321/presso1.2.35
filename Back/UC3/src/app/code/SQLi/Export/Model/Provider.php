<?php
/**
 * SQLi_Export extension.
 *
 * @category   SQLi
 * @package    SQLi_Export
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Export\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Provider.
 *
 * @package SQLi\Export\Model
 */
class Provider
{
    /**
     * @var array
     */
    protected $header;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterface
     */
    protected $results;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var
     */
    protected $entityObject;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Provider constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Set results.
     *
     * @param \Magento\Framework\Api\SearchResultsInterface $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * Get results.
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set header row.
     *
     * @param array $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Get header row.
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Retrieve filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get row data for custom entity.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $entity
     *
     * @return array
     */
    public function getRowData($entity)
    {
        return $this->entityObject->getRow($entity);
    }

    /**
     * Set entity type class name.
     *
     * @param string $className
     *
     */
    public function setEntityTypeClass($className)
    {
        $this->entityObject = $this->objectManager->create($className);
    }

}