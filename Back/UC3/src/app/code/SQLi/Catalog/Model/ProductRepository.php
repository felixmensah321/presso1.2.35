<?php

namespace SQLi\Catalog\Model;

use SQLi\Catalog\Api\ProductRepositoryInterface;

/**
 * Class ProductRepository
 * @package SQLi\Catalog\Model
 */
class ProductRepository implements ProductRepositoryInterface
{

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $eavOptionManagement;

    /**
     * ProductRepository constructor.
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $eavOptionManagement
     */
    public function __construct(
        \Magento\Eav\Api\AttributeOptionManagementInterface $eavOptionManagement
    ) {
        $this->eavOptionManagement = $eavOptionManagement;
    }

    /**
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    public function getEnergeticLabels()
    {
        return $this->eavOptionManagement->getItems(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            'energetic_label'
        );;
    }

}
