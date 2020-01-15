<?php


namespace Kofi\NgPayments;

use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\Traits\AttributesTrait;

class Bill
{
    use AttributesTrait;

    protected $paymentPageUrl;
    protected $paymentReference;
    protected $paymentProvider;

    public function __construct($customer_email = null, $naira_amount = null)
    {
        $this->paymentProvider = PaymentProviderFactory::getPaymentProvider();
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->attributes = func_get_arg(0);
        } else {
            $this->customer_email = $customer_email;
            $this->naira_amount = $naira_amount;
        }
    }

    /**
     * @return $this
     * @throws Exceptions\InvalidRequestBodyException
     */
    public function charge()
    {
        $this->paymentReference = $this->paymentProvider->initializePayment($this->attributes);
        $this->paymentPageUrl = $this->paymentProvider->getPaymentPageUrl();
        return $this;
    }

    public function splitCharge($subaccount_code)
    {
        $this->subaccount_code = $subaccount_code;
        return $this->charge();
    }

    public function subscribe($plan_code)
    {
        $this->plan_code = $plan_code;
        return $this->charge();
    }

    /**
     * @param $payment_reference
     * @param $naira_amount
     * @return bool
     * @throws Exceptions\InvalidPaymentProviderConfigException
     * @throws Exceptions\FailedPaymentException if paymentExceptions are enabled
     */
    public static function isPaymentValid($payment_reference, $naira_amount)
    {
        return PaymentProviderFactory::getPaymentProvider()->isPaymentValid($payment_reference, $naira_amount);
    }

    /**
     * @param $payment_reference
     * @param $naira_amount
     * @return string|null authorization_code or null if the request failed
     * @throws Exceptions\InvalidPaymentProviderConfigException
     * @throws Exceptions\FailedPaymentException if paymentExceptions are enabled
     */
    public static function getPaymentAuthorizationCode($payment_reference, $naira_amount)
    {
        $payment_provider = PaymentProviderFactory::getPaymentProvider();
        $payment_provider->isPaymentValid($payment_reference, $naira_amount);
        return $payment_provider->getPaymentAuthorizationCode();
    }

    /**
     * @return string|null
     */
    public function getPaymentPageUrl()
    {
        return $this->paymentPageUrl;
    }

    /**
     * @return string|null
     */
    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    /**
     * @return PaymentProviders\Base\AbstractPaymentProvider
     */
    public function &getPaymentProvider(): PaymentProviders\Base\AbstractPaymentProvider
    {
        return $this->paymentProvider;
    }


}
