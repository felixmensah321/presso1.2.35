<?php

namespace SQLi\Catalog\Api;

/**
 * Interface ProductRepositoryInterface
 * @package SQLi\Catalog\Api
 */
interface ProductRepositoryInterface
{
    /**
     * Get list of energetic labels
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    public function getEnergeticLabels();
}
