<?php

namespace Kofi\NgPayments\Tests\functional\Paystack;

use Kofi\NgPayments\Exceptions\FeatureNotSupportedException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\Subaccount;
use PHPUnit\Framework\TestCase;

class SubaccountTest extends TestCase
{
    public function testSubaccountConstructorOverload()
    {
        $attributes = [
            "business_name" => "test business",
            "settlement_bank" => "a bank",
            "account_number" => "000000000",
            "percentage_charge" => 3
        ];

        $subaccount = new Subaccount($attributes);
        $this->assertEquals($attributes, $subaccount->getAttributes());
    }

    public function testCreateSubaccount()
    {
        $subaccount = new Subaccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();
        $this->assertNotNull($subaccount_code);

        $this->expectException(InvalidRequestBodyException::class);
        $sub = new Subaccount();
        $sub->save();
    }

    public function testUpdateSubaccount()
    {
        $subaccount = new Subaccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();
        $this->assertNotNull($subaccount_code);

        $fetched_subaccount = Subaccount::fetch($subaccount_code);
        $this->assertNull($fetched_subaccount->primary_contact_email);
        $this->assertNull($fetched_subaccount->primary_contact_name);
        $this->assertEquals($subaccount_code, $fetched_subaccount->subaccount_code);

        $fetched_subaccount->primary_contact_email = "contact@email.com";
        $fetched_subaccount->primary_contact_name = "Contact Name";
        $fetched_subaccount->save();
        $fetched_subaccount = Subaccount::fetch($subaccount_code);
        $this->assertEquals("contact@email.com", $fetched_subaccount->primary_contact_email);
        $this->assertEquals("Contact Name", $fetched_subaccount->primary_contact_name);

        $subaccount = new Subaccount();
        $subaccount->id = "UNKNOWN";
        $this->assertNull($subaccount->save());
    }

    public function testFetchAllSubaccounts()
    {
        //to ensure there's at least one subaccount
        $subaccount = new Subaccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();

        $subaccounts = Subaccount::fetchAll();
        $this->assertNotNull($subaccounts);
        $this->assertContainsOnlyInstancesOf(Subaccount::class, $subaccounts);
    }

    public function testFetchSubaccount()
    {
        $subaccount = new Subaccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();

        $subaccount = Subaccount::fetch($subaccount_code);
        $this->assertEquals("Zenith Bank", $subaccount->settlement_bank);

        $subaccount = Subaccount::fetch("Invalid Subaccount");
        $this->assertNull($subaccount);
    }

    public function testDeleteSubaccount()
    {
        $subaccount = new Subaccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();
        $this->expectException(FeatureNotSupportedException::class);
        Subaccount::delete($subaccount_code);
    }

    public function testFetchBanks()
    {
        $banks = Subaccount::fetchBanks();
        $this->assertNotNull($banks);
    }
}
