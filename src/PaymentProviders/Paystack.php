<?php


namespace Metav\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use Metav\NgPayments\Exceptions\FailedTransactionException;
use Metav\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

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
        $relative_url = '/initialize';
        $request_body = $this->adaptBodyParamsToPaystackAPI($request_body);
        $this->validateRequestBodyHasRequiredParams($request_body, ['email', 'amount']);
        $request_options = $this->getRequestOptionsForPaystack($request_body);
        $this->httpResponse = $this->httpClient->post($relative_url, $request_options);
        return $this;
    }

    public function verifyPayment($reference, $amount = null)
    {
        $relative_url = '/transaction/verify/' . $reference;
        $this->httpResponse = $this->httpClient->get($relative_url, $this->getRequestOptionsForPaystack());
        $response_body = $this->getResponseBodyAsArray();
        $status = $response_body['data']['status'];

        if ($this->transactionExceptions == true && $status != 'success') {
            throw new FailedTransactionException($response_body);
        }

        if ($amount != null && $response_body['data']['amount'] != $amount) {
            throw new FailedTransactionException($response_body);
        }

        return $status;
    }

    public function getPaymentPageUrl()
    {
        return @$this->getResponseBodyAsArray()['data']['authorization_url'] ?? '';
    }

    public function getPaymentReference()
    {
        return @$this->getResponseBodyAsArray()['data']['reference'] ?? '';
    }


    protected function adaptBodyParamsToPaystackAPI($request_body)
    {
        $paystack_params = $this->getPaystackParams();
        $paystack_request_body = $this->adaptBodyParamsToAPI($request_body, $paystack_params);

        //paystack works with amount in kobo and this provider will receive amounts in naira
        //convert naira to kobo
        $paystack_request_body['amount'] = $paystack_request_body['amount'] * 100;
        return $paystack_request_body;
    }

    private function getPaystackParams()
    {
        return [
            "customer_email" => "email"
        ];
    }

    /**
     * @param $request_body
     * @return array
     */
    private function getRequestOptionsForPaystack($request_body = []): array
    {
        return [
            "headers" => [
                'authorization' => 'Bearer ' . $this->secretKey,
                'cache-control' => 'no-cache'
            ],
            "http_errors" => $this->httpExceptions,
            "json" => $request_body
        ];
    }
}
