<?php

namespace SQLi\Customer\Setup\Patch\Data;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Setup\Patch\Data\UpdateIdentifierCustomerAttributesVisibility;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use SQLi\Customer\Helper\Data;

class UpdateNespressoMemberIdAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    private $i = 0;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $collectionFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->moduleDataSetup = $setup;
    }

    public function trimmFields($customerCollection)
    {
        echo "Current Page : " . $this->i . PHP_EOL;
        $this->i++;
        foreach ($customerCollection as $customer) {
            $c = $this->customerRepository->getById($customer->getId());
            $val = $c->getCustomAttribute(Data::ATTRIBUTE_NESPRESSO_MEMBER_ID);
            $valTrimmed = ltrim($val->getValue(), '0');
            $customer->setNespressoClubMemberId($valTrimmed);
            $customer->getResource()->saveAttribute($customer, Data::ATTRIBUTE_NESPRESSO_MEMBER_ID);
            unset($customer);
            unset($c);
            unset($val);
            unset($valTrimmed);
        }
        //unset($customerCollection);
        $customerCollection = $this->getCollection();
        if ($customerCollection->count() > 0) {
            $this->trimmFields($customerCollection);
        }
        return;
    }

    public function getCollection()
    {
        echo "Get Collection Begin" . PHP_EOL;
        $customerCollection = $this->collectionFactory->create();
        $customerCollection->addFieldToFilter(Data::ATTRIBUTE_NESPRESSO_MEMBER_ID, ['like' => '0%']);
        $customerCollection->setPageSize(1000);
        $customerCollection->setCurPage($this->i)->load();
        echo "Get Collection End" . PHP_EOL;
        return $customerCollection;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('customer_entity_varchar');
        $sql="UPDATE " . $tableName . " SET value = TRIM(LEADING '0' FROM value) WHERE value LIKE '0%'";
        $connection->query($sql);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateIdentifierCustomerAttributesVisibility::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
