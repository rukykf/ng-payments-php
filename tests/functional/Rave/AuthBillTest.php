<?php

namespace Kofi\NgPayments\Tests\functional\Rave;

use Kofi\NgPayments\AuthBill;
use Kofi\NgPayments\Exceptions\FailedPaymentException;
use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\Plan;
use PHPUnit\Framework\TestCase;

class AuthBillTest extends TestCase
{
    protected function setUp()
    {
        PaymentProviderFactory::setPaymentProviderConfig(['provider' => 'flutterwave']);
    }

    public function testCharge()
    {
        $bill = new AuthBill("AUTH_CODE", "customer@email.com", 3000);
        $payment_provider = &$bill->getPaymentProvider();
        $payment_provider->disablePaymentExceptions();
        $reference = $bill->charge();
        $this->assertNull($reference);

        $payment_provider->enablePaymentExceptions();
        $this->expectException(FailedPaymentException::class);
        $bill->charge();
    }

    public function testSubscribe()
    {
        $plan = new Plan("Test Plan", 4000, "daily");
        $plan->save();
        $bill = new AuthBill("AUTH_CODE", "customer@email.com", 5000);
        $payment_provider = &$bill->getPaymentProvider();
        $payment_provider->disablePaymentExceptions();
        $reference = $bill->subscribe($plan->plan_code);
        $this->assertNull($reference);

        $payment_provider->enablePaymentExceptions();
        $this->expectException(FailedPaymentException::class);
        $bill->subscribe($plan->plan_code);
    }

}
