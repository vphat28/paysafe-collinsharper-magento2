<?php

namespace CollinsHarper\Paysafe\Helper\Builders;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote;

abstract class AbstractDataBuilder
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * AbstractDataBuilder constructor.
     * @param Config $config
     * @param DataObjectFactory $dataObjectFactory
     * @param SessionManagerInterface $checkoutSession
     */
    public function __construct(
        Config $config,
        DataObjectFactory $dataObjectFactory,
        SessionManagerInterface $checkoutSession
    ) {
        $this->config = $config;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quote = $checkoutSession->getQuote();
    }

    /**
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function getDataObject(array $data)
    {
        return $this->dataObjectFactory->create($data);
    }
}