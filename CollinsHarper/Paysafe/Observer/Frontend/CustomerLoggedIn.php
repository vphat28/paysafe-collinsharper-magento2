<?php

namespace CollinsHarper\Paysafe\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLoggedIn implements ObserverInterface {
    
    const PAYSAFE_PROFILE_ID_CUSTOM_ATTRIBUTE = 'chpaysafe_profile_id';
    protected $orderCollectionFactory;
    protected $tokenCollectionFactory;
    protected $ResourceConnection;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerFactory
     */
    private $customerResourceFactory;
    
    public function __construct(
    \Magento\Framework\App\ResourceConnection $ResourceConnection, 
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, 
            \Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory $tokenCollectionFactory, 
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->ResourceConnection = $ResourceConnection;
        $this->customerFactory = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;
    }

    public function execute(Observer $observer) {
        $customer = $observer->getData('customer');
        $storeId = $customer->getStoreId();
        $email = $customer->getEmail();
        $custId = $customer->getId();
        $orders = $this->orderCollectionFactory->create()->addFieldToSelect(
                                'entity_id'
                        )->addFieldToFilter(
                                'customer_email', $email
                        )->addFieldToFilter(
                                'customer_id', array('null' => true)
                        )
                        ->setOrder(
                                'created_at', 'desc'
                        )->setPageSize(1);

        $ordersData = $orders->getData();
        $connection = $this->ResourceConnection->getConnection();
        $tableName = $this->ResourceConnection->getTableName('vault_payment_token_order_payment_link');
        $tokenTableName = $this->ResourceConnection->getTableName('vault_payment_token');
        $orderPaymentName = $this->ResourceConnection->getTableName('sales_order_payment');
        if (!empty($ordersData)) {
            foreach ($ordersData as $orderData) {
                //Select Data from table

                $orderpaymentsql = "Select `additional_information` FROM $orderPaymentName where `parent_id` = '" . $orderData['entity_id'] . "' ";
                $paymentResult = $connection->fetchAll($orderpaymentsql); // gives associated array, table fields as key in array.

                $unselData = unserialize($paymentResult[0]['additional_information']);
                
                $this->saveCustomerPaySafeProfileId($unselData['profile_id'],$custId);

            }
        }
    }

    public function saveCustomerPaySafeProfileId($profileId,$customerId) {
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
            
        }
    }

}
