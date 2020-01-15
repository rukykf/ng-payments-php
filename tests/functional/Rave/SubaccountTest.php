<?php

namespace Kofi\NgPayments\Tests\functional\Rave;

use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\Subaccount;
use PHPUnit\Framework\TestCase;

/**
 * Rave does not provide test bank accounts for use in the creation of SubAccounts in their sandbox
 * so there's not much that can be tested here
 *
 * Class SubaccountTest
 * @package Kofi\NgPayments\Tests\functional\Rave
 */
class SubaccountTest extends TestCase
{
    protected function setUp()
    {
        PaymentProviderFactory::setPaymentProviderConfig(['provider' => 'flutterwave']);
    }

    public function testFetchBanks()
    {
        $banks = Subaccount::fetchBanks();
        $this->assertNotNull($banks);
    }
}
