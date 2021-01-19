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

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use SQLi\Customer\Helper\Data;

/**
 * Class UpdateCustomerNewsletterOptinAttribute.
 *
 * @package SQLi\Customer\Setup\Patch\Data
 */
class UpdateCustomerNewsletterOptinAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * AddNespressoMemberIdAttribute constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $optinAttribute = $customerSetup->getAttributeId(Customer::ENTITY, 'optin_fidelity');

        // Change previous optin attribute code
        if ($optinAttribute) {
            $customerSetup->updateAttribute(
                Customer::ENTITY,
                'optin_fidelity',
                'attribute_code',
                Data::ATTRIBUTE_NEWSLETTER_OPTIN
            );
        }

        // Set default value as 0
        $customerSetup->updateAttribute(
            Customer::ENTITY,
            Data::ATTRIBUTE_NEWSLETTER_OPTIN,
            'default_value',
            0
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddCustomerOptinAttribute::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
