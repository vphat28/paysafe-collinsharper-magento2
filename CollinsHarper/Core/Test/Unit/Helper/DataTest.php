<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Test\Unit\Helper;


use Magento\Framework\Xml\Security;
//use Collinsharper\Core\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Ups config helper
     *
     * @var \Magento\Ups\Helper\Config
     */
    protected $helper;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject('CollinsHarper\Core\Helper\Data');
    }


    // TODO run through a conversion for each
    public function testValidLicenses()
    {
         $this->assertEquals(true, $this->helper->checkLicenses());

    }
}
