<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Test\Unit\Helper;


use Magento\Framework\Xml\Security;
//use Collinsharper\Core\Helper;

class MeasureTest extends \PHPUnit_Framework_TestCase
{

    const LBS_PER_KG = 2.204;
    const FT_PER_MTR = 3.281;
    /**
     * CollinsHarper\Core\Helper\Measure helper
     *
     * @var \CollinsHarper\Core\Helper\Measure
     */
    protected $helper;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject('CollinsHarper\Core\Helper\Measure');
    }


    public function testWeightConvert()
    {
         $this->assertEquals(self::LBS_PER_KG, round($this->helper->getConvertedWeight(1, \Zend_Measure_Weight::KILOGRAM, \Zend_Measure_Weight::LBS), 3), '', 0.001);
    }

    public function testDimConvert()
    {
         $this->assertEquals(self::FT_PER_MTR, round($this->helper->getConvertedLength(1, \Zend_Measure_Length::METER, \Zend_Measure_Length::FEET), 3), '', 0.001);
    }
}
