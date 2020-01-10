<?php


namespace Metav\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use Metav\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

class Flutterwave extends AbstractPaymentProvider
{
    public function __construct($public_key, $secret_key, $app_env, $error_config = [])
    {
        parent::__construct($public_key, $secret_key, $app_env, $error_config);
        $this->baseUrl = "https://api.ravepay.co";
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
    }

    public function initializePayment($request_body)
    {
        // TODO: Implement initializePayment() method.
    }

    public function isPaymentValid($reference, $naira_amount)
    {
        // TODO: Implement verifyPayment() method.
    }

    public function getPaymentPageUrl()
    {
        // TODO: Implement getPaymentPageUrl() method.
    }

    public function getPaymentReference()
    {
        // TODO: Implement getPaymentReference() method.
    }

    public function getPaymentAuthorizationCode()
    {
        // TODO: Implement getPaymentAuthorizationCode() method.
    }

    public function chargeAuth($request_body)
    {
        // TODO: Implement chargeAuth() method.
    }

    public function deletePlan($plan_id)
    {
        // TODO: Implement deletePlan() method.
    }
}
