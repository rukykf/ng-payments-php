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
        string $settlement_bank = null,
        string $account_number = null,
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

    public function save()
    {
        $this->id = $this->paymentProvider->saveSubAccount($this->attributes);
        return $this->id;
    }

    public static function list($query_params = null)
    {
        $subaccounts_data = PaymentProviderFactory::getPaymentProvider()->listSubAccounts($query_params);
        $subaccounts = [];
        if ($subaccounts_data == []) {
            return $subaccounts_data;
        }
        foreach ($subaccounts_data as $subaccount_data) {
            $subaccounts[] = new SubAccount($subaccount_data);
        }

        return $subaccounts;
    }

    public static function fetch($subaccount_id)
    {
        $subaccount_details = PaymentProviderFactory::getPaymentProvider()->fetchSubAccount($subaccount_id);
        return new SubAccount($subaccount_details);
    }
}
