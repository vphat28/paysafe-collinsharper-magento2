<?php
/**
* Copyright © 2017 CollinsHarper. All rights reserved.
* See accompanying License.txt for applicable terms of use and license.
*/

namespace CollinsHarper\Paysafe\Gateway\Request;

use CollinsHarper\Paysafe\Gateway\Response\AbstractResponseHandler;
use CollinsHarper\Paysafe\Service\PaySafeService;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultRefundRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);
        $orderId = $payment->getAdditionalInformation(AbstractResponseHandler::ORDER_ID);
        $accountId = $this->config->getAccountId();
        $merchantRefNumber = $payment->getAdditionalInformation(AbstractResponseHandler::MERCHANT_REFERENCE_NUMBER);

        $requestData = [
            'merchantRefNum' => $merchantRefNumber,
            'amount' => $amount = (int)(string)((number_format($buildSubject['amount'], 2, '.', '')) * 100)
        ];

        $requestData['method'] = PaySafeService::POST;
        $requestData['uri'] = $this->config->buildUrl(
            PaySafeService::CARD_PAYMENTS_ENDPOINT . "/" . $accountId . "/settlements/" . $orderId . "/refunds"
        );

        return $requestData;
    }
}
