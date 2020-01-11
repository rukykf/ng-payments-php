<?php


namespace Kofi\NgPayments;

use Kofi\NgPayments\Interfaces\ApiDataMapperInterface;
use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\Traits\AttributesTrait;

class Plan implements ApiDataMapperInterface
{
    use AttributesTrait;

    protected $paymentProvider = null;

    public function __construct($plan_name = null, $amount_in_naira = null, $plan_interval = null)
    {
        $this->paymentProvider = PaymentProviderFactory::getPaymentProvider();
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->attributes = func_get_arg(0);
        } else {
            $this->attributes["name"] = $plan_name;
            $this->attributes["naira_amount"] = $amount_in_naira;
            $this->attributes["interval"] = $plan_interval;
        }
    }

    public function save()
    {
        $this->plan_code = $this->paymentProvider->savePlan($this->attributes);
        return $this->plan_code;
    }

    public static function fetchAll($query_params = [])
    {
        $plans_data = PaymentProviderFactory::getPaymentProvider()->fetchAllPlans($query_params);
        if ($plans_data == null) {
            return $plans_data;
        }

        $plans = [];
        foreach ($plans_data as $plan_data) {
            $plans[] = new Plan($plan_data);
        }

        return $plans;
    }

    public static function fetch($plan_id)
    {
        $plan_details = PaymentProviderFactory::getPaymentProvider()->fetchPlan($plan_id);
        if ($plan_details == null) {
            return $plan_details;
        }
        return new Plan($plan_details);
    }

    public static function delete($plan_id)
    {
        return PaymentProviderFactory::getPaymentProvider()->deletePlan($plan_id);
    }
}
