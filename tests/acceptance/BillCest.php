<?php

use Metav\NgPayments\Bill;

class BillCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function testSuccessfulPaymentCollection(AcceptanceTester $I)
    {
        $bill = new Bill("customer@email.com", 7000);
        $payment_page_url = $bill->splitCharge("ACCT_t6t7s6id243cy68")->getPaymentPageUrl();
        $I->amOnUrl($payment_page_url);
    }

    public function testSuccessfulPaymentVerification(AcceptanceTester $I)
    {
        $reference = 'c4o3u2m6ce';
        $amount = 7000;
        $is_valid = Bill::isPaymentValid($reference, $amount);
        $authorization_code = Bill::getPaymentAuthorizationCode($reference, $amount);
        $x = $is_valid;
    }
}
