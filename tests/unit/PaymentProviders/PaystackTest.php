<?php

namespace Metav\NgPayments\Tests\unit\PaymentProviders;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
use Metav\NgPayments\Exceptions\FailedTransactionException;
use Metav\NgPayments\Exceptions\InvalidRequestBodyException;
use Metav\NgPayments\PaymentProviders\Paystack;
use Metav\NgPayments\Tests\unit\Mocks\MockHttpClient;
use Metav\NgPayments\Tests\unit\Mocks\MockPaystackApiResponse;
use PHPUnit\Framework\TestCase;

class PaystackTest extends TestCase
{
    private $paystack = null;
    private $mockHttpClient = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->paystack = new Paystack('public', 'secret', 'testing');
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient());
    }

    public function testInitializePayment()
    {
        //it successfully adapts provided request params to the paystack api
        $this->paystack->initializePayment([
            'customer_email' => 'customer@example.com', //paystack expects email not customer_email as the param
            'naira_amount' => 3000 //amount in naira should be converted to kobo
        ]);
        $sent_request = MockHttpClient::getRecentRequest();
        $sent_request_body = MockHttpClient::getRecentRequestBody();

        $this->assertEquals('customer@example.com', $sent_request_body['email']);
        $this->assertEquals(300000, $sent_request_body['amount']);
        $this->assertEquals('Bearer secret', $sent_request->getHeader('authorization')[0]);
        $this->assertEquals('/initialize', $sent_request->getUri()->getPath());

        MockHttpClient::appendResponsesToMockHttpClient([new Response(200)]);
        $this->paystack->initializePayment([
            'customer_email' => 'customer@example.com', //paystack expects email not customer_email as the param
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
            MockPaystackApiResponse::getSuccessfulInitializePaymentResponse()
        ]));
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertEquals('https://example.com/mock_checkout', $this->paystack->getPaymentPageUrl());
    }

    public function testGetPaymentReference()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulInitializePaymentResponse()
        ]));
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertEquals('mock_reference', $this->paystack->getPaymentReference());
    }

    public function testIsPaymentValid()
    {
        $responses = [
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse(),
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse(),
            MockPaystackApiResponse::getFailedVerifyPaymentResponse(),
            MockPaystackApiResponse::getFailedVerifyPaymentResponse(),
        ];
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient($responses));

        //Without Exceptions
        $this->paystack->disableTransactionExceptions();

        //successful verification response
        $this->assertTrue($this->paystack->isPaymentValid('mock_reference', 5000));

        //successful verification response but wrong amount
        $this->assertFalse($this->paystack->isPaymentValid('mock_reference', 2000));

        //failed verification response
        $this->assertFalse($this->paystack->isPaymentValid('mock_reference', 5000));

        //With Exceptions
        $this->paystack->enableTransactionExceptions();

        //failed verfication response
        $this->expectException(FailedTransactionException::class);
        $this->paystack->isPaymentValid('mock_reference', 5000);
    }

    public function testIsPaymentValidThrowsFailedTransactionExceptionWithWrongAmount()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse()
        ]));

        $this->expectException(FailedTransactionException::class);
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
        $this->paystack->disableTransactionExceptions();

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

        $this->paystack->enableTransactionExceptions();
        $this->expectException(FailedTransactionException::class);
        $reference = $this->paystack->chargeAuth([
            'email' => 'customer@email.com',
            'amount' => 30000,
            'authorization_code' => 'mock_authorization_code'
        ]);
    }

    public function testGetAuthorizationCode()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse()
        ]));
        $this->assertTrue($this->paystack->isPaymentValid('mock_reference', 5000));
        $this->assertEquals('mock_authorization_code', $this->paystack->getAuthorizationCode());

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $this->paystack->disableTransactionExceptions();
        $this->paystack->isPaymentValid('mock_reference', 5000);
        $this->assertEquals('', $this->paystack->getAuthorizationCode());
    }

    public function testSavePlan()
    {
        //test create
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulCreatePlanResponse()
        ]));

        $plan_code = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000, "interval" => "weekly"]);
        $this->assertEquals("plan_code", $plan_code);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plan_code = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000, "interval" => "weekly"]);
        $this->assertNull($plan_code);

        //test update
        MockHttpClient::appendResponsesToMockHttpClient([MockPaystackApiResponse::getSuccessfulUpdatePlanResponse()]);
        $plan_code = $this->paystack->savePlan(["plan_code" => 'plan_code', "name" => "Mock Plan"]);
        $this->assertEquals("plan_code", $plan_code);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plan_code = $this->paystack->savePlan(["plan_code" => 'plan_code', "name" => "Mock Plan"]);
        $this->assertNull($plan_code);

        //test invalid request
        $this->expectException(InvalidRequestBodyException::class);
        $plan_code = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000]);
    }

    public function testFetchAllPlans()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulListPlansResponse()
        ]));

        $plans = $this->paystack->fetchAllPlans();
        $this->assertEquals(2, count($plans));

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plans = $this->paystack->fetchAllPlans();
        $this->assertEquals([], $plans);

        $this->paystack->enableHttpExceptions();
        $this->expectException(BadResponseException::class);
        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plan = $this->paystack->fetchAllPlans();
    }

    public function testFetchPlan()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulFetchPlanResponse()
        ]));

        $plan = $this->paystack->fetchPlan("plan_code");
        $this->assertEquals(50000, $plan['amount']);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plan = $this->paystack->fetchPlan("plan_code");
        $this->assertEquals([], $plan);

        $this->paystack->enableHttpExceptions();
        $this->expectException(BadResponseException::class);
        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plan = $this->paystack->fetchPlan("plan_code");
    }

    public function testSaveSubAccount()
    {
        //test create
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulCreateSubAccountResponse()
        ]));
        $subaccount_code = $this->paystack->saveSubAccount([
            "business_name" => "mock business",
            "settlement_bank" => "mock bank",
            "account_number" => "000000000",
            "percentage_charge" => 3
        ]);
        $this->assertEquals('subaccount_code', $subaccount_code);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $subaccount_code = $this->paystack->saveSubAccount([
            "business_name" => "mock business",
            "settlement_bank" => "mock bank",
            "account_number" => "000000000",
            "percentage_charge" => 3
        ]);
        $this->assertEquals(null, $subaccount_code);

        //test update
        MockHttpClient::appendResponsesToMockHttpClient([MockPaystackApiResponse::getSuccessfulUpdatePlanResponse()]);
        $subaccount_code = $this->paystack->saveSubAccount(["subaccount_code" => "subaccount_code"]);
        $this->assertEquals("subaccount_code", $subaccount_code);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $subaccount_code = $this->paystack->saveSubAccount(["subaccount_code" => "subaccount_code"]);
        $this->assertNull($subaccount_code);

        //test invalid request
        $this->expectException(InvalidRequestBodyException::class);
        $this->paystack->saveSubAccount(["settlement_bank" => "mock bank"]);
    }

    public function testFetchAllSubAccounts()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulListSubAccountsResponse()
        ]));
        $subaccounts = $this->paystack->fetchAllSubAccounts();
        $this->assertEquals(3, count($subaccounts));

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $subaccounts = $this->paystack->fetchAllSubAccounts();
        $this->assertEquals([], $subaccounts);

        $this->expectException(BadResponseException::class);
        $this->paystack->enableHttpExceptions();
        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $subaccounts = $this->paystack->fetchAllSubAccounts();
    }

    public function testFetchSubAccount()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulFetchSubAccountResponse()
        ]));
        $subaccount = $this->paystack->fetchSubAccount("subaccount_id");
        $this->assertEquals(55, $subaccount['id']);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $subaccount = $this->paystack->fetchSubAccount("subaccount_id");
        $this->assertEquals([], $subaccount);
    }
}
