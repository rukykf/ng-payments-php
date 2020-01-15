<?php


namespace Kofi\NgPayments;

use Kofi\NgPayments\Interfaces\ApiDataMapperInterface;
use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\Traits\AttributesTrait;

class Subaccount implements ApiDataMapperInterface
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
        } else {
            $this->attributes['business_name'] = $business_name;
            $this->attributes['settlement_bank'] = $settlement_bank;
            $this->attributes['account_number'] = $account_number;
            $this->attributes['percentage_charge'] = $percentage_charge;
        }
    }

    public function save()
    {
        $this->subaccount_code = $this->paymentProvider->saveSubaccount($this->attributes);
        return $this->subaccount_code;
    }

    public static function fetchAll($query_params = [])
    {
        $subaccounts_data = PaymentProviderFactory::getPaymentProvider()->fetchAllSubaccounts($query_params);
        $subaccounts = [];
        if ($subaccounts_data == null) {
            return $subaccounts_data;
        }
        foreach ($subaccounts_data as $subaccount_data) {
            $subaccounts[] = new Subaccount($subaccount_data);
        }

        return $subaccounts;
    }

    public static function fetch($subaccount_id)
    {
        $subaccount_details = PaymentProviderFactory::getPaymentProvider()->fetchSubaccount($subaccount_id);

        if ($subaccount_details == null) {
            return $subaccount_details;
        }

        return new Subaccount($subaccount_details);
    }

    public static function delete($subaccount_id)
    {
        return PaymentProviderFactory::getPaymentProvider()->deleteSubaccount($subaccount_id);
    }

    public static function fetchBanks($query_params = [])
    {
        return PaymentProviderFactory::getPaymentProvider()->fetchBanks($query_params);
    }
}
