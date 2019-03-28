<?php

namespace CollinsHarper\Paysafe\Service;

use CollinsHarper\Paysafe\Gateway\Http\TransferFactory;
use CollinsHarper\Paysafe\Gateway\Http\Client\HTTPClient;

class PaySafeService extends HTTPClient
{
    const POST = 'POST';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const PUT = 'PUT';
    const ORDER_ENDPOINT = 'hosted/v1/orders';
    const VAULT_ENDPOINT = 'customervault/v1';
    const CARD_PAYMENTS_ENDPOINT = 'cardpayments/v1/accounts';

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * PaySafeService constructor.
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \CollinsHarper\Paysafe\Gateway\Config\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     * @param TransferFactory $transferFactory
     */
    public function __construct(
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \CollinsHarper\Paysafe\Gateway\Config\Config $config,
        \Psr\Log\LoggerInterface $logger,
        TransferFactory $transferFactory
    )
    {
        parent::__construct($httpClientFactory, $config, $logger);

        $this->transferFactory = $transferFactory;
    }

    public function placeOrder(array $request)
    {
        $request['method'] = self::POST;
        $request['uri'] = $this->config->buildUrl(self::ORDER_ENDPOINT);
        $transfer = $this->transferFactory->create($request);
        return $this->placeRequest($transfer);
    }

    public function getProfile($profileId)
    {
        $fields = [];
        $fields['fields'] = 'cards';
        $request['method'] = self::GET;
        $request['uri'] = $this->config->buildUrl(self::VAULT_ENDPOINT . "/profiles/" . $profileId, $fields);
        $transfer = $this->transferFactory->create($request);
        return $this->placeRequest($transfer);
    }

    public function placeSettlement(array $request)
    {
        $request['method'] = self::POST;
        $request['uri'] = $this->config->buildUrl(self::ORDER_ENDPOINT);
        $transfer = $this->transferFactory->create($request);
        return $this->placeRequest($transfer);
    }
}