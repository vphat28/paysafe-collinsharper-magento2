<?php

namespace CollinsHarper\Paysafe\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class CaptureResponseValidator extends AbstractValidator
{
    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        $validationKeys = [
            'amount',
            'authType',
            'confirmationNumber',
            'currencyCode',
            'id',
            'mode',
            'originalMerchantRefNum',
        ];

        foreach ($validationKeys as $key) {
            if (!array_key_exists($key, $response)) {
                return false;
            }
        }

        return true;
    }
}
