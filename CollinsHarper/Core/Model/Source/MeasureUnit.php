<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Model\Source;

/**
 * Source model for Measure Unit Type
 */
class MeasureUnit implements \Magento\Framework\Option\ArrayInterface
{

    const METER = \Zend_Measure_Length::METER;
    const CMETER = \Zend_Measure_Length::CENTIMETER;
    const MMETER = \Zend_Measure_Length::MILLIMETER;
    const FEET = \Zend_Measure_Length::FEET;
    const INCH = \Zend_Measure_Length::INCH;
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Meter'), 'value' => self::METER],
            ['label' => __('Centimeter'), 'value' => self::CMETER],
            ['label' => __('Milimeter'), 'value' => self::MMETER],
            ['label' => __('Feet'), 'value' => self::FEET],
            ['label' => __('Inch'), 'value' => self::INCH]

        ];
    }
}
