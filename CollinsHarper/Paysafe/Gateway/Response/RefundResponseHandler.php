<?php
/**
* Copyright © 2017 CollinsHarper. All rights reserved.
* See accompanying License.txt for applicable terms of use and license.
*/

namespace CollinsHarper\Paysafe\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class RefundResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    const CONFIRMATION_NUMBER = 'confirmationNumber';
    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $payment->setTransactionId($response[self::ID]);
        $payment->setAdditionalInformation(self::CONFIRMATION_NUMBER, $response[self::CONFIRMATION_NUMBER]);

        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);
        $payment->setIsTransactionPending(false);
    }
}
