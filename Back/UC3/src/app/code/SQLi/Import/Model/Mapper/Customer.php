<?php
/**
 * SQLi_Import extension.
 *
 * @category   SQLi
 * @package    SQLi_Import
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Import\Model\Mapper;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use SQLi\Customer\Helper\Data;
use SQLi\Import\Logger\ImportLogger;

/**
 * Class Customer.
 *
 * @package SQLi\Import\Model\Mapper
 */
class Customer
{
    /**
     * Mandatory row length used to map data.
     */
    const ROW_MANDATORY_LENGTH = 10;

    /**
     * Index where we can find the lang.
     */
    const ROW_LANG_INDEX = 2;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $storesLangIdMapping = [];

    /**
     * @var ImportLogger
     */
    protected $logger;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var null|int
     */
    protected $nesClubMemberAttrId = null;

    /**
     * Customer constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ImportLogger $logger
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ImportLogger $logger,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->attributeRepository = $attributeRepository;
    }

    public function getStoresLangIdMapping()
    {
        return $this->storesLangIdMapping;
    }

    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Init store mapping
     */
    public function initStoreMapping()
    {
        if (empty($this->storesLangIdMapping)) {
            foreach ($this->storeManager->getStores() as $store) {
                $explodedCode = explode('_', $store->getCode());
                if (empty($explodedCode[1])) {
                    continue;
                }

                $this->storesLangIdMapping[strtoupper($explodedCode[1])] = $store->getId();
            }
        }
    }

    /**
     * Retrieve the Nespresso Club Member Id attribute Id.
     *
     * @return int|null
     */
    public function getNesClubMemberAttrId()
    {
        if ($this->nesClubMemberAttrId == null) {
            try {
                $this->nesClubMemberAttrId = $this->attributeRepository->get(
                    'customer',
                    Data::ATTRIBUTE_NESPRESSO_MEMBER_ID
                )->getAttributeId();
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->nesClubMemberAttrId;
    }
}
