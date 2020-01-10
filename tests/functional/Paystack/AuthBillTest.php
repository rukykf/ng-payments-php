<?php

namespace Metav\NgPayments\Tests\functional\Paystack;

use Metav\NgPayments\AuthBill;
use Metav\NgPayments\Exceptions\FailedTransactionException;
use Metav\NgPayments\Plan;
use PHPUnit\Framework\TestCase;

class AuthBillTest extends TestCase
{
    public function testCharge()
    {
        $bill = new AuthBill("AUTH_CODE", "customer@email.com", 3000);

        $payment_provider = &$bill->getPaymentProvider();
        $payment_provider->disableTransactionExceptions();
        $reference = $bill->charge();
        $this->assertNull($reference);

        $payment_provider->enableTransactionExceptions();
        $this->expectException(FailedTransactionException::class);
        $bill->charge();
    }

    public function testSubscribe()
    {
        $plan = new Plan("Test Plan", 4000, "daily");
        $plan->save();
        $bill = new AuthBill("AUTH_CODE", "customer@email.com", 5000);

        $payment_provider = &$bill->getPaymentProvider();
        $payment_provider->disableTransactionExceptions();
        $reference = $bill->subscribe($plan->plan_code);
        $this->assertNull($reference);

        $payment_provider->enableTransactionExceptions();
        $this->expectException(FailedTransactionException::class);
        $bill->subscribe($plan->plan_code);
    }
}
