<?php

namespace CollinsHarper\Paysafe\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class VaultSettlementResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    const ID = "id";
    const TRANSACTION_STATUS = "status";

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

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response[self::ID]);
        $payment->setAdditionalInformation(self::ORDER_ID, $response[self::ID]);
        $payment->setAdditionalInformation(self::IS_VAULT_PAYMENT, true);
        $payment->setAdditionalInformation(self::TRANSACTION_STATUS, $response[self::TRANSACTION_STATUS]);
        $payment->setAdditionalInformation(self::AUTH_CODE, $response[self::AUTH_CODE]);
        $payment->setAdditionalInformation(self::AVAILABLE_TO_SETTLE, $response[self::AVAILABLE_TO_SETTLE]);
        $payment->setAdditionalInformation(self::MERCHANT_REFERENCE_NUMBER, $response[self::MERCHANT_REFERENCE_NUMBER]);

        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
