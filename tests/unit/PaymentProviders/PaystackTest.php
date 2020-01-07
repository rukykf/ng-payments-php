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
            'amount' => 3000 //amount in naira should be converted to kobo
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

    public function testVerifyPayment()
    {
        //successful verification
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulVerifyPaymentResponse(),
        ]));
        $this->assertEquals('success', $this->paystack->verifyPayment('mock_reference'));

        //failed verification without exceptions
        $this->paystack->disableTransactionExceptions();
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getFailedVerifyPaymentResponse(),
            MockPaystackApiResponse::getFailedVerifyPaymentResponse()
        ]));
        $this->assertEquals('failed', $this->paystack->verifyPayment('mock_reference'));

        //failed verification with exceptions
        $this->paystack->enableTransactionExceptions();
        $this->expectException(FailedTransactionException::class);
        $this->paystack->verifyPayment('mock_reference');
    }

    public function testSavePlan()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulCreatePlanResponse()
        ]));

        $plan_id = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000, "interval" => "weekly"]);
        $this->assertEquals(37425, $plan_id);

        MockHttpClient::appendResponsesToMockHttpClient([new Response(404)]);
        $plan_id = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000, "interval" => "weekly"]);
        $this->assertNull($plan_id);

        $this->expectException(InvalidRequestBodyException::class);
        $plan_id = $this->paystack->savePlan(["name" => "Mock Plan", "amount" => 30000]);
    }

    public function testListPlans()
    {
        $this->paystack->setHttpClient(MockHttpClient::getHttpClient([
            MockPaystackApiResponse::getSuccessfulListPlansResponse()
        ]));

        $plans = $this->paystack->listPlans();
        $this->assertEquals(2, count($plans));
    }
}
