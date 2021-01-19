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
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreManagerInterface;

class DisableNewCustomerAccountConfirmation implements DataPatchInterface, PatchRevertableInterface
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
     * UpdateStoresLocales constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ConfigWriter $configWriter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ConfigWriter $configWriter,
        StoreManagerInterface $storeManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
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
        $configurationList = [
            'customer/create_account/confirm' => "0"
        ];
        $this->moduleDataSetup->startSetup();
        foreach ($configurationList as $path => $value) {
            $this->configWriter->saveConfig(
                $path,
                $value,
                'default',
                0
            );
        }
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
