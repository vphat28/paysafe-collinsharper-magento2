<?php

namespace CollinsHarper\Paysafe\Helper;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use CollinsHarper\Paysafe\Helper\Builders\AddressDataBuilder;
use CollinsHarper\Paysafe\Helper\Builders\CustomerDataBuilder;
use CollinsHarper\Paysafe\Helper\Builders\PaymentDataBuilder;
use Magento\Checkout\Model\Session;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Gateway\Request\BuilderComposite;

/**
 * Payment Data Builder
 */
class PaySafeOrderBuilderComposite extends BuilderComposite
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var locale
     */
    protected $locale;

    /**
     * PaySafeOrderBuilderComposite constructor.
     * @param TMapFactory $tMapFactory
     * @param Config $config
     * @param SessionManagerInterface $checkoutSession
     */
    public function __construct(
        TMapFactory $tMapFactory,
        Config $config,
        SessionManagerInterface $checkoutSession,
        \Magento\Framework\Locale\Resolver $locale
    ) {
        parent::__construct($tMapFactory, [
            CustomerDataBuilder::class,
            AddressDataBuilder::class,
            PaymentDataBuilder::class
        ]);

        $this->locale = $locale;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $requestBuilt = parent::build($buildSubject);

        $is3DSecureEnabled = $this->config->is3DSecureEnabled();
        $shouldSkip3D = ($is3DSecureEnabled) ? "false" : "true";
        $extendedOptions[] = array(
            'key' => "skip3D",
            'value' => $shouldSkip3D
        );

        $isSilentPost = ($this->config->isSilentPost()) ? "true" : "false";
        $extendedOptions[] = array(
            'key' => 'silentPost',
            'value' => $isSilentPost
        );

        $disablePaymentMethodStorage = ($this->config->isVaultEnabled()) ? "false" : "true";

        $extendedOptions[] = array(
            'key' => 'disablePaymentMethodStorage',
            'value' => $disablePaymentMethodStorage
        );

        $suppressCustomerEmail = ($this->config->getEmailCustomer() ? "false" : "true");

        $extendedOptions[] = array(
            'key' => 'suppressCustomerEmail',
            'value' => $suppressCustomerEmail
        );

        // Do we have French website?
        $requestBuilt['locale'] = $this->locale->getLocale();;

        $requestBuilt['extendedOptions'] = array_merge($requestBuilt['extendedOptions'], $extendedOptions);
        return $requestBuilt;
    }
}

