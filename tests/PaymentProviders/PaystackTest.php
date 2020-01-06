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

    public function setUp() : void
    {
        parent::setUp();
        $this->paystack = new Paystack('public', 'secret', 'testing');
        $this->mockHttpClient = new MockHttpClient();
        $this->paystack->setHttpClient($this->mockHttpClient->getMockHttpClient());
    }

    public function testInitializePayment()
    {
        //it successfully adapts provided request params to the paystack api
        $this->paystack->initializePayment([
            'customer_email' => 'customer@example.com', //paystack expects email not customer_email as the param
            'amount' => 3000 //amount in naira should be converted to kobo
        ]);
        $sent_request = $this->mockHttpClient->getRecentRequest();
        $sent_request_body = $this->mockHttpClient->getRecentRequestBody();

        $this->assertEquals('customer@example.com', $sent_request_body['email']);
        $this->assertEquals(300000, $sent_request_body['amount']);
        $this->assertEquals('Bearer secret', $sent_request->getHeader('authorization')[0]);
        $this->assertEquals('/initialize', $sent_request->getUri()->getPath());


        //it throws an exception when required parameters are not provided
        $this->expectException(InvalidRequestBodyException::class);
        $this->paystack->initializePayment(['amount' => 3000]);
    }

    public function testInitializePaymentThrowsHttpExceptions()
    {
        $expected_response = new Response(401);
        $this->paystack->setHttpClient($this->mockHttpClient->getMockHttpClient([
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
        $this->paystack->setHttpClient($this->mockHttpClient->getMockHttpClient([
            MockPaystackApiResponse::getInitializePaymentSuccessResponse()
        ]));
        $this->paystack->initializePayment([
            'email' => 'customer@example.com',
            'amount' => 3000
        ]);
        $this->assertEquals('https://example.com/mock_checkout', $this->paystack->getPaymentPageUrl());
    }

    public function testGetPaymentReference()
    {
        $this->paystack->setHttpClient($this->mockHttpClient->getMockHttpClient([
            MockPaystackApiResponse::getInitializePaymentSuccessResponse()
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
        $this->paystack->setHttpClient($this->mockHttpClient->getMockHttpClient([
            MockPaystackApiResponse::getSuccessfulPaymentVerificationResponse(),
        ]));
        $this->assertEquals('success', $this->paystack->verifyPayment('mock_reference'));

        //failed verification without exceptions
        $this->paystack->disableTransactionExceptions();
        $this->paystack->setHttpClient($this->mockHttpClient->getMockHttpClient([
            MockPaystackApiResponse::getFailedPaymentVerificationResponse(),
            MockPaystackApiResponse::getFailedPaymentVerificationResponse()
        ]));
        $this->assertEquals('failed', $this->paystack->verifyPayment('mock_reference'));

        //failed verification with exceptions
        $this->paystack->enableTransactionExceptions();
        try {
            $this->paystack->verifyPayment('mock_reference');
        } catch (FailedTransactionException $e) {
            $this->assertEquals('Transaction Failed. Check Response Body for details', $e->getMessage());
        }
    }
}
