<?php
/**
 * SQLi_Config extension.
 *
 * @category   SQLi
 * @package    SQLi_Config
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Config\Setup\Patch\Data;

use Magento\Catalog\Api\AttributeSetManagementInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Api\Data\AttributeSetInterfaceFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Class CreateNespressoAttribute.
 *
 * @package SQLi\Config\Setup\Patch\Data
 */
class CreateNespressoAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var AttributeSetInterfaceFactory
     */
    protected $attributeSetFactory;

    /**
     * @var AttributeSetManagementInterface
     */
    protected $attributeSetManagement;

    /**
     * @var CategorySetupFactory
     */
    protected $catalogSetupFactory;

    /**
     * @var ProductAttributeManagementInterface
     */
    protected $productAttributeManagement;

    /**
     * CreateNespressoAttribute constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeSetInterfaceFactory $attributeSetFactory
     * @param AttributeSetManagementInterface $attributeSetManagement
     * @param CategorySetupFactory $categorySetupFactory
     * @param ProductAttributeManagementInterface $attributeManagement
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetInterfaceFactory $attributeSetFactory,
        AttributeSetManagementInterface $attributeSetManagement,
        CategorySetupFactory $categorySetupFactory,
        ProductAttributeManagementInterface $attributeManagement
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->catalogSetupFactory = $categorySetupFactory;
        $this->productAttributeManagement = $attributeManagement;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeSetId = $this->createAttributeSet('Coffee', ProductAttributeInterface::ENTITY_TYPE_ID, 4);

        if ($attributeSetId) {
            $this->createAttributeGroup('Coffee', ProductAttributeInterface::ENTITY_TYPE_ID, $attributeSetId, 10, $catalogSetup);
        }

        $attribute = $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'nb_capsules',
            [
                'group' => 'Coffee',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Number of capsules',
                'input' => 'text',
                'class' => '',
                'source' =>'',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'apply_to' => '',
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'searchable' => true,
                'filterable' => false,
                'comparable' => true,
            ]
        );

        $this->productAttributeManagement->assign(
            $attributeSetId,
            $attribute->getAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, 'Coffee', 'attribute_group_id'),
            $attribute->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'nb_capsules'),
            10
        );

        $attribute = $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'packaging',
            [
                'group' => 'Coffee',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Packaging',
                'input' => 'text',
                'class' => '',
                'source' =>'',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'apply_to' => '',
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'searchable' => true,
                'filterable' => false,
                'comparable' => true,
            ]
        );

        $this->productAttributeManagement->assign(
            $attributeSetId,
            $attribute->getAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, 'Coffee', 'attribute_group_id'),
            $attribute->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'packaging'),
            20
        );
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        //Here should go code that will revert all operations from `apply` method
        //Please note, that some operations, like removing data from column, that is in role of foreign key reference
        //is dangerous, because it can trigger ON DELETE statement
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    private function createAttributeSet($name, $entityType, $skeletonId)
    {
        /** @var \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setAttributeSetName($name);
        $attributeSet->setEntityTypeId($entityType);

        try {
            $attributeSet = $this->attributeSetManagement->create($attributeSet, $skeletonId);
        } catch (\Exception $e) {

        }

        return $attributeSet->getAttributeSetId();
    }

    private function createAttributeGroup($name, $entityType, $attributeSetId, $sortOrder, $setup)
    {
        if (!$setup->getAttributeGroup($entityType, $attributeSetId, $name)) {
            $setup->addAttributeGroup($entityType, $attributeSetId, $name, $sortOrder);
        }
    }
}
