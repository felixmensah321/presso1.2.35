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
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Model\ResourceModel\Calculation\Rate;
use Magento\Tax\Model\ResourceModel\Calculation\Rule;

/**
 * Class InitNespressoWebshop.
 *
 * @package SQLi\Config\Setup\Patch\Data
 */
class InitNespressoWebshop implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var Website
     */
    protected $websiteResourceModel;

    /**
     * @var StoreInterfaceFactory
     */
    protected $storeFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var Store
     */
    protected $storeResourceModel;

    /**
     * @var GroupRepositoryInterface
     */
    protected $storeGroupRepository;

    /**
     * @var Group
     */
    protected $storeGroupResourceModel;

    /**
     * @var TaxRuleInterfaceFactory
     */
    protected $taxRuleFactory;

    /**
     * @var TaxRateInterfaceFactory
     */
    protected $taxRateFactory;

    /**
     * @var Rate
     */
    protected $taxRateResourceModel;

    /**
     * @var Rule
     */
    protected $taxRuleResourceModel;

    /**
     * @var ConfigWriter
     */
    protected $configWriter;

    /**
     * InitNespressoWebshop constructor.
     * 
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param Website $websiteResourceModel
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreInterfaceFactory $storeInterfaceFactory
     * @param Store $storeResourceModel
     * @param GroupRepositoryInterface $storeGroupRepository
     * @param Group $storeGroupResourceModel
     * @param TaxRateInterfaceFactory $taxRateFactory
     * @param TaxRuleInterfaceFactory $taxRuleFactory
     * @param Rate $taxRateResourceModel
     * @param Rule $taxRuleResourceModel
     * @param ConfigWriter $configWriter
     */
    public function __construct(
      ModuleDataSetupInterface $moduleDataSetup,
      WebsiteRepositoryInterface $websiteRepository,
      Website $websiteResourceModel,
      StoreRepositoryInterface $storeRepository,
      StoreInterfaceFactory $storeInterfaceFactory,
      Store $storeResourceModel,
      GroupRepositoryInterface $storeGroupRepository,
      Group $storeGroupResourceModel,
      TaxRateInterfaceFactory $taxRateFactory,
      TaxRuleInterfaceFactory $taxRuleFactory,
      Rate $taxRateResourceModel,
      Rule $taxRuleResourceModel,
      ConfigWriter $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->websiteRepository = $websiteRepository;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->storeRepository = $storeRepository;
        $this->storeFactory = $storeInterfaceFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeGroupRepository = $storeGroupRepository;
        $this->storeGroupResourceModel = $storeGroupResourceModel;
        $this->taxRateFactory = $taxRateFactory;
        $this->taxRuleFactory = $taxRuleFactory;
        $this->taxRateResourceModel = $taxRateResourceModel;
        $this->taxRuleResourceModel = $taxRuleResourceModel;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        //rename website
        $this->updateWebsiteData('base', 'code', 'nespresso');
        $this->updateWebsiteData('base', 'name', 'Nespresso');

        //rename store group
        $this->updateStoreGroupData('main_website_store', 'code', 'nespresso_store');
        $this->updateStoreGroupData('nespresso_store', 'name', 'Nespresso Store');

        //rename store view
        $this->updateStoreData('default', 'code', 'nespresso_fr');
        $this->updateStoreData('nespresso_fr', 'name', 'Nespresso France');

        //create store view EN, DE, IT
        $storeGroupId = $this->getStoreGroupIdByCode('nespresso_store');
        if ($storeGroupId) {
            $this->createStoreView('nespresso_de', 1, $storeGroupId, 'Nespresso Deutsch', 1);
            $this->createStoreView('nespresso_en', 1, $storeGroupId, 'Nespresso English', 2);
            $this->createStoreView('nespresso_it', 1, $storeGroupId, 'Nespresso Italian', 3);
        }

        //create global tax
        $rateId = $this->createTaxRate('CH-7.7', 'CH', '*', 7.7);
        if ($rateId) {
            $this->createTaxRule('Nespresso CH', 0, 0, [3], [2], [$rateId]);
        }

        //rename root category id

        //global configuration
        $configurationList = [
            'general/country/default' => 'CH',
            'general/locale/timezone' => 'Europe/Zurich',
            'general/locale/code' => 'fr_CH',
            'general/locale/weight_unit' => 'kgs',
            'general/store_information/name' => 'Nespresso CH',
            'general/store_information/country_id' => 'CH',
            'currency/options/base' => 'CHF',
            'currency/options/default' => 'CHF',
            'tax/defaults/country' => 'CH',
            'tax/display/type' => 2,
            'carriers/flaterate/active' => 1,
            'carriers/flaterate/price' => 0,
            'payment/checkmo/active' => 1,
        ];

        foreach ($configurationList as $path => $value) {
            $this->configWriter->saveConfig(
                $path,
                $value,
                'websites',
               1
            );
        }
        //configuration DE
        $deStore = $this->storeRepository->get('nespresso_de');
        if ($deStore->getId()) {
            $this->configWriter->saveConfig(
                'general/locale/code',
                'de_CH',
                'stores',
                $deStore->getId()
            );
        }

        //configuration IT
        $itStore = $this->storeRepository->get('nespresso_it');
        if ($itStore->getId()) {
            $this->configWriter->saveConfig(
                'general/locale/code',
                'it_CH',
                'stores',
                $itStore->getId()
            );
        }

        //configuration EN
        $enStore = $this->storeRepository->get('nespresso_en');
        if ($enStore->getId()) {
            $this->configWriter->saveConfig(
                'general/locale/code',
                'en_US',
                'stores',
                $enStore->getId()
            );
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
        return [];
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

    /**
     * @param $code
     * @param $dataToUpdate
     * @param $newValue
     */
    private function updateWebsiteData($code, $dataToUpdate, $newValue)
    {
        try {
            $website = $this->websiteRepository->get($code);
            $website->setData($dataToUpdate, $newValue);
            $this->websiteResourceModel->save($website);
        } catch (\Exception $e) {

        }
    }

    /**
     * @param $code
     * @param $dataToUpdate
     * @param $newValue
     */
    private function updateStoreData($code, $dataToUpdate, $newValue)
    {
        try {
            $store = $this->storeRepository->get($code);
            $store->setData($dataToUpdate, $newValue);
            $this->storeResourceModel->save($store);
        } catch (\Exception $e) {

        }
    }

    /**
     * @param $code
     * @param $websiteId
     * @param $groupId
     * @param $name
     * @param $sortOrder
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function createStoreView($code, $websiteId, $groupId, $name, $sortOrder)
    {
        $store = $this->storeFactory->create();

        $store->setCode($code)
            ->setWebsiteId($websiteId)
            ->setGroupId($groupId)
            ->setName($name)
            ->setSortOrder($sortOrder)
            ->setIsActive(1);

        $this->storeResourceModel->save($store);
    }

    /**
     * @param $code
     * @param $dataToUpdate
     * @param $newValue
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function updateStoreGroupData($code, $dataToUpdate, $newValue)
    {
        foreach ($this->storeGroupRepository->getList() as $storeGroup) {
            if ($storeGroup->getCode() == $code) {
                $storeGroup->setData($dataToUpdate, $newValue);
                $this->storeGroupResourceModel->save($storeGroup);
            }
        }
    }

    /**
     * @param $code
     * @return int|null
     */
    private function getStoreGroupIdByCode($code)
    {
        $id = null;

        foreach ($this->storeGroupRepository->getList() as $storeGroup) {
            if ($storeGroup->getCode() == $code) {
                $id = $storeGroup->getId();
            }
        }

        return $id;
    }

    private function createTaxRate($code, $countryId, $postCode, $ratePercent)
    {
        $rate = $this->taxRateFactory->create();

        $rate->setTaxCountryId($countryId)
            ->setTaxPostcode($postCode)
            ->setRate($ratePercent)
            ->setCode($code);

        $this->taxRateResourceModel->save($rate);

        if ($rate->getId()) {
            return $rate->getId();
        }

        return null;
    }

    private function createTaxRule($code, $priority, $position, $customerTaxClassId, $productTaxClass, $taxRateId)
    {
        $rule = $this->taxRuleFactory->create();

        $rule->setCode($code)
            ->setPriority($priority)
            ->setPosition($position)
            ->setCustomerTaxClassIds($customerTaxClassId)
            ->setProductTaxClassIds($productTaxClass)
            ->setTaxRateIds($taxRateId);

        $this->taxRuleResourceModel->save($rule);
    }
}
