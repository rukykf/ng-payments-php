<?php


namespace Kofi\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Kofi\NgPayments\Exceptions\FailedPaymentException;
use Kofi\NgPayments\Exceptions\FeatureNotSupportedException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

class Paystack extends AbstractPaymentProvider
{
    public function __construct($public_key, $secret_key, $app_env)
    {
        parent::__construct($public_key, $secret_key, $app_env);
        $this->baseUrl = "https://api.paystack.co";
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
    }

    /**
     * Start a new transaction
     *
     * @param array $request_body
     * @return string|null reference for this transaction or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function initializePayment($request_body)
    {
        $relative_url = '/transaction/initialize';
        $request_body = $this->adaptBodyParamsToPaystackAPI(
            $request_body,
            $this->getPaystackTransactionEndpointParams()
        );
        $request = $this->createRequestForPaystack($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ['email', 'amount'], $request);
        $this->sendRequest($request);
        return $this->getPaymentReference();
    }

    /**
     * Checks if the customer paid the amount that they were expected to pay
     *
     * @param string $reference
     * @param float $expected_naira_amount
     * @return boolean
     * @throws FailedPaymentException if paymentExceptions are turned on
     */
    public function isPaymentValid($reference, $expected_naira_amount)
    {
        $relative_url = '/transaction/verify/' . $reference;
        $request = $this->createRequestForPaystack($relative_url, [], 'GET');
        $this->sendRequest($request);

        $response_body = $this->getResponseBody();
        $status = @$response_body['data']['status'];
        $expected_amount = $expected_naira_amount * 100;
        $amount_paid = @$response_body['data']['amount'];

        if ($this->paymentExceptions == true && $status != 'success') {
            throw new FailedPaymentException($request, $this->httpResponse);
        }

        if ($this->paymentExceptions == true && $amount_paid != $expected_amount) {
            throw new FailedPaymentException($request, $this->httpResponse);
        }

        if ($status == 'success' && $amount_paid == $expected_amount) {
            return true;
        }

        return false;
    }

    /**
     * Used along with the authorization_code to bill a customer without
     * requesting for their payment details again
     *
     * @param array $request_body
     * @return string|null transaction reference or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws FailedPaymentException if paymentExceptions are enabled
     */
    public function chargeAuth($request_body)
    {
        $relative_url = "/transaction/charge_authorization";
        $request_body = $this->adaptBodyParamsToPaystackAPI(
            $request_body,
            $this->getPaystackTransactionEndpointParams()
        );
        $request = $this->createRequestForPaystack($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ['email', 'amount', 'authorization_code'], $request);
        $this->sendRequest($request);

        $status = @$this->getResponseBody()['data']['status'];
        if ($this->paymentExceptions == true && $status != 'success') {
            throw new FailedPaymentException($request, $this->httpResponse);
        }

        if ($status == 'success') {
            return $this->getPaymentReference();
        }
        return null;
    }

    /**
     * Should be called after calling initializePayment() to retrieve the Url to redirect the customer to for payment
     *
     * @return string|null
     */
    public function getPaymentPageUrl()
    {
        return @$this->getResponseBody()['data']['authorization_url'];
    }

    /**
     * @return string|null
     */
    public function getPaymentReference()
    {
        return @$this->getResponseBody()['data']['reference'];
    }

    /**
     * Should be called after isPaymentValid() to retrieve the authorization_code
     * which can be used to charge the customer again in the future
     *
     * @return string|null
     */
    public function getPaymentAuthorizationCode()
    {
        return @$this->getResponseBody()['data']['authorization']['authorization_code'];
    }

    /**
     * @param array $request_body
     * @return mixed|null returns the id of the saved plan or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function savePlan($request_body)
    {
        $relative_url = "/plan";
        $request_body = $this->adaptBodyParamsToPaystackAPI($request_body);

        $plan_id = @$request_body['id'] ?? @$request_body['plan_code'];
        if ($plan_id == null) {
            return $this->createPlan($request_body, $relative_url);
        } else {
            return $this->updatePlan($request_body, $plan_id, $relative_url);
        }
    }

    /**
     * @param array $query_params
     * @return array|null array of plan assoc arrays or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchAllPlans($query_params = [])
    {
        $relative_url = "/plan";
        $query_params = $this->adaptBodyParamsToPaystackAPI($query_params);
        $request = $this->createRequestForPaystack($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data'];
    }

    /**
     * @param $plan_id
     * @return array|null an assoc array of plan details or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchPlan($plan_id)
    {
        $relative_url = "/plan" . "/" . $plan_id;
        $request = $this->createRequestForPaystack($relative_url, [], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data'];
    }

    /**
     * @param $plan_id
     * @throws FeatureNotSupportedException
     */
    public function deletePlan($plan_id)
    {
        throw new FeatureNotSupportedException("Paystack does not provide an endpoint for deleting payment plans");
    }

    /**
     * @param array $request_body
     * @return mixed|null the saved subaccount's subaccount_id or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function saveSubaccount($request_body)
    {
        $relative_url = "/subaccount";
        $request_body = $this->adaptBodyParamsToPaystackAPI($request_body);

        $subaccount_id = @$request_body['id'] ?? @$request_body['subaccount_code'];
        if ($subaccount_id == null) {
            return $this->createSubaccount($request_body, $relative_url);
        } else {
            return $this->updateSubaccount($request_body, $subaccount_id, $relative_url);
        }
    }

    /**
     * @param array $query_params
     * @return array|null an array of subaccount assoc arrays or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchAllSubaccounts($query_params = [])
    {
        $relative_url = "/subaccount";
        $query_params = $this->adaptBodyParamsToPaystackAPI($query_params);
        $request = $this->createRequestForPaystack($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data'];
    }

    /**
     * @param $subaccount_id
     * @return array|null an assoc array containing the subaccount's data or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchSubaccount($subaccount_id)
    {
        $relative_url = "/subaccount" . "/" . $subaccount_id;
        $request = $this->createRequestForPaystack($relative_url, [], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data'];
    }

    /**
     * @param $subaccount_id
     * @throws FeatureNotSupportedException if httpExceptions are enabled
     */
    public function deleteSubaccount($subaccount_id)
    {
        throw new FeatureNotSupportedException("Paystack does not provide an endpoint for deleting subaccounts");
    }

    /**
     * @param array $query_params
     * @return array|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchBanks($query_params = [])
    {
        $relative_url = "/bank";
        if (!isset($query_params["country"])) {
            $query_params["country"] = "Nigeria";
        }

        $request = $this->createRequestForPaystack($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data'];
    }

    /**
     * @param $request_body
     * @param string $relative_url
     * @return mixed|null
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function createPlan($request_body, string $relative_url)
    {
        $request = $this->createRequestForPaystack($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ['name', 'amount', 'interval'], $request);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['plan_code'];
    }

    /**
     * @param $request_body
     * @param $plan_id
     * @param string $relative_url
     * @return mixed|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function updatePlan($request_body, $plan_id, string $relative_url)
    {
        $relative_url .= "/" . $plan_id;
        $request = $this->createRequestForPaystack($relative_url, $request_body, 'PUT');
        $this->sendRequest($request);

        if (@$this->getResponseBody()["status"] == true) {
            return $plan_id;
        }
        return null;
    }

    /**
     * @param array $request_body
     * @param string $relative_url
     * @return mixed|null
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function createSubaccount(array $request_body, string $relative_url)
    {
        $request = $this->createRequestForPaystack($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams(
            $request_body,
            ['business_name', 'settlement_bank', 'account_number', 'percentage_charge'],
            $request
        );
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['subaccount_code'];
    }

    /**
     * @param $request_body
     * @param $subaccount_id
     * @param string $relative_url
     * @return mixed|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function updateSubaccount($request_body, $subaccount_id, string $relative_url)
    {
        $relative_url .= "/" . $subaccount_id;
        $request = $this->createRequestForPaystack($relative_url, $request_body, 'PUT');
        $this->sendRequest($request);
        if (@$this->getResponseBody()["status"] == true) {
            return $subaccount_id;
        }
        return null;
    }

    /**
     * @param string $relative_url
     * @param array $request_body
     * @param string $http_method
     * @param bool $is_query
     * @return \GuzzleHttp\Psr7\Request
     */
    private function createRequestForPaystack(
        $relative_url,
        array $request_body,
        $http_method = "POST",
        $is_query = true
    ) {
        $url = $this->baseUrl . $relative_url;
        $headers = [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Cache-Control' => 'no-cache',
            "Content-Type" => "application/json"
        ];

        return $this->createRequest($url, $headers, $request_body, $http_method, $is_query);
    }

    /**
     * @param array $request_body
     * @param array $paystack_endpoint_params
     * @return array
     */
    private function adaptBodyParamsToPaystackAPI($request_body, $paystack_endpoint_params = [])
    {
        $paystack_params = $this->getPaystackParams();
        $paystack_request_body = $this->adaptBodyParamsToAPI($request_body, $paystack_params);
        $paystack_request_body = $this->adaptBodyParamsToAPI($paystack_request_body, $paystack_endpoint_params);

        //paystack works with amount in kobo
        if (isset($paystack_request_body['naira_amount']) && !isset($paystack_request_body['amount'])) {
            $paystack_request_body['amount'] = $paystack_request_body['naira_amount'] * 100;
            unset($paystack_request_body['naira_amount']);
        }

        return $paystack_request_body;
    }

    /**
     * @return array
     */
    private function getPaystackParams()
    {
        return [
            "customer_email" => "email"
        ];
    }

    /**
     * @return array
     */
    private function getPaystackTransactionEndpointParams()
    {
        return [
            "subaccount_code" => "subaccount",
            "plan_code" => "plan"
        ];
    }
}
