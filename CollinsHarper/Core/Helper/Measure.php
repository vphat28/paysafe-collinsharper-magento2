<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Helper;

use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\Helper\Context;


/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Measure extends \Magento\Framework\App\Helper\AbstractHelper
{


    const XML_PATH_DEFAULT_LENGTH = 'catalog/chcore/default_length';
    const XML_PATH_DEFAULT_WIDTH = 'catalog/chcore/default_width';
    const XML_PATH_DEFAULT_HEIGHT = 'catalog/chcore/default_height';
    const XML_PATH_DEFAULT_WEIGHT_UNIT = 'catalog/chcore/weight_unit';
    const XML_PATH_DEFAULT_MEASURE_UNIT = 'catalog/chcore/measure_unit';
    const XML_PATH_PRODUCT_SHIPPING_LENGTH = 'catalog/chcore/shipping_length';
    const XML_PATH_PRODUCT_SHIPPING_WIDTH = 'catalog/chcore/shipping_width';
    const XML_PATH_PRODUCT_SHIPPING_HEIGHT = 'catalog/chcore/shipping_height';
    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $objectManager;
    protected $carrierHelper;

    const CARRIER_HELPER_CLASS = '\Magento\Shipping\Helper\Carrier';

    /**
     * @param Context $context
     * @param \CollinsHarper\Core\Logger\Logger $chLogged
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    /*public function __construct(
    public function __construct(
        Context $context,
        \Magento\Shipping\Helper\Carrier $carrierHelper

    )
    {
        $this->_moduleManager = $context->getModuleManager();
        $this->_logger = $context->getLogger();
        $this->_carrierHelper = $carrierHelper;
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_httpHeader = $context->getHttpHeader();
        $this->_eventManager = $context->getEventManager();
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->_cacheConfig = $context->getCacheConfig();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlDecoder = $context->getUrlDecoder();
        $this->scopeConfig = $context->getScopeConfig();
    }
*/

    public function getCarrierHelper()
    {
        if(!$this->carrierHelper) {
            $this->carrierHelper = $this->objectManager->get(self::CARRIER_HELPER_CLASS);
        }
        return $this->carrierHelper;
    }
    /**
     * Retrieve converted Weight
     *
     * @param   float $value value to convert
     * @param   string $fromUnit From unit
     * @param   string $toUnit Destination unit
     * @return  float
     */

    public function getConvertedWeight($value, $fromUnit, $toUnit)
    {
        if ($value) {
            $unitWeight = new \Zend_Measure_Weight($value, $fromUnit);
            $unitWeight->setType($toUnit);
            return $unitWeight->getValue();
        }
        return null;
    }

    /**
     * Retrieve converted Weight
     *
     * @param   float $value value to convert
     * @param   string $fromUnit From unit
     * @param   string $toUnit Destination unit
     * @return  float
     */

    public function getConvertedLength($value, $fromUnit, $toUnit)
    {

        if ($value) {
            $unitDimension = new \Zend_Measure_Length($value, $fromUnit);
            $unitDimension->setType($toUnit);
            return $unitDimension->getValue();
        }
        return null;

    }


}

