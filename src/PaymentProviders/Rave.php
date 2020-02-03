<?php


namespace Kofi\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Kofi\NgPayments\Exceptions\FailedPaymentException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

class Rave extends AbstractPaymentProvider
{
    public function __construct($public_key, $secret_key, $app_env)
    {
        parent::__construct($public_key, $secret_key, $app_env);
        $this->baseUrl = "https://api.ravepay.co";
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
    }

    /**
     * @param array $request_body
     * @return string|null reference for this transaction or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function initializePayment($request_body)
    {
        $relative_url = "/flwv3-pug/getpaidx/api/v2/hosted/pay";
        $request_body = $this->adaptBodyParamsToRaveAPI($request_body, ['plan_code' => 'payment_plan']);
        $request_body = $this->processSubaccountsForRaveTransactionEndpoint($request_body);
        $request_body = $this->addRaveTransactionDefaults($request_body);
        $request_body['PBFPubKey'] = $this->publicKey;
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ['customer_email', 'amount'], $request);
        $this->sendRequest($request);
        if ($this->httpResponse->getStatusCode() == 200) {
            return $request_body['txref'];
        }
        return null;
    }

    /**
     * @param string $reference
     * @param float $expected_naira_amount
     * @return bool
     * @throws FailedPaymentException if paymentExceptions are enabled
     */
    public function isPaymentValid($reference, $expected_naira_amount)
    {
        $relative_url = "/flwv3-pug/getpaidx/api/v2/verify";
        $request_body = ["SECKEY" => $this->secretKey, "txref" => $reference];
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->sendRequest($request);
        $status = @$this->getResponseBody()["data"]["status"];
        $amount_paid = @$this->getResponseBody()["data"]["chargedamount"];

        if ($this->paymentExceptions == true && $status != "successful") {
            throw new FailedPaymentException($request, $this->httpResponse);
        }

        if ($this->paymentExceptions == true && $amount_paid != $expected_naira_amount) {
            throw new FailedPaymentException($request, $this->httpResponse);
        }

        if ($status == "successful" && $amount_paid == $expected_naira_amount) {
            return true;
        }

        return false;
    }

    /**
     * Used along with the customer's embedtoken to bill the customer
     * without requesting for their payment details again
     *
     * @param array $request_body
     * @return string|null transaction reference or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws FailedPaymentException if paymentExceptions are enabled
     */
    public function chargeAuth($request_body)
    {
        $relative_url = "/flwv3-pug/getpaidx/api/tokenized/charge";
        $request_body = $this->adaptBodyParamsToRaveAPI(
            $request_body,
            ["customer_email" => "email", "plan_code" => "payment_plan"]
        );
        $request_body = $this->addRaveTransactionDefaults($request_body);
        $request_body = $this->processSubaccountsForRaveTransactionEndpoint($request_body);

        //Rave is not very consistent with its parameter spellings across endpoints
        //this is a quirk I noticed for this endpoint
        if (!isset($request_body['txRef'])) {
            $request_body['txRef'] = $request_body['txref'];
        }
        $request_body['SECKEY'] = $this->secretKey;
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ["amount", "email", "token"], $request);
        $this->sendRequest($request);
        $status = @$this->getResponseBody()["data"]["status"];

        if ($this->paymentExceptions == true && $status != "successful") {
            throw new FailedPaymentException($request, $this->httpResponse);
        }

        if ($status == "successful") {
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
        return @$this->getResponseBody()['data']['link'];
    }

    /**
     * @return string|null
     */
    public function getPaymentReference()
    {
        $response_body = $this->getResponseBody();
        return @$response_body['data']['txref'] ?? @$response_body['data']['txRef'];
    }

    /**
     * Should be called after isPaymentValid() to retrieve the embedtoken
     * which can be used to charge the customer again in the future
     *
     * @return string|null
     */
    public function getPaymentAuthorizationCode()
    {
        return @$this->getResponseBody()['data']['card']['card_tokens'][0]['embedtoken'];
    }

    /**
     * @param array $request_body
     * @return mixed|null id of the saved plan or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     * @throws InvalidRequestBodyException
     */
    public function savePlan($request_body)
    {
        $request_body = $this->adaptBodyParamsToRaveAPI($request_body, ["plan_code" => "id"]);
        $plan_id = @$request_body['id'];
        if ($plan_id == null) {
            return $this->createPlan($request_body);
        } else {
            return $this->updatePlan($request_body, $plan_id);
        }
    }

    /**
     * @param array $query_params
     * @return array|null an array of plan assoc arrays or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchAllPlans($query_params = [])
    {
        $relative_url = "/v2/gpx/paymentplans/query";
        $query_params = $this->adaptBodyParamsToRaveAPI($query_params);
        $query_params['seckey'] = $this->secretKey;
        $request = $this->createRequestForRave($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['paymentplans'];
    }

    /**
     * @param $plan_id
     * @return array|null an assoc array of plan details or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchPlan($plan_id)
    {
        $relative_url = "/v2/gpx/paymentplans/query";
        $query_params = [
            "id" => $plan_id,
            "seckey" => $this->secretKey
        ];
        $request = $this->createRequestForRave($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['paymentplans'][0];
    }

    /**
     * @param $plan_id
     * @return string|null a success message or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function deletePlan($plan_id)
    {
        $relative_url = "/v2/gpx/paymentplans/" . $plan_id . "/cancel";
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey]);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['status'];
    }

    /**
     * @param array $request_body
     * @return mixed|null the saved subaccount's subaccount_id or null if the request failed
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enbabled
     */
    public function saveSubaccount($request_body)
    {
        $request_body = $this->adaptBodyParamsToRaveAPI(
            $request_body,
            ["subaccount_code" => "id", "percentage_charge" => "split_value"]
        );
        $subaccount_id = @$request_body['id'] ?? @$request_body['subaccount_id'];
        if ($subaccount_id == null) {
            return $this->createSubaccount($request_body);
        } else {
            return $this->updateSubaccount($request_body, $subaccount_id);
        }
    }

    /**
     * @param array $query_params
     * @return array|null an array of subaccount assoc arrays or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchAllSubaccounts($query_params = [])
    {
        $relative_url = "/v2/gpx/subaccounts";
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['subaccounts'];
    }

    /**
     * @param $subaccount_id
     * @return array|null an assoc array containing the subaccount's data or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchSubaccount($subaccount_id)
    {
        $relative_url = "/v2/gpx/subaccounts/get/" . $subaccount_id;
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey], 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data'];
    }

    /**
     * @param $subaccount_id
     * @return string|null a success message or null if the request failed
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function deleteSubaccount($subaccount_id)
    {
        $relative_url = "/v2/gpx/subaccounts/delete";
        $request = $this->createRequestForRave($relative_url, ['seckey' => $this->secretKey, 'id' => $subaccount_id]);
        $this->sendRequest($request);
        return @$this->getResponseBody()['status'];
    }

    /**
     * @param array $query_params
     * @return array|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    public function fetchBanks($query_params = [])
    {
        $country = $query_params["country"] ?? "NG";
        $relative_url = "/v2/banks/" . $country;
        $query_params["public_key"] = $this->publicKey;
        $request = $this->createRequestForRave($relative_url, $query_params, 'GET', true);
        $this->sendRequest($request);
        return @$this->getResponseBody()["data"]["Banks"];
    }

    /**
     * @param $request_body
     * @return mixed|null
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function createPlan($request_body)
    {
        $relative_url = "/v2/gpx/paymentplans/create";
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ["amount", "name", "interval"]);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['id'];
    }

    /**
     * @param $request_body
     * @param $plan_id
     * @return mixed|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function updatePlan($request_body, $plan_id)
    {
        $relative_url = "/v2/gpx/paymentplans/" . $plan_id . "/edit";
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['id'];
    }

    /**
     * Rave has two methods of identifying subaccounts: a numeric Id and an alphanumeric string
     * it makes use of the numeric Id for some subaccount endpoints and the alphanumeric string on others
     * this function converts the alphanumeric subaccount_id into the corresponding numeric id for the same subaccount
     *
     * @param $subaccount_id
     * @return string|int
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function translateSubAccountId($subaccount_id)
    {
        if (is_int($subaccount_id)) {
            return $subaccount_id;
        }
        $subaccount = $this->fetchSubaccount($subaccount_id);
        return $subaccount['id'];
    }

    /**
     * @param $request_body
     * @return mixed|null
     * @throws InvalidRequestBodyException
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function createSubaccount($request_body)
    {
        $relative_url = "/v2/gpx/subaccounts/create";
        $request_body = $this->adaptBodyParamsToRaveAPI($request_body, $this->getRaveCreateSubaccountParams());
        $request_body = $this->addRaveCreateSubaccountDefaults($request_body);
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->validateRequestBodyHasRequiredParams(
            $request_body,
            ["account_bank", "account_number", "business_mobile", "business_name", "business_email", "split_value"],
            $request
        );
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['subaccount_id'];
    }

    /**
     * @param $request_body
     * @param $subaccount_id
     * @return mixed|null
     * @throws BadResponseException if httpExceptions are enabled
     */
    private function updateSubaccount($request_body, $subaccount_id)
    {
        $relative_url = "/v2/gpx/subaccounts/edit";
        $subaccount_id = $this->translateSubAccountId($subaccount_id);
        $request_body['id'] = $subaccount_id;
        $request = $this->createRequestForRave($relative_url, $request_body);
        $this->sendRequest($request);
        return @$this->getResponseBody()['data']['subaccount_id'];
    }

    /**
     * @param string $relative_url
     * @param array $request_body
     * @param string $http_method
     * @param bool $is_query
     * @return \GuzzleHttp\Psr7\Request
     */
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
        if (!isset($request_body['PBFPubKey']) && !isset($request_body['SECKEY'])
            && !isset($request_body['seckey']) && !isset($request_body['public_key'])) {
            $request_body['seckey'] = $this->secretKey;
        }

        return $this->createRequest($url, $headers, $request_body, $http_method, $is_query);
    }

    /**
     * @param $request_body
     * @param array $rave_endpoint_params
     * @return array
     */
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

    /**
     * @param array $request_body
     * @return array
     */
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

    /**
     * @param array $request_body
     * @return array
     */
    private function addRaveCreateSubaccountDefaults(array $request_body)
    {
        if (!isset($request_body['split_type'])) {
            $request_body['split_type'] = 'percentage';
        }

        if (!isset($request_body['country'])) {
            $request_body['country'] = 'NG';
        }

        return $request_body;
    }

    /**
     * @param mixed $id
     * @return string
     */
    private function generateUniqueTransactionReference($id = null)
    {
        $random_string = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $transaction_reference = uniqid($random_string, true) . time() . $id;
        return $transaction_reference;
    }

    /**
     * @return array
     */
    private function getRaveParams()
    {
        return [
            "authorization_code" => "token",
            "settlement_bank" => "account_bank"
        ];
    }

    /**
     * @return array
     */
    private function getRaveCreateSubaccountParams()
    {
        return [
            "settlement_bank" => "account_bank",
            "percentage_charge" => "split_value",
        ];
    }

    /**
     * @param $request_body
     * @return mixed
     */
    private function processSubaccountsForRaveTransactionEndpoint($request_body)
    {
        if (isset($request_body['subaccount_code'])) {
            $request_body['subaccounts'] = [
                [
                    'id' => $request_body['subaccount_code']
                ]
            ];
        }
        return $request_body;
    }
}
