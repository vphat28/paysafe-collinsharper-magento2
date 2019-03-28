<?php
/**
* Copyright © 2017 CollinsHarper. All rights reserved.
* See accompanying License.txt for applicable terms of use and license.
*/

namespace CollinsHarper\Paysafe\Gateway\Request;

use CollinsHarper\Paysafe\Gateway\Response\AbstractResponseHandler;
use CollinsHarper\Paysafe\Service\PaySafeService;
use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundRequest extends AbstractRequest implements BuilderInterface
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
        $merchantRefNum = $payment->getAdditionalInformation('merchantRefNum');
        $orderId = $payment->getAdditionalInformation('id');

        $requestData = [
            'merchantRefNum' => $merchantRefNum,
            'amount' => $amount = (int)(string)((number_format($buildSubject['amount'], 2, '.', '')) * 100)
        ];

        $requestData['method'] = PaySafeService::POST;
        $requestData['uri'] = $this->config->buildUrl(
            PaySafeService::ORDER_ENDPOINT . "/" . $orderId . "/refund"
        );

        return $requestData;
    }
}
