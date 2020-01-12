<?php


namespace Kofi\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use Kofi\NgPayments\Exceptions\FailedTransactionException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

class Rave extends AbstractPaymentProvider
{
    public function __construct($public_key, $secret_key, $app_env)
    {
        parent::__construct($public_key, $secret_key, $app_env);
        $this->baseUrl = "https://api.ravepay.co";
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
    }

    public function initializePayment($request_body)
    {
        $relative_url = "/flwv3-pug/getpaidx/api/v2/hosted/pay";
        $request_body = $this->adaptBodyParamsToRaveAPI($request_body);
        $request_body = $this->addRaveTransactionDefaults($request_body);
        $request_body['PBFPubKey'] = $this->publicKey;
        $this->validateRequestBodyHasRequiredParams($request_body, ['email', 'amount']);
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->httpResponse = $this->httpClient->send($request, ["http_errors" => $this->httpExceptions]);
        return $request_body['txref'];
    }

    public function isPaymentValid($reference, $expected_naira_amount)
    {
        $relative_url = "/flwv3-pug/getpaidx/api/v2/verify";
        $request_body = ["SECKEY" => $this->secretKey, "txref" => $reference];
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->sendRequest($request);
        $status = @$this->getResponseBodyAsArray()["data"]["status"];
        $amount_paid = @$this->getResponseBodyAsArray()["data"]["chargedamount"];

        if ($this->transactionExceptions == true && $status != "successful") {
            throw new FailedTransactionException($request, $this->httpResponse);
        }

        if ($this->transactionExceptions == true && $amount_paid != $expected_naira_amount) {
            throw new FailedTransactionException($request, $this->httpResponse);
        }

        if ($status == "successful" && $amount_paid == $expected_naira_amount) {
            return true;
        }

        return false;
    }

    public function chargeAuth($request_body)
    {
        $relative_url = "/flwv3-pug/getpaidx/api/tokenized/charge";
        $request_body = $this->adaptBodyParamsToRaveAPI($request_body, ["customer_email" => "email"]);
        $request_body = $this->addRaveTransactionDefaults($request_body);

        //Rave is not very consistent with its parameter spellings across endpoints
        //this is a quirk I noticed for this endpoint
        if (!isset($request_body['txRef'])) {
            $request_body['txRef'] = $request_body['txref'];
        }
        $request_body['SECKEY'] = $this->secretKey;
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ["amount", "email", "token"], $request);
        $this->sendRequest($request);
        $status = @$this->getResponseBodyAsArray()["data"]["status"];

        if ($this->transactionExceptions == true && $status != "successful") {
            throw new FailedTransactionException($request, $this->httpResponse);
        }

        if ($status == "successful") {
            return $this->getPaymentReference();
        }

        return null;
    }

    public function getPaymentPageUrl()
    {
        return @$this->getResponseBodyAsArray()['data']['link'];
    }

    public function getPaymentReference()
    {
        $response_body = $this->getResponseBodyAsArray();
        return @$response_body['data']['txref'] ?? @$response_body['data']['txRef'];
    }

    public function getPaymentAuthorizationCode()
    {
        return @$this->getResponseBodyAsArray()['data']['card']['card_tokens']['embedtoken'];
    }

    public function savePlan($request_body)
    {
        $request_body = $this->adaptBodyParamsToPaystackAPI($request_body);
        $plan_id = @$request_body['id'];
        if ($plan_id == null) {
            return $this->createPlan($request_body);
        } else {
            return $this->updatePlan($request_body, $plan_id);
        }
    }

    public function fetchAllPlans($query_params = [])
    {
        $relative_url = "/v2/gpx/paymentplans/query";
        $query_params = $this->adaptBodyParamsToRaveAPI($query_params);
        $query_params['seckey'] = $this->secretKey;
        $request = $this->createRequestForRave($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['paymentplans'];
    }

    public function fetchPlan($plan_id)
    {
        $relative_url = "/v2/gpx/paymentplans/query";
        $query_params = [
            "id" => $plan_id,
            "seckey" => $this->secretKey
        ];
        $request = $this->createRequestForRave($relative_url, $query_params, 'GET', true);
        $this->sendRequest();
        return @$this->getResponseBodyAsArray()['data']['paymentplans'][0];
    }

    public function deletePlan($plan_id)
    {
        $relative_url = "/v2/gpx/paymentplans/" . $plan_id . "/cancel";
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey]);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['status'];
    }

    public function saveSubAccount($request_body)
    {
        $request_body = $this->adaptBodyParamsToPaystackAPI($request_body);
        $subaccount_id = @$request_body['id'] ?? @$request_body['subaccount_id'];
        if ($subaccount_id == null) {
            return $this->createSubAccount($request_body);
        } else {
            return $this->updateSubAccount($request_body, $subaccount_id);
        }
    }

    public function fetchAllSubAccounts($query_params = [])
    {
        $relative_url = "/v2/gpx/subaccounts";
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['subaccounts'];
    }

    public function fetchSubAccount($subaccount_id)
    {
        $relative_url = "/v2/gpx/subaccounts/get/" . $subaccount_id;
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data'];
    }

    public function deleteSubAccount($subaccount_id)
    {
        $relative_url = "/v2/gpx/subaccounts/delete";
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey, 'id' => $subaccount_id]);
        $this->sendRequest($request);
        if (@$this->getResponseBodyAsArray()['status'] == "success") {
            return "successfully deleted";
        }

        return null;
    }

    private function createPlan($request_body)
    {
        $relative_url = "/v2/gpx/paymentplans/create";
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ["amount", "name", "interval"]);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['id'];
    }

    private function updatePlan($request_body, $plan_id)
    {
        $relative_url = "/v2/gpx/paymentplans/" . $plan_id . "/edit";
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['id'];
    }

    private function translateSubAccountId($subaccount_id)
    {
        if (is_int($subaccount_id)) {
            return $subaccount_id;
        }
        $subaccount = $this->fetchSubAccount($subaccount_id);
        return $subaccount['id'];
    }

    private function createSubAccount($request_body)
    {
        $relative_url = "/v2/gpx/subaccounts/create";
        $request_body = $this->adaptBodyParamsToRaveAPI($request_body, $this->getRaveCreateSubAccountParams());
        $request_body = $this->addRaveCreateSubAccountDefaults($request_body);
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams(
            $request_body,
            ["account_bank", "account_number", "business_mobile", "business_name", "business_email", "split_value"],
            $request
        );
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['subaccount_id'];
    }

    private function updateSubAccount($request_body, $subaccount_id)
    {
        $relative_url = "/v2/gpx/subaccounts/edit";
        $subaccount_id = $this->translateSubAccountId($subaccount_id);
        $request_body['id'] = $subaccount_id;
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->sendRequest($request);
        return @$this->getResponseBodyAsArray()['data']['subaccount_id'];
    }

    private function createRequestForRave(
        string $relative_url,
        array $request_body,
        $http_method = "POST",
        $is_query = true
    ) {
        $headers = [
            "Content-Type" => "application/json"
        ];
        $url = $this->baseUrl . $relative_url;
        if (!isset($request_body['PBFPubKey']) && !isset($request_body['SECKEY']) && !isset($request_body['seckey'])) {
            $request_body['seckey'] = $this->secretKey;
        }

        return $this->createRequest($url, $headers, $request_body, $http_method, $is_query);
    }

    private function adaptBodyParamsToRaveAPI($request_body, $rave_endpoint_params = [])
    {
        $rave_params = $this->getRaveParams();
        $rave_request_body = $this->adaptBodyParamsToAPI($request_body, $rave_params);
        $rave_request_body = $this->adaptBodyParamsToAPI($rave_request_body, $rave_endpoint_params);

        if (isset($rave_request_body['naira_amount']) && !isset($rave_request_body['amount'])) {
            $rave_request_body['amount'] = $rave_request_body['naira_amount'];
            unset($rave_request_body['naira_amount']);
        }

        return $rave_request_body;
    }

    private function addRaveTransactionDefaults(array $request_body)
    {
        if (!isset($request_body['currency'])) {
            $request_body['currency'] = 'NGN';
        }

        if (!isset($request_body['txref'])) {
            $request_body['txref'] = $this->generateUniqueTransactionReference(@$request_body['unique_id']);
        }

        if (!isset($request_body['payment_options'])) {
            $request_body['payment_options'] = "card";
        }

        return $request_body;
    }

    private function addRaveCreateSubAccountDefaults(array $request_body)
    {
        if (!isset($request_body['split_type'])) {
            $request_body['split_type'] = 'percentage';
        }

        if (!isset($request_body['country'])) {
            $request_body['country'] = 'NG';
        }

        return $request_body;
    }

    private function generateUniqueTransactionReference($id = null)
    {
        $random_string = chr(rand(65, 90) . rand(65, 90) . rand(65, 90));
        $transaction_reference = uniqid($random_string, true) . time() . $id;
        return $transaction_reference;
    }

    private function getRaveParams()
    {
        return [
            "subaccount_code" => "id",
            "authorization_code" => "token",
            "plan_code" => "id"
        ];
    }

    private function getRaveCreateSubAccountParams()
    {
        return [
            "settlement_bank" => "account_bank",
            "percentage_charge" => "split_value",
        ];
    }
}
