<?php

namespace CollinsHarper\Paysafe\Gateway\Response;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use CollinsHarper\Paysafe\Gateway\Helper\SubjectReader;
use CollinsHarper\Paysafe\Service\PaySafeService;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;

class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var CreditCardTokenFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PaySafeService
     */
    protected $service;

    /**
     * Constructor
     *
     * @param CreditCardTokenFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param PaySafeService $paySafeService
     */
    public function __construct(
        CreditCardTokenFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Config $config,
        SubjectReader $subjectReader,
        PaySafeService $paySafeService
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->service = $paySafeService;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $paymentTokens = $this->getVaultPaymentToken($response);

        if (!empty($paymentTokens)) {
            /** @var PaymentTokenInterface $paymentToken */
            foreach ($paymentTokens as $paymentToken) {
                $extensionAttributes = $this->getExtensionAttributes($payment);
                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    private function getVaultPaymentToken(array $transaction)
    {
        $profile = $this->service->getProfile($transaction['profile_id']);

        // Check token existing in gateway response
        if (!is_array($profile) && !array_key_exists('cards', $profile)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentTokens[] */
        $paymentTokens = [];

        foreach ($profile['cards'] as $card) {
            /** @var PaymentTokenInterface $paymentToken */
            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->setGatewayToken($card['paymentToken']);
            $paymentToken->setExpiresAt($this->getExpirationDate($card['cardExpiry']));
            $paymentToken->setIsVisible(true);

            $paymentToken->setTokenDetails($this->convertDetailsToJSON([
                'type' => $card['cardType'],
                'maskedCC' => $card['lastDigits'],
                'expirationDate' => $card['cardExpiry']['year'] . "/" . $card['cardExpiry']['month']
            ]));

            $paymentTokens[] = $paymentToken;
        }

        return $paymentTokens;
    }

    /**
     * @return string
     */
    private function getExpirationDate(array $cardExpiry)
    {
        $expDate = new \DateTime(
            $cardExpiry['year']
            . '-'
            . $cardExpiry['month']
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
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
}