<?php

namespace Metav\NgPayments\Tests\functional;

use Metav\NgPayments\Exceptions\InvalidRequestBodyException;
use Metav\NgPayments\SubAccount;
use PHPUnit\Framework\TestCase;

class SubAccountTest extends TestCase
{
    public function testSubAccountConstructorOverload()
    {
        $attributes = ["color" => "blue", "shade" => "grey"];

        $subaccount = new SubAccount($attributes);
        $this->assertEquals($attributes, $subaccount->getAttributes());
    }

    public function testCreateSubAccount()
    {
        $subaccount = new SubAccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();
        $this->assertNotNull($subaccount_code);

        $this->expectException(InvalidRequestBodyException::class);
        $sub = new SubAccount();
        $sub->save();
    }

    public function testUpdateSubAccount()
    {
        $subaccount = new SubAccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();
        $this->assertNotNull($subaccount_code);

        $fetched_subaccount = SubAccount::fetch($subaccount_code);
        $this->assertNull($fetched_subaccount->primary_contact_email);
        $this->assertNull($fetched_subaccount->primary_contact_name);
        $this->assertEquals($subaccount_code, $fetched_subaccount->subaccount_code);

        $fetched_subaccount->primary_contact_email = "contact@email.com";
        $fetched_subaccount->primary_contact_name = "Contact Name";
        $fetched_subaccount->save();
        $fetched_subaccount = SubAccount::fetch($subaccount_code);
        $this->assertEquals("contact@email.com", $fetched_subaccount->primary_contact_email);
        $this->assertEquals("Contact Name", $fetched_subaccount->primary_contact_name);

        $subaccount = new SubAccount();
        $subaccount->id = "UNKNOWN";
        $this->assertNull($subaccount->save());
    }

    public function testFetchAllSubAccounts()
    {
        $subaccount = new SubAccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();

        $subaccounts = SubAccount::fetchAll();
        $this->assertNotNull($subaccounts);
        $this->assertContainsOnlyInstancesOf(SubAccount::class, $subaccounts);
    }

    public function testFetchSubAccount(){
        $subaccount = new SubAccount("Test Business", "Zenith Bank", '0000000000', 3);
        $subaccount_code = $subaccount->save();

        $subaccount = SubAccount::fetch($subaccount_code);
        $this->assertEquals("Zenith Bank", $subaccount->settlement_bank);
    }
}
