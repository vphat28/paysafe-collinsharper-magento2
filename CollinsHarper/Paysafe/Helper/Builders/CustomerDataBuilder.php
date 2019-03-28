<?php

namespace CollinsHarper\Paysafe\Helper\Builders;

use CollinsHarper\Paysafe\Controller\Hosted\PlaceOrder;
use CollinsHarper\Paysafe\Gateway\Config\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CustomerDataBuilder
 */
class CustomerDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        Config $config,
        DataObjectFactory $dataObjectFactory,
        SessionManagerInterface $checkoutSession,
        SessionManagerInterface $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($config, $dataObjectFactory, $checkoutSession);
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Node name in request
     */
    const CUSTOMER = 'profile';

    /**
     * API Properties
     */
    const ID = 'id';
    const PAYMENT_TOKEN = 'paymentToken';
    const MERCHANT_CUSTOMER_ID = 'merchantCustomerId';
    const FIRST_NAME = 'firstName';
    const LAST_NAME = 'lastName';
    const CUSTOMER_NOTIFICATION_EMAIL = 'customerNotificationEmail';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $isGuestCheckout = ($this->quote->getCustomerId() === null) ? true : false;
        $billingAddress = $this->quote->getBillingAddress();

        $data[self::CUSTOMER_NOTIFICATION_EMAIL] = $billingAddress->getEmail();

        if (!$isGuestCheckout) {
            $customerId = $this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $profileAttribute = $customer->getCustomAttribute(PlaceOrder::PAYSAFE_PROFILE_ID_CUSTOM_ATTRIBUTE);

            if ($profileAttribute !== null) {
                $data[self::CUSTOMER][self::ID] = $profileAttribute->getValue();
            } else {
                $data[self::CUSTOMER][self::MERCHANT_CUSTOMER_ID] = $this->config->buildMerchantCustomerId($this->quote->getId());
            }
        } else {
            $data[self::CUSTOMER][self::MERCHANT_CUSTOMER_ID] = 'guest-' . microtime();
        }

        $data[self::CUSTOMER][self::FIRST_NAME] = $billingAddress->getFirstname();
        $data[self::CUSTOMER][self::LAST_NAME] = $billingAddress->getLastname();

        return $data;
    }


}
