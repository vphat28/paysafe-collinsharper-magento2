<?php
/**
 * Copyright © 2017 CollinsHarper. All rights reserved.
 * See accompanying License.txt for applicable terms of use and license.
 */

namespace CollinsHarper\Paysafe\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;

class SaleResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $this->getValidPaymentInstance($handlingSubject);
        $payment = $this->handleAuthorizeResponse($payment, $response);

        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
