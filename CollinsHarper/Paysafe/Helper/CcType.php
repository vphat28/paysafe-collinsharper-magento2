<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Paysafe\Helper;

use CollinsHarper\Paysafe\Model\Adminhtml\Source\CcType as CcTypeSource;

/**
 * Class CcType
 */
class CcType
{
    /**
     * All possible credit card types
     *
     * @var array
     */
    private $ccTypes = [];

    /**
     * @var \CollinsHarper\Paysafe\Model\Adminhtml\Source\CcType
     */
    private $ccTypeSource;

    /**
     * @param CcType $ccTypeSource
     */
    public function __construct(CcTypeSource $ccTypeSource)
    {
        $this->ccTypeSource = $ccTypeSource;
    }

    /**
     * All possible credit card types
     *
     * @return array
     */
    public function getCcTypes()
    {
        if (!$this->ccTypes) {
            $this->ccTypes = $this->ccTypeSource->toOptionArray();
        }
        return $this->ccTypes;
    }
}
