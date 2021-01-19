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


use Magento\Config\Model\ResourceModel\Config as ConfigWriter;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreManagerInterface;

class PhoneNumberNotRequired implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ConfigWriter
     */
    protected $configWriter;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * PhoneNumberNotRequired constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [InitNespressoWebshop::class];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return UpdateStoresLocales|void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->updateAttribute(\Magento\Customer\Api\AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS,'telephone','is_required','false');

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return array|void
     */
    public function revert()
    {
        return [];
    }
}
