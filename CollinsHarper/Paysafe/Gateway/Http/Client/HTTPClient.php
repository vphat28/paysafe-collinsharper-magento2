<?php
/**
 * Copyright © 2017 CollinsHarper. All rights reserved.
 * See accompanying License.txt for applicable terms of use and license.
 */

namespace CollinsHarper\Paysafe\Gateway\Http\Client;

use CollinsHarper\Paysafe\Gateway\Config\Config;
use CollinsHarper\Paysafe\Model\Adminhtml\Source\Environment;
use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use CollinsHarper\Paysafe\Gateway\Exception\PaysafeException;
use CollinsHarper\Paysafe\Gateway\Exception\InvalidRequestException;
use CollinsHarper\Paysafe\Gateway\Exception\InvalidCredentialsException;
use CollinsHarper\Paysafe\Gateway\Exception\RequestDeclinedException;
use CollinsHarper\Paysafe\Gateway\Exception\PermissionException;
use CollinsHarper\Paysafe\Gateway\Exception\EntityNotFoundException;
use CollinsHarper\Paysafe\Gateway\Exception\RequestConflictException;
use CollinsHarper\Paysafe\Gateway\Exception\APIException;

class HTTPClient implements ClientInterface
{
    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var ZendClient
     */
    protected $client;

    /**
     * @var  Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        Config $config,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->createClient();
    }

    /**
     * @param TransferInterface $transferObject
     * @return \Zend_Http_Response|array
     * @throws \Zend_Http_Client_Exception
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        if ($transferObject->getUri() === "" && $transferObject->getMethod() === "") {
            return $transferObject->getBody();
        }

        $this->client->setMethod($transferObject->getMethod());
        $this->client->setUri($transferObject->getUri());
        $this->client->setRawData($transferObject->getBody());

        $this->logger->debug("URI: " . $transferObject->getUri());
        $this->logger->debug("Header: " . $this->client->getHeader('Authorization'));
        $this->logger->debug("Body: ". $transferObject->getBody());

        try {
            $response = $this->client->request();
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $response = $this->handleResponse($response);

        return $response;
    }

    /**
     * @param \Zend_Http_Response $response
     * @return mixed
     * @throws \Zend_Json_Exception
     */
    private function handleResponse(\Zend_Http_Response $response)
    {
        $responseCode = $response->getStatus();

        if (!($return = json_decode($response->getBody(), true))) {
            if ($responseCode < 200 || $responseCode >= 206) {
                throw $this->getPaysafeException($responseCode);
            }
            throw new \Zend_Json_Exception("Unable to parse gateway response " . $response->getBody());
        }

        if (is_array($return)) {
            if ($responseCode < 200 || $responseCode >= 206) {
                $error = $this->getPaysafeException($responseCode, $return['error']['message'], $return['error']['code']);
                $error->rawResponse = $return;
                if(array_key_exists('error', $return)) {
                    if (array_key_exists('fieldErrors', $return['error'])) {
                        $error->fieldErrors = $return['error']['fieldErrors'];
                    }
                    if (array_key_exists('links', $return['error'])) {
                        $error->links = $return['error']['links'];
                    }
                    if (array_key_exists('details', $return['error'])) {
                        $error->details = $return['error']['details'];
                    }
                }
                throw $error;
            }
            return $return;
        } else {
            throw $this->getPaySafeException($responseCode, $return);
        }
    }

    /**
     * @param $httpCode
     * @param null $message
     * @param null $code
     * @return mixed
     */
    private function getPaySafeException($httpCode, $message = null, $code = null)
    {
        if(!$message) {
            $message = "An unknown error has occurred.";
        }
        if(!$code) {
            $code = $httpCode;
        }
        $exceptionType = PaysafeException::class;
        switch($httpCode) {
            case '400':
                $exceptionType = InvalidRequestException::class;
                break;
            case '401':
                $exceptionType = InvalidCredentialsException::class;
                break;
            case '402':
                $exceptionType = RequestDeclinedException::class;
                break;
            case '403':
                $exceptionType = PermissionException::class;
                break;
            case '404':
                $exceptionType = EntityNotFoundException::class;
                break;
            case '409':
                $exceptionType = RequestConflictException::class;
                break;
            case '406':
            case '415':
                $exceptionType = APIException::class;
                break;
            default:
                if($httpCode >= 500) {
                    $exceptionType = APIException::class;
                }
                break;
        }
        return new $exceptionType($message,$code);
    }

    private function createClient()
    {
        /** @var ZendClient $client */
        $this->client = $this->httpClientFactory->create()->setHeaders($this->getRequestHeaders());
    }

    /**
     * @return array
     */
    public function getRequestHeaders()
    {
        return [
            'Authorization' => $this->getAuthorizationToken(),
            'Content-Type' => 'application/json; charset=utf-8'
        ];
    }

    public function getAuthorizationToken()
    {
        return 'Basic ' . base64_encode($this->config->getApiUsername() . ':' . $this->config->getApiPassword());
    }
}