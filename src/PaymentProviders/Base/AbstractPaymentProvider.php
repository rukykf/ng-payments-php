<?php


namespace Metav\NgPayments\PaymentProviders\Base;

use GuzzleHttp\Client;

abstract class AbstractPaymentProvider
{
    protected $publicKey = "";
    protected $secretKey = "";
    protected $appEnv = "";
    protected $baseUrl = "";
    protected $httpClient = null;
    protected $httpResponse = null;
    protected $httpExceptions = false;
    protected $transactionExceptions = true;

    public function __construct($public_key, $secret_key, $app_env)
    {
        $this->publicKey = $public_key;
        $this->secretKey = $secret_key;
        $this->appEnv = $app_env;
        $this->httpClient = new Client();
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getAppEnv(): string
    {
        return $this->appEnv;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return \GuzzleHttp\Client|null
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return \GuzzleHttp\Psr7\Response|null
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function getResponseBodyAsArray()
    {
        if ($this->httpResponse == null) {
            return null;
        }

        return json_decode($this->httpResponse->getBody(), true);
    }

    public function getResponseBodyAsObject()
    {
        if ($this->httpResponse == null) {
            return null;
        }

        return json_decode($this->httpResponse->getBody());
    }

    public function getResponseBodyAsStream()
    {
        if ($this->httpResponse == null) {
            return null;
        }

        return $this->httpResponse->getBody();
    }

    public function setHttpClient(Client $http_client)
    {
        $this->httpClient = $http_client;
    }

    public function enableHttpExceptions()
    {
        $this->httpExceptions = true;
    }

    public function enableTransactionExceptions()
    {
        $this->transactionExceptions = true;
    }

    public function disableHttpExceptions()
    {
        $this->httpExceptions = false;
    }

    public function disableTransactionExceptions()
    {
        $this->transactionExceptions = false;
    }
}
