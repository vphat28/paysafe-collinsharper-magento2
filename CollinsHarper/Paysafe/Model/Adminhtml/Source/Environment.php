<?php

namespace CollinsHarper\Paysafe\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Environment
 */
class Environment implements ArrayInterface
{
    const TEST = "test";
    const PRODUCTION = "live";

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TEST,
                'label' => 'Sandbox',
            ],
            [
                'value' => self::PRODUCTION,
                'label' => 'Production'
            ]
        ];
    }
}
