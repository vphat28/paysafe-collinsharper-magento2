<?php
/**
* Copyright © 2017 CollinsHarper. All rights reserved.
* See accompanying License.txt for applicable terms of use and license.
*/

namespace CollinsHarper\Paysafe\Gateway\Request;

use CollinsHarper\Paysafe\Gateway\Response\AbstractResponseHandler;
use CollinsHarper\Paysafe\Gateway\Response\VaultCaptureResponseHandler;
use CollinsHarper\Paysafe\Service\PaySafeService;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CancelRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);

        $isVaultPayment = $payment->getAdditionalInformation(AbstractResponseHandler::IS_VAULT_PAYMENT);

        /**
         * Cancel a vault payment
         */
        if ($isVaultPayment) {

            $orderId = $payment->getAdditionalInformation(AbstractResponseHandler::ORDER_ID);
            $accountId = $this->config->getAccountId();

            $requestData = [
                'status' => 'CANCELLED'
            ];

            $requestData['method'] = PaySafeService::PUT;
            $requestData['uri'] = $this->config->buildUrl(
                PaySafeService::CARD_PAYMENTS_ENDPOINT . "/" . $accountId . "/settlements/" . $orderId
            );
        } else {
            /**
             * Cancel a hosted payment
             */
            $orderId = $payment->getAdditionalInformation('id');
            $requestData['method'] = PaySafeService::DELETE;
            $requestData['uri'] = $this->config->buildUrl(
                PaySafeService::ORDER_ENDPOINT . "/" . $orderId
            );
        }

        return $requestData;
    }
}
