<?php

namespace CollinsHarper\Paysafe\Gateway\Response;

use CollinsHarper\Paysafe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractResponseHandler
{
    const ID = "id";
    const TRANSACTION_STATUS = "transaction_status";
    const TRANSACTION_CONFIRMATION_NUMBER = "transaction_confirmationNumber";
    const MERCHANT_REFERENCE_NUMBER = "merchantRefNum";
    const AUTH_CODE = "authCode";
    const AVAILABLE_TO_SETTLE = "availableToSettle";
    const AVAILABLE_TO_REFUND = "availableToRefund";
    const ORDER_ID = "orderId";
    const IS_VAULT_PAYMENT = 'is_vault_payment';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractResponseHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return \Magento\Payment\Model\InfoInterface
     */
    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }

    protected function handleAuthorizeResponse($payment, $response)
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response[self::ID]);
        $payment->setAdditionalInformation(self::TRANSACTION_STATUS, $response[self::TRANSACTION_STATUS]);
        $payment->setAdditionalInformation(self::TRANSACTION_CONFIRMATION_NUMBER, $response[self::TRANSACTION_CONFIRMATION_NUMBER]);
        $payment->setAdditionalInformation(self::MERCHANT_REFERENCE_NUMBER, $response[self::MERCHANT_REFERENCE_NUMBER]);

        return $payment;
    }
}