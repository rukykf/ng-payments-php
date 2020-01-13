<?php

namespace Kofi\NgPayments\Tests\functional\Rave;

use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\SubAccount;
use PHPUnit\Framework\TestCase;

/**
 * Rave does not provide test bank accounts for use in the creation of SubAccounts in their sandbox
 * so there's not much that can be tested here
 *
 * Class SubAccountTest
 * @package Kofi\NgPayments\Tests\functional\Rave
 */
class SubAccountTest extends TestCase
{
    protected function setUp()
    {
        PaymentProviderFactory::setPaymentProviderConfig(['provider' => 'flutterwave']);
    }

    public function testFetchBanks()
    {
        $banks = SubAccount::fetchBanks();
        $this->assertNotNull($banks);
    }
}
