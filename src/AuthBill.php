<?php


namespace Kofi\NgPayments;

use Kofi\NgPayments\Exceptions\FailedPaymentException;

class AuthBill extends Bill
{
    public function __construct($authorization_code = null, $customer_email = null, $naira_amount = null)
    {
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            parent::__construct(func_get_arg(0));
        } else {
            $this->authorization_code = $authorization_code;
            parent::__construct($customer_email, $naira_amount);
        }
    }

    /**
     * @return string|null payment reference or null if the request failed
     * @throws Exceptions\InvalidRequestBodyException
     * @throws Exceptions\FailedPaymentException if PaymentExceptions are enabled
     */
    public function charge()
    {
        $this->paymentReference = $this->paymentProvider->chargeAuth($this->attributes);
        return $this->paymentReference;
    }

}
