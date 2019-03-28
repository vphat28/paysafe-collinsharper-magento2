<?php
/**
 * Copyright © 2017 CollinsHarper. All rights reserved.
 * See accompanying License.txt for applicable terms of use and license.
 */

namespace CollinsHarper\Paysafe\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CaptureResponseHandler extends AbstractResponseHandler  implements HandlerInterface
{
    const CONFIRMATION_NUMBER = "confirmationNumber";
    const ORIGINAL_MERCHANT_REF_NUMBER = "originalMerchantRefNum";
    const AUTH_TYPE = "authType";

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response[self::ID]);
        $payment->setAdditionalInformation(self::CONFIRMATION_NUMBER, $response[self::CONFIRMATION_NUMBER]);
        $payment->setAdditionalInformation(self::ORIGINAL_MERCHANT_REF_NUMBER, $response[self::ORIGINAL_MERCHANT_REF_NUMBER]);
        $payment->setAdditionalInformation(self::AUTH_TYPE, $response[self::AUTH_TYPE]);

        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(false);
    }
}
