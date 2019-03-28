<?php
/**
 * Copyright © 2017 CollinsHarper. All rights reserved.
 * See accompanying License.txt for applicable terms of use and license.
 */

namespace CollinsHarper\Paysafe\Gateway\Request;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use CollinsHarper\Paysafe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractRequest
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }
}