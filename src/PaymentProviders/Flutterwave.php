<?php


namespace Metav\NgPayments\PaymentProviders;

use GuzzleHttp\Client;
use Metav\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;

class Flutterwave extends AbstractPaymentProvider
{
    public function __construct($public_key, $secret_key, $app_env)
    {
        parent::__construct($public_key, $secret_key, $app_env);
        $this->baseUrl = "https://api.ravepay.co";
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
    }
}
