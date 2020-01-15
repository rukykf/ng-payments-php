<?php


namespace Kofi\NgPayments\PaymentProviders\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Kofi\NgPayments\Exceptions\FailedPaymentException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractPaymentProvider
{
    /**
     * @var string
     */
    protected $publicKey = "";

    /**
     * @var string
     */
    protected $secretKey = "";

    /**
     * @var string
     */
    protected $appEnv = "";

    /**
     * @var string
     */
    protected $baseUrl = "";

    /**
     * @var Client|null
     */
    protected $httpClient = null;

    /**
     * @var ResponseInterface
     */
    protected $httpResponse = null;

    /**
     * @var bool
     */
    protected $httpExceptions = false;

    /**
     * @var bool
     */
    protected $paymentExceptions = true;

    public function __construct($public_key, $secret_key, $app_env)
    {
        $this->publicKey = $public_key;
        $this->secretKey = $secret_key;
        $this->appEnv = $app_env;
        $this->httpClient = new Client();
    }

    //region interfaces

    /**
     * Start a new transaction
     *
     * @param array $request_body
     * @return string|null reference for this transaction or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function initializePayment($request_body);

    /**
     * Checks if the customer paid the amount that they were expected to pay
     *
     * @param string $reference
     * @param float $expected_naira_amount
     * @return boolean
     * @throws FailedPaymentException if paymentExceptions are enabled
     */
    abstract public function isPaymentValid($reference, $expected_naira_amount);

    /**
     * Used along with the authorization_code or token to bill a customer without
     * requesting for their payment details again
     *
     * When a customer makes a successful payment, Paystack and Rave store the customer's details
     * and send an authorization_code or token which can be stored and used to charge that same customer again
     * in the future
     *
     * @param array $request_body
     * @return string|null transaction reference or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws FailedPaymentException if paymentExceptions are enabled
     */
    abstract public function chargeAuth($request_body);

    /**
     * Should be called after calling initializePayment() to retrieve the Url to redirect the customer to for payment
     *
     * @return string|null
     */
    abstract public function getPaymentPageUrl();

    /**
     * @return string|null
     */
    abstract public function getPaymentReference();

    /**
     * Should be called after isPaymentValid() to retrieve the authorization_code or embedtoken
     * which can be used to charge the customer again in the future
     *
     * @return string|null
     */
    abstract public function getPaymentAuthorizationCode();

    /**
     * @param array $request_body
     * @return mixed|null returns the id of the saved plan or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function savePlan($request_body);

    /**
     * @param array $query_params
     * @return array|null array of plan assoc arrays or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function fetchAllPlans($query_params = []);

    /**
     * @param $plan_id
     * @return array|null an assoc array of plan details or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function fetchPlan($plan_id);

    /**
     * @param $plan_id
     * @return string|null a success message or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function deletePlan($plan_id);

    /**
     * @param array $request_body
     * @return mixed|null the saved subaccount's subaccount_id or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function saveSubaccount($request_body);

    /**
     * @param array $query_params
     * @return array|null an array of subaccount assoc arrays or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function fetchAllSubaccounts($query_params = []);

    /**
     * @param $subaccount_id
     * @return array|null an assoc array containing the subaccount's data or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function fetchSubaccount($subaccount_id);

    /**
     * @param $subaccount_id
     * @return string|null a success message or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function deleteSubaccount($subaccount_id);

    /**
     * @param array $query_params
     * @return array|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    abstract public function fetchBanks($query_params = []);

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

    /**
     * @return array|null
     */
    public function getResponseBody()
    {
        return json_decode(@$this->httpResponse->getBody(), true);
    }

    public function setHttpClient(Client $http_client)
    {
        $this->httpClient = $http_client;
    }

    /**
     * Determine if the provider should throw http and transaction exceptions
     *
     * @param array $error_config
     */
    public function setErrorConfig($error_config)
    {
        $this->httpExceptions = @$error_config["http_exceptions"] ?? false;
        $this->paymentExceptions = @$error_config["transaction_exceptions"] ?? true;
    }

    public function enableHttpExceptions()
    {
        $this->httpExceptions = true;
    }

    public function enablePaymentExceptions()
    {
        $this->paymentExceptions = true;
    }

    public function disableHttpExceptions()
    {
        $this->httpExceptions = false;
    }

    public function disablePaymentExceptions()
    {
        $this->paymentExceptions = false;
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return ResponseInterface|null
     */
    public function sendRequest(RequestInterface $request, $options = [])
    {
        $options["http_errors"] = $this->httpExceptions;
        $this->httpResponse = $this->httpClient->send($request, $options);
        return $this->httpResponse;
    }

    //endregion

    //region Helpers
    /**
     * @param string $uri
     * @param array $request_body
     * @param string $http_method
     * @param boolean $is_query
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

    /**
     * Checkes if the required parameters for a request are set
     * and throws an exception otherwise
     *
     * @param array $request_body
     * @param array $required_params
     * @param RequestInterface|null $request
     * @return bool
     * @throws InvalidRequestBodyException
     */
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

    /**
     * While Rave and Paystack are very similar, they have some differences
     * in the way they name certain things. e.g Paystack needs email for an endpoint
     * while Rave needs customer_email. This method attempts to unify those differences across the interfaces
     * that this library is exposing by making it possible to pass one customer_email param
     * to the same PaymentProvider interface method
     * and having Paystack do the necessary conversion from customer_email to email
     *
     *
     * @param array $request_body
     * @param array $api_params
     * @return array
     */
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
