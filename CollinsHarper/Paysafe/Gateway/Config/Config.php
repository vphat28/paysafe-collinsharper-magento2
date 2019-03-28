<?php

namespace CollinsHarper\Paysafe\Gateway\Config;

use CollinsHarper\Paysafe\Model\Adminhtml\Source\Environment;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_ENVIRONMENT = 'environment';
    const KEY_PAYMENT_ACTION = 'payment_action';
    const KEY_API_USERNAME = 'username';
    const KEY_API_PASSWORD = 'password';
    const KEY_TITLE = 'title';
    const KEY_DEBUG = 'debug';
    const KEY_EMAIL_CUSTOMER = 'email_customer';
    const KEY_MERCHANT_EMAIL = 'merchant_email';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_ENABLE_3D_SECURE = 'enable_3dsecure';
    const KEY_ENABLE_INTERAC = 'enable_interac';
    const KEY_ALLOW_SPECIFIC = 'allowspecific';
    const KEY_VAULT_ENABLE = 'active';
    const KEY_VAULT_ADDRESS_EDITABLE = 'vault_address_editable';
    const KEY_THREAT_METRIX_ENABLE = 'threat_metrix_enable';
    const KEY_ENABLE_SILENT_POST = 'enable_silent_post';
    const KEY_CC_TYPES_PAYSAFE_MAPPER = 'cctypes_paysafe_mapper';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_ACCOUNT_ID = 'account_id';

    const PAYSAFE_API_URL = 'https://api.netbanx.com';
    const PAYSAFE_API_URL_TEST = 'https://api.test.netbanx.com';

    public function isActive()
    {
        return $this->getValue(self::KEY_ACTIVE);
    }

    public function getEnvironment()
    {
        return $this->getValue(self::KEY_ENVIRONMENT);
    }

    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    public function getApiUsername()
    {
        return $this->getValue(self::KEY_API_USERNAME);
    }

    public function getApiPassword()
    {
        return $this->getValue(self::KEY_API_PASSWORD);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    public function getDebug()
    {
        return $this->getValue(self::KEY_DEBUG);
    }

    public function getEmailCustomer()
    {
        return $this->getValue(self::KEY_EMAIL_CUSTOMER);
    }

    public function getMerchantEmail()
    {
        return $this->getValue(self::KEY_MERCHANT_EMAIL);
    }

    /**
     * Return the country specific card type config
     *
     * @return array
     */
    public function getCountrySpecificCardTypeConfig()
    {
        $countriesCardTypes = unserialize($this->getValue(self::KEY_COUNTRY_CREDIT_CARD));

        return is_array($countriesCardTypes) ? $countriesCardTypes : [];
    }

    public function getAvailableCardTypes()
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Braintree card types
     *
     * @return array
     */
    public function getCcTypesMapper()
    {
        $result = json_decode(
            $this->getValue(self::KEY_CC_TYPES_PAYSAFE_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }


    public function is3DSecureEnabled()
    {
        return (bool) $this->getValue(self::KEY_ENABLE_3D_SECURE);
    }

    public function isInteracEnabled()
    {
        return (bool) $this->getValue(self::KEY_ENABLE_INTERAC);
    }

    public function getAllowSpecific()
    {
        return $this->getValue(self::KEY_ALLOW_SPECIFIC);
    }

    public function isVaultEnabled()
    {
        $this->setMethodCode('chpaysafe_cc_vault');
        $isVaultEnable = (bool) $this->getValue(self::KEY_VAULT_ENABLE);
        $this->setMethodCode('chpaysafe');
        return $isVaultEnable;
    }

    public function isVaultAddressEditable()
    {
        return (bool) $this->getValue(self::KEY_VAULT_ADDRESS_EDITABLE);
    }

    public function isThreatMetrixEnabled()
    {
        return (bool) $this->getValue(self::KEY_THREAT_METRIX_ENABLE);
    }

    public function isSilentPost()
    {
        return (bool) $this->getValue(self::KEY_ENABLE_SILENT_POST);
    }

    public function getAccountId()
    {
        return $this->getValue(self::KEY_ACCOUNT_ID);
    }

    public function getPaySafeApiBaseUrl()
    {
        return ($this->getEnvironment() == Environment::TEST) ? self::PAYSAFE_API_URL_TEST : self::PAYSAFE_API_URL;
    }

    public function buildUrl($uri, $queryString = null)
    {
        return $this->getPaySafeApiBaseUrl() . '/' . $uri . ($queryString ? '?' . http_build_query($queryString) : '');
    }

    public function buildMerchantCustomerId($quoteId)
    {
        return md5($this->getApiPassword() . $quoteId . microtime());
    }
}
