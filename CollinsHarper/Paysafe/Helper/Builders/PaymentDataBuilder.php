<?php

namespace CollinsHarper\Paysafe\Helper\Builders;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use CollinsHarper\Paysafe\Model\Adminhtml\Source\Environment;
use CollinsHarper\Paysafe\Model\Adminhtml\Source\PaymentAction;
use CollinsHarper\Paysafe\Model\Ui\ConfigProvider;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Payment Data Builder
 */
class PaymentDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * API Properties
     */
    const MERCHANT_REF_NUM = 'merchantRefNum';
    const AMOUNT = 'totalAmount';
    const CURRENCY_CODE = 'currencyCode';
    const CARD_NUM = 'cardNum';
    const CARD_EXPIRY_MONTH = 'cardExpiryMonth';
    const CARD_EXPIRY_YEAR = 'cardExpiryYear';
    const PAYMENT_TOKEN = 'paymentToken';
    const SHOPPING_CART = 'shoppingCart';
    const ANCILLARY_FEES = 'ancillaryFees';
    const REDIRECT = 'redirect';
    const EXTENDED_OPTIONS = 'extendedOptions';
    const CALLBACK_URI = ConfigProvider::CODE . '/hosted/placeOrder';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * PaymentDataBuilder constructor.
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param DataObjectFactory $dataObjectFactory
     * @param SessionManagerInterface $checkoutSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config,
        DataObjectFactory $dataObjectFactory,
        SessionManagerInterface $checkoutSession
    ) {
        parent::__construct($config, $dataObjectFactory, $checkoutSession);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $shoppingCartArray  = array();

        // Order extended options
        $extendedOptionsArray = array();

        // Minimum order information needed
        $data = array(
            self::AMOUNT => $this->formatAmount($this->quote->getBaseGrandTotal()),
            self::CURRENCY_CODE => $this->quote->getBaseCurrencyCode(),
            self::MERCHANT_REF_NUM => (string) $this->quote->getReservedOrderId() . time()
        );

        if ($this->config->getEnvironment() === Environment::PRODUCTION) {
            $data['customerIp'] = $this->quote->getRemoteIp();
        }

        $isInteracEnabled = $this->config->isInteracEnabled();
        $customerChooseInterac = $buildSubject['customerChooseInterac'];

        if ($isInteracEnabled && $customerChooseInterac === "true") {
            $data['paymentMethod'] = ['interac'];
        }

        if (strlen($this->config->getMerchantEmail()) > 0) {
            $data['merchantNotificationEmail'] = $this->config->getMerchantEmail();
        }

        $transactionMode = $this->config->getPaymentAction();

        if ($transactionMode == PaymentAction::ACTION_AUTHORIZE) {
            $transactionType = 'auth';
        } else {
            $transactionType = 'purchase';
        }

        $extendedOptionsArray[] = array(
            'key' => 'authType',
            'value' => $transactionType
        );

        // Calculate quote totals
        $this->quote->collectTotals();

        if ($this->quote->isVirtual()) {
            $baseTaxAmount = $this->quote->getBillingAddress()->getBaseTaxAmount();
            $baseDiscountAmount = $this->quote->getBillingAddress()->getBaseDiscountAmount();
            $customerBalanceAmount = $this->quote->getBillingAddress()->getCustbalanceAmount() * -1;
        } else {
            $baseTaxAmount = $this->quote->getShippingAddress()->getBaseTaxAmount();
            $baseDiscountAmount = $this->quote->getShippingAddress()->getBaseDiscountAmount();
            $customerBalanceAmount = $this->quote->getShippingAddress()->getCustbalanceAmount() * -1;
        }

        // Ancillary fees information
        $ancillaryFeesArray = array(
            array(
                'amount' => (int)$this->formatAmount($this->quote->getShippingAddress()->getBaseShippingAmount()),
                'description' => "Shipping Amount"
            ),
            array(
                'amount' => (int)$this->formatAmount($baseTaxAmount),
                'description' => "Tax Amount"
            ),
            array(
                'amount' => (int)$this->formatAmount($baseDiscountAmount),
                'description' => "Discount Amount"
            ),
            array(
                'amount' => (int)$this->formatAmount($customerBalanceAmount),
                'description' => "Store Credit Amount"
            )
        );

        $giftCardsAmount = $this->quote->getBaseGiftCardsAmountUsed();

        $pointsData = $this->checkoutSession->getData('reward_sales_rules');
        if ($pointsData && isset($pointsData['base_discount'])) {
            $giftCardsAmount += $pointsData['base_discount'];
        }

        if (!empty($giftCardsAmount)) {
            $ancillaryFeesArray[] = array(
                'amount' => (-100 * $giftCardsAmount),
                'description' => "Gift Cards Amount"
            );
        }

        foreach ($this->quote->getAllVisibleItems() as $item) {
            $itemArray = array(
                'amount' => $this->formatAmount($item->getBasePrice()),
                'quantity' => $item->getQty(),
                'sku' => $item->getSku(),
                'description' => substr($item->getName(), 0, 45)
            );

            $shoppingCartArray[] = $itemArray;
        }

        // Add extra information to the order Data
        $data[self::SHOPPING_CART] = $shoppingCartArray;
        $data[self::ANCILLARY_FEES] = $ancillaryFeesArray;
        $data[self::REDIRECT] = $this->buildRedirectData();
        $data[self::EXTENDED_OPTIONS] = $extendedOptionsArray;

        return $data;
    }

    public function formatAmount($amount)
    {
        $amount = (int)(string)((number_format($amount, 2, '.', '')) * 100);
        return $amount;
    }

    private function getCallBackUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . self::CALLBACK_URI;
    }

    private function buildRedirectData()
    {
        $returnKeys = array(
            'id',
            'transaction.confirmationNumber',
            'transaction.status',
            'transaction.errorCode',
            'transaction.errorMessage',
            'profile.id',
            'profile.paymentToken'
        );

        if ($this->config->is3DSecureEnabled()) {
            array_push($returnKeys, 'transaction.riskReasonCode');
        }

        $statuses = array('on_success','on_error','on_decline','on_timeout','on_hold');

        $redirectArray = [];
        foreach ($statuses as $status) {
            $redirectArray[] = array(
                'rel' => $status,
                'returnKeys' => $returnKeys,
                'uri' =>  $this->getCallBackUrl()
            );
        }

        return $redirectArray;
    }
}
