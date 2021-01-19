<?php

namespace SQLi\Catalog\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\NoSuchEntityException;
use SQLi\Catalog\Api\CategoryRepositoryInterface;

/**
 * Class CategoryRepository
 * @package SQLi\Catalog\Model
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var Category[]
     */
    protected $trees = [];
    /**
     * @var Category[]
     */
    protected $instances = [];
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CategoryRepository constructor.
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @todo This is for initializing the safety requirement for UC3 optimization
     * @param null $rootCategoryId
     * @param null $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface|mixed|null
     * @throws NoSuchEntityException
     */
    public function getList($rootCategoryId = null, $storeId = null)
    {
        $cacheKey = $storeId ?? 'all';
        if (!isset($this->trees[$rootCategoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }
            $category->load($rootCategoryId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $rootCategoryId);
            }
            $this->trees[$rootCategoryId][$cacheKey] = $category;
        }
        return $this->trees[$rootCategoryId][$cacheKey];
    }

    /**
     * @todo This is for initializing the safety requirement for UC3 optimization
     * @param null $categoryId
     * @param null $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface|mixed|null
     * @throws NoSuchEntityException
     */
    public function get($categoryId = null, $storeId = null)
    {
        $cacheKey = $storeId ?? 'all';
        if (!isset($this->instances[$categoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }
            $category->load($categoryId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $categoryId);
            }
            $this->instances[$categoryId][$cacheKey] = $category;
        }
        return $this->instances[$categoryId][$cacheKey];
    }

}
