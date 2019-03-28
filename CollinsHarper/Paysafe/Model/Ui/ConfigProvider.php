<?php

namespace CollinsHarper\Paysafe\Model\Ui;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'chpaysafe';
    const CC_VAULT_CODE = 'chpaysafe_cc_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'active' => $this->config->isActive(),
                    'environment' => $this->config->getEnvironment(),
                    'payment_action' => $this->config->getPaymentAction(),
                    'title' => $this->config->getTitle(),
                    'enable_3dsecure' => $this->config->is3DSecureEnabled(),
                    'enable_silent_post' => $this->config->isSilentPost(),
                    'enable_interac' => $this->config->isInteracEnabled(),
                    'availableCardTypes' => $this->config->getAvailableCardTypes(),
                    'ccTypesMapper' => $this->config->getCctypesMapper(),
                    'countrySpecificCardTypes' => $this->config->getCountrySpecificCardTypeConfig(),
                    'allowspecific' => $this->config->getAllowSpecific(),
                    'vaultCode' => self::CC_VAULT_CODE,
                    'vault_enable' => $this->config->isVaultEnabled(),
                    'vault_address_editable' => $this->config->isVaultAddressEditable(),
                    'threat_metrix_enable' => $this->config->isThreatMetrixEnabled()
                ]
            ]
        ];
    }
}
