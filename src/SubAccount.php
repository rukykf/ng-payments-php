<?php


namespace Metav\NgPayments;

use Metav\NgPayments\PaymentProviders\PaymentProviderFactory;
use Metav\NgPayments\Traits\AttributesTrait;

class SubAccount
{
    use AttributesTrait;

    protected $paymentProvider = null;

    public function __construct(
        $business_name = null,
        $settlement_bank = null,
        $account_number = null,
        $percentage_charge = null
    ) {
        $this->paymentProvider = PaymentProviderFactory::getPaymentProvider();
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->attributes = func_get_arg(0);
            return;
        }

        $this->attributes['business_name'] = $business_name;
        $this->attributes['settlement_bank'] = $settlement_bank;
        $this->attributes['account_number'] = $account_number;
        $this->attributes['percentage_charge'] = $percentage_charge;
    }
}
