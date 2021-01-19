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


use Cellap\Config\Setup\Patch\Data\InitCellapWebshop;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Model\WebsiteFactory;

class UpdateStoresName implements DataPatchInterface, PatchRevertableInterface
{

    const STORE_UPDATES = [
        "nespresso_fr" => "Nespresso ch-fr",
        "nespresso_de" => "Nespresso ch-de",
        "nespresso_en" => "Nespresso ch-en",
        "nespresso_it" => "Nespresso ch-it"
    ];

    const WEBSITE_UPDATES = ["nespresso" => "Nespresso CH"];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var StoreInterfaceFactory
     */
    private $storeFactory;
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * UpdateStoresName constructor.
     * @param StoreManagerInterface $storeManager
     * @param StoreInterfaceFactory $storeInterfaceFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StoreInterfaceFactory $storeInterfaceFactory,
        WebsiteFactory $websiteFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->storeManager = $storeManager;
        $this->storeFactory = $storeInterfaceFactory;
        $this->websiteFactory = $websiteFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return UpdateStoresName|void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        foreach (self::STORE_UPDATES as $key => $value) {
            $this->editStoreName($key, $value);
        }
        foreach (self::WEBSITE_UPDATES as $key => $value) {
            $this->editWebsiteName($key, $value);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Edit the Store name
     * @param $code
     * @param $name
     */
    private function editStoreName($code, $name)
    {
        $store = $this->storeFactory->create();
        $store->load($code);
        if ($store->getId()) {
            $store->setName($name);
            $store->save($store);
        }
    }

    /**
     * @param $code
     * @param $name
     */
    private function editWebsiteName($code, $name)
    {
        $website = $this->websiteFactory->create();
        $website->load($code);
        if ($website->getId()) {
            $website->setName($name);
            $website->save();
        }
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
        return [InitNespressoWebshop::class];
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
