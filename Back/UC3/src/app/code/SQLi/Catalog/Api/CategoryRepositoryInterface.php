<?php

namespace SQLi\Catalog\Api;


interface CategoryRepositoryInterface
{
    /**
     * Get info about category by category id
     *
     * @param int $categoryId
     * @param int $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($categoryId = null, $storeId = null);
    /**
     * Get list of categories
     *
     * @param int $categoryId
     * @param int $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList($rootCategoryId = null, $storeId = null);
}
