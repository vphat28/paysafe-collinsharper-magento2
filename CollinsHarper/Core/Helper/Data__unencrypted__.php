<?php

/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const MODULE_CODE = 'chcore';
	const CONFIG_PATH = 'core/';
	const XML_PATH_TEST = 'test';
	const XML_PATH_DEBUG = 'debug';
	const CODE_PATH = 'app/code/CollinsHarper/';
	const WARN_DAYS = 30;
	const WARN_FREQUENCY = 1;
	const CACHE_TAG = 'ch_core_license_notification';
	const CACHE_LIFE = 86400;
	const EXPIRES_REGEX = '/Expires: ([\w\s]+)/i';

	/**
	 * @var \Magento\Framework\Event\ManagerInterface
	 */
	protected $_eventDispatcher;


	/**
	 * @var \CollinsHarper\Logger\Logger
	 */
	protected $_logger;


	/**
	 * @var scopeConfig
	 */
	protected $scopeConfig;



	/**
	 * @var \CollinsHarper\Core\Model\ObjectFactory
	 */
	protected $objectFactory;


	/**
	 * @var \Magento\Framework\App\CacheInterface
	 */
	protected $_cacheManager;


	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $registry;
	protected $customerSession;


	/**
	 *
	 * @var bool
	 */
	private $is_mock = false;

	/**
	 *
	 * @var Varien_Data
	 */
	private $mock_config = false;
	private $mockObjectManager = false;


	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \CollinsHarper\Core\Logger\Logger $logger
	 * @param \CollinsHarper\Core\Model\ObjectFactory $objectFactory
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\App\CacheInterface $cacheManager
	 * @param \Magento\Framework\App\State $appState
	 * @param \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\CollinsHarper\Core\Logger\Logger $logger,
		\CollinsHarper\Core\Model\ObjectFactory $objectFactory,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\App\CacheInterface $cacheManager
	) {
		$this->_urlBuilder = $context->getUrlBuilder();
		$this->_eventDispatcher = $context->getEventManager();
		$this->_cacheManager = $cacheManager;
		$this->_logger = $logger;
		$this->registry = $registry;
		$this->objectFactory = $objectFactory;
		$this->scopeConfig = $context->getScopeConfig();

	}


	public function isTest()
	{
		return $this->getModuleConfig(self::XML_PATH_TEST);
	}

	public function isDebug()
	{
		return $this->getModuleConfig(self::XML_PATH_DEBUG);
	}

	public function getConfigPath()
	{
		return self::CONFIG_PATH . self::MODULE_CODE . '/';
	}

	public function getModuleConfig($path)
	{
		$path = $this->getConfigPath() . $path;

		$value = $this->getConfigValue($path);

		if ($this->isAdmin()) {

			$_creditMemo = $this->registry->registry('current_creditmemo');
			if ($_creditMemo) {
				$storeId = $_creditMemo->getOrder()->getData("store_id");
			} else {
				$quote = $this->getBackendSessionQuote();

				$storeId = $quote->getStoreId();
			}

			if(!$storeId) {
				if($this->registry->registry('current_order') &&  $this->registry->registry('current_order')->getStoreId()) {
					$storeId = $this->registry->registry('current_order')->getStoreId();
				} else if ( $this->registry->registry('current_invoice') &&  $this->registry->registry('current_invoice')->getStoreId()) {
					$storeId = $this->registry->registry('current_invoice')->getStoreId();
				}
			}


			if($storeId) {
				$value = $this->getConfigValue($path, $storeId);
			}
		}

		if($this->isMock()) {
			$value = isset($this->mock_config[$path]) ? $this->mock_config[$path]: $value;
		}

		return $value;
	}

	public function getObject($class)
	{
		if($this->isMock() && $this->mockObjectManager) {
			return $this->mockObjectManager->getObject($class);
		}

		return $this->objectFactory->create([], $class);
	}

	public function setMockManager($objectManager)
	{
		$this->mockObjectManager = $objectManager;
		$this->is_mock = true;


	}
	public function isAdmin()
	{
		// TODO this needs testing && tet for username? valiid sesion?
		return $this->getBackendSession() && $this->getBackendSession()->getId();
	}

	public function isMock()
	{
		return $this->is_mock == true;
	}

	public function setMockData($data)
	{
		$this->mock_config = $data;
		$this->is_mock = true;

	}

	public function getMockData()
	{
		return $this->mock_config;
	}


	/**
	 *
	 * @param string $path
	 * @param string $scopeType
	 * @param string $scopeCode
	 * @return mixed
	 */
	public function getConfigValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
	{
		$value = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);

		if($this->isMock()) {
			$value = isset($this->mock_config[$path]) ? $this->mock_config[$path]: $value;
		}
		return $value;
	}

	public function repopulateCart()
	{
		$session = $this->getCheckoutSession();
		$quoteId = $session->getMonerisccQuoteId(true);

		if (!$quoteId) {
			return $this;
		}

		$session->setQuoteId($quoteId);
		$session->setLoadInactive(true)->getQuote()->setIsActive(true)->save();

//		$cart = Mage::getModel('sales/quote')->load($quoteId);
//		$cart->setIsActive(true)->save();

		return $this;
	}

	public function setCustomerSession($x)
	{
		$this->customerSession = $x;
	}
	public function getCustomerSession()
	{
		if(!$this->customerSession) {
			$this->customerSession = $this->getObject('Magento\Customer\Model\Session');
		}
		return $this->customerSession;
	}

	public function getCheckoutSession()
	{
		return $this->getObject('Magento\Checkout\Model\Session');
	}

	public function getOrder($orderId)
	{
		return $this->getObject('Magento\Sales\Model\Order')->load($orderId);
	}


	public function getBackendSession()
	{
		return $this->getObject('Magento\Backend\Model\Session');
    }

	public function getBackendSessionQuote()
	{
		return $this->getObject('Magento\Backend\Model\Session\Quote');
    }

	public function log($data, $force = false)
	{
		if($force || $this->isTest() || $this->isDebug()) {

			if ($data instanceof Varien_Object) {
				$data = $data->getData();
			}

			$this->_logger->info($data);
		}
	}

	public function critical($data, $force = false)
	{
		if($force || $this->isTest() || $this->isDebug()) {

			if ($data instanceof Varien_Object) {
				$data = $data->getData();
			}

			$this->_logger->critical($data);
		}
	}

	/**
	 * Retrieve url
	 *
	 * @param   string $route
	 * @param   array $params
	 * @return  string
	 */
	public function getUrl($route, $params = [])
	{
		return $this->_urlBuilder->getUrl($route, $params);
	}


// TODO check all TXT files in the root of the site ch_lic_X.txt
// if they are time limited and set to expire in a short amount of time
// attempt a call out to CH lic end point
// if we cant get there  or the lic is still valid and we cant update the file
// tell the admin
// if the license is bad ; do nothing it will die on its own?

	public function checkLicenses()
	{
		$recentChecked =  $this->_cacheManager->load(self::CACHE_TAG);
		if($recentChecked == date('Y-m-d')) {
			return;
		}

		$files = glob(BP . SELF::CODE_PATH . '*/etc/collinsharper_license_*.txt');

		foreach($files as $file) {
			try {

				$data = file_get_contents($file);
				$match = false;
				$regEx = self::EXPIRES_REGEX;

				preg_match($regEx, $data, $match);

			} catch (Exception $e) {
				// this is a notification class; exceptions are ignored.

			}

			if(isset($match[1])) {
				$expirationDate = strtotime($match[1]);
				if($expirationDate) {
					$today  = strtotime('now');
					if($expirationDate - $today <= self::WARN_DAYS * (60*60*24)) {
						// TODO set admin warning notice
					}
				}
			}

		}

		$this->_cacheManager->save(date('Y-m-d'), self::CACHE_TAG, [self::CACHE_TAG], self::CACHE_LIFE);
		return true;
	}

}

