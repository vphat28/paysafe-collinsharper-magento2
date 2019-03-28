<?php

namespace CollinsHarper\Paysafe\Controller\Hosted;

use CollinsHarper\Paysafe\Helper\PaySafeOrderBuilderComposite;
use CollinsHarper\Paysafe\Service\PaySafeService;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Redirect
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var PaySafeOrderBuilderComposite
     */
    private $paySafeOrderBuilderComposite;

    /**
     * @var PaySafeService
     */
    private $paySafeService;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param SessionManagerInterface $checkoutSession
     * @param PaySafeOrderBuilderComposite $paySafeOrderBuilderComposite
     * @param PaySafeService $paySafeService
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $checkoutSession,
        PaySafeOrderBuilderComposite $paySafeOrderBuilderComposite,
        PaySafeService $paySafeService,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->paySafeOrderBuilderComposite = $paySafeOrderBuilderComposite;
        $this->paySafeService = $paySafeService;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $customerChooseInterac = $this->_request->getParam('customerChooseInterac');

        $buildSubject = [
            'customerChooseInterac' => $customerChooseInterac
        ];

        try {
            $request = $this->paySafeOrderBuilderComposite->build($buildSubject);
            $this->checkoutSession->setMerchantReferenceNumber($request['merchantRefNum']);
            $response = $this->paySafeService->placeOrder($request);
            $result = $this->resultJsonFactory->create();
            $result->setData($response);
            return $result;
        } catch (\Exception $e) {
            $this->checkoutSession->unsMerchantReferenceNumber();
            $responseError = [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
            $result = $this->resultJsonFactory->create();
            $result->setData($responseError);
            $result->setHttpResponseCode(400);
            return $result;
        }
    }
}
