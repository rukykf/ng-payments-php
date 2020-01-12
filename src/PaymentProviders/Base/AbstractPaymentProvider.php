<?php


namespace Kofi\NgPayments\PaymentProviders\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Psr\Http\Message\RequestInterface;

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

    //region interfaces
    abstract public function initializePayment($request_body);

    abstract public function isPaymentValid($reference, $expected_naira_amount);

    abstract public function chargeAuth($request_body);

    abstract public function getPaymentPageUrl();

    abstract public function getPaymentReference();

    abstract public function getPaymentAuthorizationCode();

    abstract public function savePlan($request_body);

    abstract public function fetchAllPlans($query_params = []);

    abstract public function fetchPlan($plan_id);

    abstract public function deletePlan($plan_id);

    abstract public function saveSubAccount($request_body);

    abstract public function fetchAllSubAccounts($query_params = []);

    abstract public function fetchSubAccount($subaccount_id);

    abstract public function deleteSubAccount($subaccount_id);
    //endregion

    //region getters and setters

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

    public function getResponseBody()
    {
        if ($this->httpResponse == null) {
            return null;
        }

        return $this->httpResponse->getBody();
    }

    public function getResponseBodyAsArray()
    {
        if ($this->httpResponse == null) {
            return [];
        }

        return json_decode($this->httpResponse->getBody(), true) ?? [];
    }

    public function getResponseBodyAsObject()
    {
        if ($this->httpResponse == null) {
            return null;
        }

        return json_decode($this->httpResponse->getBody());
    }

    public function setHttpClient(Client $http_client)
    {
        $this->httpClient = $http_client;
    }

    public function setErrorConfig($error_config)
    {
        $this->httpExceptions = @$error_config["http_exceptions"] ?? false;
        $this->transactionExceptions = @$error_config["transaction_exceptions"] ?? true;
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

    public function sendRequest(Request $request, $options = [])
    {
        $options["http_errors"] = $this->httpExceptions;
        $this->httpResponse = $this->httpClient->send($request, $options);
        return $this->httpResponse;
    }

    //endregion

    //region Helpers
    /**
     * @param $uri
     * @param array $request_body
     * @param $http_method
     * @param $is_query
     * @param array $headers
     * @return Request
     */
    protected function createRequest(
        $uri,
        array $headers,
        array $request_body,
        $http_method = "POST",
        $is_query = true
    ): Request {
        $uri = new Uri($uri);
        if ($http_method == "GET" && $is_query == true) {
            $uri = Uri::withQueryValues($uri, $request_body);
            $request = new Request($http_method, $uri, $headers);
        } else {
            $request = new Request($http_method, $uri, $headers, json_encode($request_body));
        }
        return $request;
    }

    protected function validateRequestBodyHasRequiredParams(
        $request_body,
        $required_params,
        RequestInterface $request = null
    ) {
        foreach ($required_params as $param) {
            if (!array_key_exists($param, $request_body) || @$request_body[$param] === null) {
                throw new InvalidRequestBodyException($param . " is a required parameter for this request", $request);
            }
        }

        return true;
    }

    protected function adaptBodyParamsToAPI($request_body, $api_params)
    {
        if (empty($request_body)) {
            return $request_body;
        }

        $api_request_body = [];
        foreach ($request_body as $param => $value) {
            if (isset($api_params[$param])) {
                $api_param = $api_params[$param];
                $api_request_body[$api_param] = $value;
            } else {
                $api_request_body[$param] = $value;
            }
        }
        return $api_request_body;
    }
    //endregion

}
