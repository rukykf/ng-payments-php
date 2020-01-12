<?php


namespace Kofi\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use Kofi\NgPayments\Exceptions\FailedTransactionException;
use Kofi\NgPayments\Exceptions\FeatureNotSupportedException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

class Paystack extends AbstractPaymentProvider
{
    public function __construct($public_key, $secret_key, $app_env)
    {
        parent::__construct($public_key, $secret_key, $app_env);
        $this->baseUrl = "https://api.paystack.co";
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
    }

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

    public function isPaymentValid($reference, $expected_naira_amount)
    {
        $relative_url = '/transaction/verify/' . $reference;
        $request = $this->createRequestForPaystack($relative_url, [], 'GET');
        $this->sendRequest($request);

        $response_body = $this->getResponseBodyAsArray();
        $status = @$response_body['data']['status'];
        $expected_amount = $expected_naira_amount * 100;
        $amount_paid = @$response_body['data']['amount'];

        if ($this->transactionExceptions == true && $status != 'success') {
            throw new FailedTransactionException($request, $this->httpResponse);
        }

        if ($this->transactionExceptions == true && $amount_paid != $expected_amount) {
            throw new FailedTransactionException($request, $this->httpResponse);
        }

        if ($status == 'success' && $amount_paid == $expected_amount) {
            return true;
        }

        return false;
    }

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

        $status = @$this->getResponseBodyAsArray()['data']['status'];
        if ($this->transactionExceptions == true && $status != 'success') {
            throw new FailedTransactionException($request, $this->httpResponse);
        }

        if ($status == 'success') {
            return $this->getPaymentReference();
        }
        return null;
    }

    public function getPaymentPageUrl()
    {
        return @$this->getResponseBodyAsArray()['data']['authorization_url'];
    }

    public function getPaymentReference()
    {
        return @$this->getResponseBodyAsArray()['data']['reference'];
    }

    public function getPaymentAuthorizationCode()
    {
        return @$this->getResponseBodyAsArray()['data']['authorization']['authorization_code'];
    }

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

    public function fetchAllPlans($query_params = [])
    {
        $relative_url = "/plan";
        $query_params = $this->adaptBodyParamsToPaystackAPI($query_params);
        $request = $this->createRequestForPaystack($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data'];
    }

    public function fetchPlan($plan_id)
    {
        $relative_url = "/plan" . "/" . $plan_id;
        $request = $this->createRequestForPaystack($relative_url, [], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data'];
    }

    public function deletePlan($plan_id)
    {
        throw new FeatureNotSupportedException("Paystack does not provide an endpoint for deleting payment plans");
    }

    public function saveSubAccount($request_body)
    {
        $relative_url = "/subaccount";
        $request_body = $this->adaptBodyParamsToPaystackAPI($request_body);

        $subaccount_id = @$request_body['id'] ?? @$request_body['subaccount_code'];
        if ($subaccount_id == null) {
            return $this->createSubAccount($request_body, $relative_url);
        } else {
            return $this->updateSubAccount($request_body, $subaccount_id, $relative_url);
        }
    }

    public function fetchAllSubAccounts($query_params = [])
    {
        $relative_url = "/subaccount";
        $query_params = $this->adaptBodyParamsToPaystackAPI($query_params);
        $request = $this->createRequestForPaystack($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data'];
    }

    public function fetchSubAccount($subaccount_id)
    {
        $relative_url = "/subaccount" . "/" . $subaccount_id;
        $request = $this->createRequestForPaystack($relative_url, [], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data'];
    }

    public function deleteSubAccount($subaccount_id)
    {
        throw new FeatureNotSupportedException("Paystack does not provide an endpoint for deleting subaccounts");
    }

    /**
     * @param $request_body
     * @param string $relative_url
     * @return int|null
     * @throws \Metav\NgPayments\Exceptions\InvalidRequestBodyException
     */
    private function createPlan($request_body, string $relative_url)
    {
        $request = $this->createRequestForPaystack($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ['name', 'amount', 'interval'], $request);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['plan_code'];
    }

    /**
     * @param $request_body
     * @param $plan_id
     * @param string $relative_url
     * @return mixed
     */
    private function updatePlan($request_body, $plan_id, string $relative_url)
    {
        $relative_url .= "/" . $plan_id;
        $request = $this->createRequestForPaystack($relative_url, $request_body, 'PUT');
        $this->sendRequest($request);

        if (@$this->getResponseBodyAsArray()["status"] == true) {
            return $plan_id;
        }
        return null;
    }

    private function createSubAccount(array $request_body, string $relative_url)
    {
        $request = $this->createRequestForPaystack($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams(
            $request_body,
            ['business_name', 'settlement_bank', 'account_number', 'percentage_charge'],
            $request
        );
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['subaccount_code'];
    }

    /**
     * @param $request_body
     * @param $subaccount_id
     * @param string $relative_url
     * @return mixed
     */
    private function updateSubAccount($request_body, $subaccount_id, string $relative_url)
    {
        $relative_url .= "/" . $subaccount_id;
        $request = $this->createRequestForPaystack($relative_url, $request_body, 'PUT');
        $this->sendRequest($request);
        if (@$this->getResponseBodyAsArray()["status"] == true) {
            return $subaccount_id;
        }
        return null;
    }

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

    private function getPaystackParams()
    {
        return [
            "customer_email" => "email"
        ];
    }

    private function getPaystackTransactionEndpointParams()
    {
        return [
            "subaccount_code" => "subaccount",
            "plan_code" => "plan"
        ];
    }
}
