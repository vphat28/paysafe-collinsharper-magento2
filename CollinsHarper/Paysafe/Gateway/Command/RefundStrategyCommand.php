<?php

namespace CollinsHarper\Paysafe\Gateway\Command;

use CollinsHarper\Paysafe\Gateway\Response\VaultCaptureResponseHandler;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use CollinsHarper\Paysafe\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Class CaptureStrategyCommand
 * @SuppressWarnings(PHPMD)
 */
class RefundStrategyCommand implements CommandInterface
{
    const REFUND_HOSTED = 'refund_hosted';
    const REFUND_VAULT = 'vault_refund';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CaptureStrategyCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);

        /**
         * Try to refund vault order, if order is not refundable, perform a cancel operation
         */
        try {
            $this->commandPool->get($command)->execute($commandSubject);
        } catch (\Exception $e) {
            $this->commandPool->get('cancel')->execute($commandSubject);
        }
    }

    /**
     * Get execution command name
     * @param OrderPaymentInterface $payment
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment)
    {
        $isVaultPayment = $payment->getAdditionalInformation(VaultCaptureResponseHandler::IS_VAULT_PAYMENT);

        if ($isVaultPayment !== null && $isVaultPayment) {
            return self::REFUND_VAULT;
        }

        return self::REFUND_HOSTED;
    }
}
