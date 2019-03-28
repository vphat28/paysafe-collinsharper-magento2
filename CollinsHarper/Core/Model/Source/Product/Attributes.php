<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Model\Source\Product;

/**
 * Source model for Product Attributes List
 */
class Attributes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param array $data
     */

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
//        parent::__construct($collectionFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $collection = $this->_collectionFactory->create()->addVisibleFilter();
        // TODO sort by label
        $array = array([  'label' =>  __('Not Selected'), 'value' => '' ]);
        foreach($collection as $attribute) {
            $array[] = [
                'label' => $attribute->getFrontendLabel(), 'value' => $attribute->getAttributeCode()
            ];
        }
        return $array;
    }
}
