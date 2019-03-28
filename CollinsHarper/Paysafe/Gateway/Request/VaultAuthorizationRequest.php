<?php

namespace CollinsHarper\Paysafe\Gateway\Request;

use CollinsHarper\Paysafe\Model\Adminhtml\Source\PaymentAction;
use CollinsHarper\Paysafe\Service\PaySafeService;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultAuthorizationRequest extends AbstractRequest implements BuilderInterface
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

        /** @var \Magento\Vault\Model\PaymentToken $vaultPaymentToken */
        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();

        $amount = (int)(string)((number_format($buildSubject['amount'], 2, '.', '')) * 100);

        if ($vaultPaymentToken !== null && !$vaultPaymentToken->isEmpty()) {
            $requestData = [
                "merchantRefNum" => $this->config->buildMerchantCustomerId($payment->getOrder()->getQuoteId()),
                "amount" => $amount,
                "settleWithAuth" => ($this->config->getPaymentAction() === PaymentAction::ACTION_AUTHORIZE_CAPTURE) ? "true" : "false",
                "card" => [
                    "paymentToken" => $vaultPaymentToken->getGatewayToken()
                ]
            ];
        }

        $requestData['method'] = PaySafeService::POST;
        $requestData['uri'] = $this->config->buildUrl(
            PaySafeService::CARD_PAYMENTS_ENDPOINT . "/" . $this->config->getAccountId() . "/auths"
        );

        return $requestData;
    }
}
