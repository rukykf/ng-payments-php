<?php

namespace Kofi\NgPayments\Tests\unit\PaymentProviders;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
use Kofi\NgPayments\Exceptions\FailedPaymentException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\PaymentProviders\Paystack;
use Kofi\NgPayments\Tests\unit\Mocks\MockHttpClient;
use Kofi\NgPayments\Tests\unit\Mocks\MockPaystackApiResponse;
use PHPUnit\Framework\TestCase;

class PaystackTest extends TestCase
{
    private $paystack = null;

    public function setUp(): void
    {
        $this->paystack = new Paystack('public', 'secret', 'testing');
    }

    public function testInitializePayment()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulInitializePaymentResponse(),
            MockPaystackApiResponse::getSuccessfulInitializePaymentResponse()
        ]));

        //it successfully adapts provided request params to the paystack api
        $this->paystack->initializePayment([
            'customer_email' => 'customer@example.com', //paystack expects email not customer_email as the param
            'naira_amount' => 3000, //amount in naira should be converted to kobo
            'subaccount_code' => 'mock_subaccount' //this endpoint expects subaccount
        ]);
        $sent_request = MockHttpClient::getRecentRequest();
        $sent_request_body = MockHttpClient::getRecentRequestBody();

        $this->assertEquals('customer@example.com', $sent_request_body['email']);
        $this->assertEquals(300000, $sent_request_body['amount']);
        $this->assertEquals('mock_subaccount', $sent_request_body['subaccount']);
        $this->assertEquals('Bearer secret', $sent_request->getHeader('authorization')[0]);
        $this->assertEquals('/transaction/initialize', $sent_request->getUri()->getPath());


        $this->paystack->initializePayment([
            'customer_email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $sent_request_body = MockHttpClient::getRecentRequestBody();
        $this->assertEquals(3000, $sent_request_body['amount']);

        //it throws an exception when required parameters are not provided
        $this->expectException(InvalidRequestBodyException::class);
        $this->paystack->initializePayment(['amount' => 3000]);
    }

    public function testInitializePaymentThrowsHttpExceptions()
    {
        $expected_response = new Response(401);
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            $expected_response,
            $expected_response
        ]));

        //httpExceptions disabled
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertEquals($expected_response, $this->paystack->getHttpResponse());

        //httpExceptions enabled
        $this->paystack->enableHttpExceptions();
        $this->expectException(BadResponseException::class);
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
    }

    public function testGetPaymentPageUrl()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulInitializePaymentResponse(),
            new Response(404)
        ]));

        //success
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertEquals('https://example.com/mock_checkout', $this->paystack->getPaymentPageUrl());

        //fail
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertNull($this->paystack->getPaymentPageUrl());
    }

    public function testGetPaymentReference()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulInitializePaymentResponse(),
            new Response(404)
        ]));

        //success
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertEquals('mock_reference', $this->paystack->getPaymentReference());

        //fail
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertNull($this->paystack->getPaymentReference());
    }

    public function testIsPaymentValid()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse(),
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse(),
            MockPaystackApiResponse::getFailedVerifyPaymentResponse(),
            MockPaystackApiResponse::getFailedVerifyPaymentResponse(),
        ]));

        //Without Exceptions
        $this->paystack->disablePaymentExceptions();

        //successful verification response
        $this->assertTrue($this->paystack->isPaymentValid('mock_reference', 5000));

        //successful verification response but wrong amount
        $this->assertFalse($this->paystack->isPaymentValid('mock_reference', 2000));

        //failed verification response
        $this->assertFalse($this->paystack->isPaymentValid('mock_reference', 5000));

        //With Exceptions
        $this->paystack->enablePaymentExceptions();

        //failed verfication response
        $this->expectException(FailedPaymentException::class);
        $this->paystack->isPaymentValid('mock_reference', 5000);
    }

    public function testIsPaymentValidThrowsFailedPaymentExceptionWithWrongAmount()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse()
        ]));

        $this->expectException(FailedPaymentException::class);
        $this->paystack->isPaymentValid('mock_reference', 2000);
    }

    public function testChargeAuth()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulChargeAuthResponse(),
            MockPaystackApiResponse::getFailedChargeAuthResponse(),
            MockPaystackApiResponse::getFailedChargeAuthResponse()
        ]));

        //without exceptions
        $this->paystack->disablePaymentExceptions();

        //success
        $reference = $this->paystack->chargeAuth([
            'email' => 'customer@email.com',
            'amount' => 30000,
            'authorization_code' => 'mock_authorization_code'
        ]);
        $this->assertEquals('mock_reference', $reference);

        //failed
        $reference = $this->paystack->chargeAuth([
            'email' => 'customer@email.com',
            'amount' => 30000,
            'authorization_code' => 'mock_authorization_code'
        ]);
        $this->assertNull($reference);

        //with exceptions
        $this->paystack->enablePaymentExceptions();
        $this->expectException(FailedPaymentException::class);
        $reference = $this->paystack->chargeAuth([
            'email' => 'customer@email.com',
            'amount' => 30000,
            'authorization_code' => 'mock_authorization_code'
        ]);
    }

    public function testGetPaymentAuthorizationCode()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse(),
            new Response(404)
        ]));
        $this->assertTrue($this->paystack->isPaymentValid('mock_reference', 5000));
        $this->assertEquals('mock_authorization_code', $this->paystack->getPaymentAuthorizationCode());

        $this->paystack->disablePaymentExceptions();
        $this->paystack->isPaymentValid('mock_reference', 5000);
        $this->assertNull($this->paystack->getPaymentAuthorizationCode());
    }

    public function testSavePlan()
    {
        //test create
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulCreatePlanResponse(),
            new Response(404),
            MockPaystackApiResponse::getSuccessfulUpdatePlanResponse(),
            new Response(404),
        ]));

        $plan_code = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000, "interval" => "weekly"]);
        $this->assertEquals("plan_code", $plan_code);

        $plan_code = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000, "interval" => "weekly"]);
        $this->assertNull($plan_code);

        //test update
        $plan_code = $this->paystack->savePlan(["plan_code" => 'plan_code', "name" => "Mock Plan"]);
        $this->assertEquals("plan_code", $plan_code);

        $plan_code = $this->paystack->savePlan(["plan_code" => 'plan_code', "name" => "Mock Plan"]);
        $this->assertNull($plan_code);

        //test invalid request
        $this->expectException(InvalidRequestBodyException::class);
        $plan_code = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000]);
    }

    public function testFetchAllPlans()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulFetchAllPlansResponse(),
            new Response(404),
            new Response(404)
        ]));

        $plans = $this->paystack->fetchAllPlans();
        $this->assertEquals(2, count($plans));

        $plans = $this->paystack->fetchAllPlans();
        $this->assertNull($plans);

        $this->paystack->enableHttpExceptions();
        $this->expectException(BadResponseException::class);
        $plan = $this->paystack->fetchAllPlans();
    }

    public function testFetchPlan()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulFetchPlanResponse(),
            new Response(404),
            new Response(404)
        ]));

        $plan = $this->paystack->fetchPlan("plan_code");
        $this->assertEquals(50000, $plan['amount']);

        $plan = $this->paystack->fetchPlan("plan_code");
        $this->assertNull($plan);

        $this->paystack->enableHttpExceptions();
        $this->expectException(BadResponseException::class);
        $this->paystack->fetchPlan("plan_code");
    }

    public function testSaveSubaccount()
    {
        //test create
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulCreateSubAccountResponse(),
            new Response(404),
            MockPaystackApiResponse::getSuccessfulUpdateSubAccountResponse(),
            new Response(404)
        ]));
        $subaccount_code = $this->paystack->saveSubaccount([
            "business_name" => "mock business",
            "settlement_bank" => "mock bank",
            "account_number" => "000000000",
            "percentage_charge" => 3
        ]);
        $this->assertEquals('subaccount_code', $subaccount_code);

        $subaccount_code = $this->paystack->saveSubaccount([
            "business_name" => "mock business",
            "settlement_bank" => "mock bank",
            "account_number" => "000000000",
            "percentage_charge" => 3
        ]);
        $this->assertEquals(null, $subaccount_code);

        //test update
        $subaccount_code = $this->paystack->saveSubaccount(["subaccount_code" => "subaccount_code"]);
        $this->assertEquals("subaccount_code", $subaccount_code);

        $subaccount_code = $this->paystack->saveSubaccount(["subaccount_code" => "subaccount_code"]);
        $this->assertNull($subaccount_code);

        //test invalid request
        $this->expectException(InvalidRequestBodyException::class);
        $this->paystack->saveSubaccount(["settlement_bank" => "mock bank"]);
    }

    public function testFetchAllSubaccounts()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulFetchAllSubAccountsResponse(),
            new Response(404),
            new Response(404)
        ]));
        $subaccounts = $this->paystack->fetchAllSubaccounts();
        $this->assertEquals(3, count($subaccounts));

        $subaccounts = $this->paystack->fetchAllSubaccounts();
        $this->assertNull($subaccounts);

        $this->expectException(BadResponseException::class);
        $this->paystack->enableHttpExceptions();
        $this->paystack->fetchAllSubaccounts();
    }

    public function testFetchSubaccount()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulFetchSubAccountResponse(),
            new Response(404)
        ]));
        $subaccount = $this->paystack->fetchSubaccount("subaccount_id");
        $this->assertEquals(55, $subaccount['id']);

        $subaccount = $this->paystack->fetchSubaccount("subaccount_id");
        $this->assertNull($subaccount);
    }
}
