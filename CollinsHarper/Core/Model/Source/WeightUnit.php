<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Model\Source;

/**
 * Source model for Weight Unit Type
 */
class WeightUnit implements \Magento\Framework\Option\ArrayInterface
{

    const KG = \Zend_Measure_Weight::KILOGRAM;
    const GRAM = \Zend_Measure_Weight::GRAM;
    const LB = \Zend_Measure_Weight::LBS;
    const OZ = \Zend_Measure_Weight::OUNCE;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('KG'), 'value' => self::KG],
            ['label' => __('Gram'), 'value' => self::GRAM],
            ['label' => __('LB'), 'value' => self::LB],
            ['label' => __('Ounce'), 'value' => self::OZ]

        ];
    }
}
