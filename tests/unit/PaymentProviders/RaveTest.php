<?php

namespace Kofi\NgPayments\Tests\unit\PaymentProviders;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
use Kofi\NgPayments\Exceptions\FailedPaymentException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\PaymentProviders\Rave;
use Kofi\NgPayments\Tests\unit\Mocks\MockHttpClient;
use Kofi\NgPayments\Tests\unit\Mocks\MockRaveApiResponse;
use PHPUnit\Framework\TestCase;

class RaveTest extends TestCase
{
    private $rave = null;

    public function setUp()
    {
        $this->rave = new Rave('public', 'secret', 'testing');
    }

    public function testInitializePayment()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulInitializePaymentResponse(),
            MockRaveApiResponse::getSuccessfulInitializePaymentResponse()
        ]));

        $this->rave->initializePayment([
            "customer_email" => "customer@email.com",
            "naira_amount" => 3000
        ]);
        $sent_request = MockHttpClient::getRecentRequest();
        $sent_request_body = MockHttpClient::getRecentRequestBody();

        $this->assertEquals("customer@email.com", $sent_request_body["customer_email"]);
        $this->assertEquals(3000, $sent_request_body["amount"]);
        $this->assertEquals("public", $sent_request_body["PBFPubKey"]);
        $this->assertEquals("NGN", $sent_request_body["currency"]);
        $this->assertNotNull($sent_request_body["txref"]);
        $this->assertEquals(
            "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/hosted/pay",
            $sent_request->getUri()->__toString()
        );
        $this->rave->initializePayment([
            "customer_email" => "customer@email.com",
            "naira_amount" => 3000,
            "amount" => 2000,
            "plan_code" => "plan_code",
            "subaccount_code" => "subaccount_code"
        ]);
        $sent_request_body = MockHttpClient::getRecentRequestBody();
        $this->assertEquals(2000, $sent_request_body["amount"]);
        $this->assertEquals("plan_code", $sent_request_body["payment_plan"]);
        $this->assertEquals("subaccount_code", $sent_request_body["subaccounts"][0]["id"]);

        $this->expectException(InvalidRequestBodyException::class);
        $this->rave->initializePayment(["customer_email" => "customer@email.com"]);
    }

    public function testInitializePaymentThrowsHttpExceptions()
    {
        $this->rave->enableHttpExceptions();
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            new Response(404)
        ]));

        $this->expectException(BadResponseException::class);
        $this->rave->initializePayment([
            "customer_email" => "customer@email.com",
            "naira_amount" => 3000
        ]);

    }

    public function testIsPaymentValid()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulVerifyPaymentResponse(),
            MockRaveApiResponse::getSuccessfulVerifyPaymentResponse(),
            MockRaveApiResponse::getFailedVerifyPaymentResponse(),
            MockRaveApiResponse::getFailedVerifyPaymentResponse(),
        ]));

        $this->rave->disablePaymentExceptions();
        $is_valid = $this->rave->isPaymentValid("mock_reference", 5000);
        $this->assertTrue($is_valid);

        $is_valid = $this->rave->isPaymentValid("mock_reference", 3000);
        $this->assertFalse($is_valid);

        $is_valid = $this->rave->isPaymentValid("mock_reference", 5000);
        $this->assertFalse($is_valid);

        $this->rave->enablePaymentExceptions();
        $this->expectException(FailedPaymentException::class);
        $this->rave->isPaymentValid("mock_reference", 5000);
    }

    public function testChargeAuth()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulChargeAuthResponse(),
            MockRaveApiResponse::getFailedChargeAuthResponse(),
            MockRaveApiResponse::getFailedChargeAuthResponse()
        ]));

        $this->rave->disablePaymentExceptions();
        $reference = $this->rave->chargeAuth([
            "naira_amount" => 3000,
            "authorization_code" => "mock_token",
            "customer_email" => "customer@email.com",
            "subaccount_code" => "subaccount_code"
        ]);
        $sent_request_body = MockHttpClient::getRecentRequestBody();
        $this->assertEquals("customer@email.com", $sent_request_body["email"]);
        $this->assertEquals("mock_token", $sent_request_body["token"]);
        $this->assertEquals(3000, $sent_request_body["amount"]);
        $this->assertEquals("mock_reference", $reference);
        $this->assertEquals("subaccount_code", $sent_request_body["subaccounts"][0]["id"]);

        $reference = $this->rave->chargeAuth([
            "naira_amount" => 3000,
            "authorization_code" => "mock_token",
            "customer_email" => "customer@email.com"
        ]);
        $this->assertNull($reference);

        $this->rave->enablePaymentExceptions();
        $this->expectException(FailedPaymentException::class);
        $reference = $this->rave->chargeAuth([
            "naira_amount" => 3000,
            "authorization_code" => "mock_token",
            "customer_email" => "customer@email.com"
        ]);
    }

    public function testCreatePlan()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulCreatePlanResponse(),
            new Response(400),
            new Response(400)
        ]));

        $plan_id = $this->rave->savePlan([
            "naira_amount" => 3000,
            "name" => "Test Plan",
            "interval" => "weekly"
        ]);
        $this->assertEquals(3704, $plan_id);

        $plan_id = $this->rave->savePlan([
            "naira_amount" => 3000,
            "name" => "Test Plan",
            "interval" => "weekly"
        ]);
        $this->assertEquals(null, $plan_id);

        $this->rave->enableHttpExceptions();
        $this->expectException(BadResponseException::class);
        $this->rave->savePlan([
            "naira_amount" => 3000,
            "name" => "Test Plan",
            "interval" => "weekly"
        ]);
    }

    public function testUpdatePlan()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulUpdatePlanResponse(),
            new Response(400),
        ]));

        $plan_id = $this->rave->savePlan([
            "plan_code" => "mock_plan_code",
            "naira_amount" => 3000,
            "name" => "Test Plan",
            "interval" => "weekly"
        ]);
        $sent_request_body = MockHttpClient::getRecentRequestBody();
        $this->assertEquals("mock_plan_code", $sent_request_body["id"]);
        $this->assertEquals(3707, $plan_id);

        $plan_id = $this->rave->savePlan([
            "plan_code" => "mock_plan_code",
            "naira_amount" => 3000,
            "name" => "Test Plan",
            "interval" => "weekly"
        ]);
        $this->assertNull($plan_id);
    }

    public function testFetchPlan()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulFetchPlanResponse(),
            new Response(400)
        ]));

        $plan = $this->rave->fetchPlan(3707);
        $this->assertEquals("active", $plan["status"]);
        $this->assertEquals("NGN", $plan["currency"]);

        $plan = $this->rave->fetchPlan(3707);
        $this->assertNull($plan);
    }

    public function testFetchAllPlans()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulFetchAllPlansResponse(),
            new Response(400)
        ]));

        $plans = $this->rave->fetchAllPlans();
        $this->assertEquals(4, count($plans));

        $plans = $this->rave->fetchAllPlans();
        $this->assertNull($plans);
    }

    public function testCreateSubaccount()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulCreateSubAccountResponse(),
            new Response(400)
        ]));

        $subaccount_id = $this->rave->saveSubaccount([
            "business_name" => "Test Business",
            "settlement_bank" => "044",
            "account_number" => "000000000",
            "business_mobile" => 9399999999,
            "business_email" => "business@email.com",
            "percentage_charge" => 4
        ]);
        $sent_request_body = MockHttpClient::getRecentRequestBody();
        $this->assertEquals("044", $sent_request_body["account_bank"]);
        $this->assertEquals(4, $sent_request_body["split_value"]);
        $this->assertEquals("percentage", $sent_request_body["split_type"]);
        $this->assertEquals("mock_subaccount_id", $subaccount_id);

        $subaccount_id = $this->rave->saveSubaccount([
            "business_name" => "Test Business",
            "settlement_bank" => "044",
            "account_number" => "000000000",
            "business_mobile" => 9399999999,
            "business_email" => "business@email.com",
            "percentage_charge" => 4
        ]);
        $this->assertNull($subaccount_id);
    }

    public function testUpdateSubaccount()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulFetchSubAccountResponse(),
            MockRaveApiResponse::getSuccessfulUpdateSubAccountResponse(),
            MockRaveApiResponse::getSuccessfulUpdateSubAccountResponse(),
            new Response(400)
        ]));

        $subaccount_id = $this->rave->saveSubaccount([
            "subaccount_code" => "mock_subaccount_id",
            "business_name" => "Test Business"
        ]);
        $sent_request_body = MockHttpClient::getRecentRequestBody();
        $this->assertEquals("mock_subaccount_id", $subaccount_id);
        $this->assertEquals(2115, $sent_request_body["id"]);

        $subaccount_id = $this->rave->saveSubaccount([
            "subaccount_code" => 2115,
            "business_name" => "Test Business"
        ]);
        $this->assertEquals("mock_subaccount_id", $subaccount_id);


        $subaccount_id = $this->rave->saveSubaccount([
            "subaccount_code" => 2115,
            "business_name" => "Test Business"
        ]);
        $this->assertNull($subaccount_id);
    }

    public function testFetchSubaccount()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulFetchSubAccountResponse(),
            new Response(400)
        ]));

        $subaccount = $this->rave->fetchSubaccount("mock_subaccount_id");
        $this->assertEquals("1234567890", $subaccount["account_number"]);

        $subaccount = $this->rave->fetchSubaccount("mock_subaccount_id");
        $this->assertNull($subaccount);
    }

    public function testFetchAllSubaccounts()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulFetchAllSubAccountsResponse(),
            new Response(400)
        ]));

        $subaccounts = $this->rave->fetchAllSubaccounts();
        $this->assertEquals(4, count($subaccounts));

        $subaccounts = $this->rave->fetchAllSubaccounts();
        $this->assertNull($subaccounts);
    }

    public function testDeleteSubaccount()
    {
        $this->rave->setHttpClient(MockHttpClient::getHttpClient([
            MockRaveApiResponse::getSuccessfulDeleteSubAccountResponse(),
            new Response(400)
        ]));

        $result = $this->rave->deleteSubaccount("mock_subaccount_id");
        $this->assertEquals("success", $result);

        $result = $this->rave->deleteSubaccount("mock_subaccount_id");
        $this->assertNull($result);
    }
}
