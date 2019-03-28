<?php
/**
* Copyright © 2017 CollinsHarper. All rights reserved.
* See accompanying License.txt for applicable terms of use and license.
*/

namespace CollinsHarper\Paysafe\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class AuthorizeResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);
        $payment = $this->handleAuthorizeResponse($payment, $response);

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
