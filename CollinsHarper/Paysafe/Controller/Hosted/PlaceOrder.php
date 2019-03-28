<?php

namespace CollinsHarper\Paysafe\Controller\Hosted;

use CollinsHarper\Paysafe\Model\Ui\ConfigProvider;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{
    const PAYSAFE_PROFILE_ID_CUSTOM_ATTRIBUTE = 'chpaysafe_profile_id';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteManagement $quoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerFactory
     */
    private $customerResourceFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $orderSender;

    /**
     * PlaceOrder constructor.
     * @param Context $context
     * @param SessionManagerInterface $checkoutSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $checkoutSession,
        SessionManagerInterface $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->quoteManagement = $quoteManagement;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->orderSender = $orderSender;
    }

    public function execute()
    {
        $responseParams = $this->_request->getParams();

        if ($responseParams['transaction_status'] !== 'success') {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $errorCode = $responseParams['transaction_errorCode'];

            if (
                array_key_exists('transaction_riskReasonCode', $responseParams) &&
                ($responseParams['transaction_riskReasonCode'] !== null ||
                $responseParams['transaction_riskReasonCode'] !== '')
            ) {
                $errorCode = $responseParams['transaction_riskReasonCode'];
            }

            $this->messageManager->addErrorMessage("Unable to process order on gateway - errorCode: " . $errorCode);
            return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }

        $responseParams['merchantRefNum'] = $this->checkoutSession->getMerchantReferenceNumber();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        /**
         * Handle customer guest
         */
        //if ($quote->getCustomerEmail() === null) {
            $quote = $this->prepareGuestQuote($quote);
        //} else {
        //    $this->saveCustomerPaySafeProfileId($responseParams['profile_id']);
        //}

        $quote->setPaymentMethod(ConfigProvider::CODE);
        $quote->setInventoryProcessed(false);

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);
        $quote->getPayment()->setAdditionalInformation($responseParams);

        $quote->collectTotals();

        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->clearHelperData();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {

            $order = $this->quoteManagement->submit($quote);

            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());
            $this->checkoutSession->replaceQuote($quote);

            $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
            $this->orderSender->send($order, true);

            $successValidator = $this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator');

            if (!$successValidator->isValid()) {
                return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
            }

            $this->messageManager->addSuccessMessage('Your order has been successfully created!');
            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }

    private function saveCustomerPaySafeProfileId($profileId)
    {
        $customerId = $this->customerSession->getCustomerId();

        /** @var \Magento\Customer\Model\CustomerFactory $customerFactory */
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create();

        /** @var \Magento\Customer\Model\Data\Customer $customerData */
        $customerData = $customer->getDataModel();
        $customerData->setId($customerId);

        $customerData->setCustomAttribute(self::PAYSAFE_PROFILE_ID_CUSTOM_ATTRIBUTE, $profileId);

        $customer->updateData($customerData);

        /** @var \Magento\Customer\Model\ResourceModel\Customer $customerResource */
        /** @var \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory */
        $customerResource = $this->customerResourceFactory->create();

        try {
            if ($profileId != "") {
                $customerResource->saveAttribute($customer, self::PAYSAFE_PROFILE_ID_CUSTOM_ATTRIBUTE);
            }
        } catch (\Exception $e) {
            $this->messageManager->addWarningMessage("Unable to save customer paysafe profile id");
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    private function prepareGuestQuote(\Magento\Quote\Model\Quote $quote)
    {
        $quote->setCustomerId(null);
        $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        return $quote;
    }
}
