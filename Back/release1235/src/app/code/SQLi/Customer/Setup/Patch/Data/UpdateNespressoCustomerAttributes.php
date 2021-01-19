<?php
/**
 * SQLi_Customer extension.
 *
 * @category   SQLi
 * @package    SQLi_Customer
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Customer\Setup\Patch\Data;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Setup\Module\Setup;
use SQLi\Customer\Setup\Patch\Schema\UpdateNespressoMemberId;

/**
 * Class UpdateNespressoCustomerAttributes
 * @package SQLi\Customer\Setup\Patch\Data
 */
class UpdateNespressoCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * UpdateNespressoCustomerAttributes constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resourceConnection
     * @param ModuleDataSetupInterface $setup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        ModuleDataSetupInterface $setup,
        CustomerSetupFactory $customerSetupFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->moduleDataSetup = $setup;
        $this->resourceConnection = $resourceConnection;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @return UpdateNespressoCustomerAttributes|void
     * @throws \Exception
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup->startSetup();

        $this->updateCustomerFields();
        $this->updateOldAttributes();
        $this->cleanCaches();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection() {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Move customer attribute "nespresso_club_member_id" and "customer_relation_id" to fields on the table
     */
    public function updateCustomerFields()
    {
        $nespressoClubMemberIdAttribute = $this->attributeRepository->get(Customer::ENTITY, 'nespresso_club_member_id');

        if (!$nespressoClubMemberIdAttribute) {
            throw new \Exception("Nespresso Club Member ID does not exist and so could not be fixed");
        }

        $customerRelationIdAttribute = $this->attributeRepository->get(Customer::ENTITY, 'customer_relation_id');

        if (!$customerRelationIdAttribute) {
            throw new \Exception("Customer Relation ID does not exist and so could not be fixed");
        }

        $connection = $this->getConnection();
        $customerTable = $connection->getTableName('customer_entity');
        $attributeValueTable = $connection->getTableName('customer_entity_varchar');

        // Nespresso Club Member Id transfert
        $nespressoClubMemberIdSelect = $connection
            ->select()
            ->from($attributeValueTable)
            ->where('attribute_id = ?', $nespressoClubMemberIdAttribute->getAttributeId());

        $nespressoClubMemberIdValues = $connection->fetchAll($nespressoClubMemberIdSelect);
        // Process by limited batches to avoid memory issues
        $maxByBatches = 100000;
        $current = 0;

        $nespressoClubMemberIdToUpdate = [];

        foreach ($nespressoClubMemberIdValues as $nespressoClubMemberIdValue) {
            $valueId = $nespressoClubMemberIdValue['value_id'];
            $customerId = $nespressoClubMemberIdValue['entity_id'];
            $nespressoClubMemberId = $nespressoClubMemberIdValue['value'];
            $nespressoClubMemberIdToUpdate[] = [
                'entity_id' => $customerId,
                'nespresso_club_member_id' => $nespressoClubMemberId
            ];
            // Remove old value
            $connection->delete($attributeValueTable, ['value_id = ?' => $valueId]);
            $current++;
            if ($current > $maxByBatches) {
                $connection->insertOnDuplicate($customerTable, $nespressoClubMemberIdToUpdate);

                $nespressoClubMemberIdToUpdate = [];
                $current = 0;
            }
        }
        // Last batch if necessary
        if (!empty($nespressoClubMemberIdToUpdate)) {
            $connection->insertOnDuplicate($customerTable, $nespressoClubMemberIdToUpdate);
        }

        // Customer Relation Id transfert
        $customerRelationIdSelect = $connection
            ->select()
            ->from($attributeValueTable)
            ->where('attribute_id = ?', $customerRelationIdAttribute->getAttributeId());

        $customerRelationIdValues = $connection->fetchAll($customerRelationIdSelect);
        $customerRelationIdToUpdate = [];
        $current = 0;

        foreach ($customerRelationIdValues as $customerRelationIdValue) {
            $valueId = $customerRelationIdValue['value_id'];
            $customerId = $customerRelationIdValue['entity_id'];
            $customerRelationId = $customerRelationIdValue['value'];
            $customerRelationIdToUpdate[] = [
                'entity_id' => $customerId,
                'customer_relation_id' => $customerRelationId
            ];
            // Remove old value
            $connection->delete($attributeValueTable, ['value_id = ?' => $valueId]);
            $current++;
            if ($current > $maxByBatches) {
                $connection->insertOnDuplicate($customerTable, $customerRelationIdToUpdate);
                $customerRelationIdToUpdate = [];
                $current = 0;
            }
        }
        // Last batch if necessary
        if (!empty($customerRelationIdToUpdate)) {
            $connection->insertOnDuplicate($customerTable, $customerRelationIdToUpdate);
        }
    }

    /**
     * Update old customer attributes
     */
    protected function updateOldAttributes()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(
            Customer::ENTITY,
            'nespresso_club_member_id',
            [
                'backend_type' => 'static',
                'is_used_in_grid' => true
            ]
        );
        $customerSetup->updateAttribute(
            Customer::ENTITY,
            'customer_relation_id',
            [
                'backend_type' => 'static',
                'is_used_in_grid' => true
            ]
        );
    }

    /**
     * Clean caches
     */
    protected function cleanCaches()
    {
        $types = ['config', 'eav'];

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddNespressoMemberIdAttribute::class,
            AddCustomerRelationIdAttribute::class,
            UpdateNespressoMemberId::class,
            UpdateNespressoMemberIdAttribute::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
