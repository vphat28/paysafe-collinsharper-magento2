<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Paysafe\Model\Adminhtml\Source;

/**
 * Class PaymentAction
 */
class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{

    const ACTION_AUTHORIZE = "authorize";
    const ACTION_AUTHORIZE_CAPTURE = "authorize_capture";

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTION_AUTHORIZE,
                'label' => __('Authorize')
            ],
            [
                'value' => self::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
