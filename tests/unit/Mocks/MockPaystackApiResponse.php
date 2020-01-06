<?php


namespace Metav\NgPayments\Tests\unit\Mocks;

use GuzzleHttp\Psr7\Response;

class MockPaystackApiResponse
{
    public static function getInitializePaymentSuccessResponse()
    {
        $response_body = [
            "status" => true,
            "message" => "Authorization URL created",
            "data" => [
                "authorization_url" => "https://example.com/mock_checkout",
                "access_code" => "mock_access_code",
                "reference" => "mock_reference"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulPaymentVerificationResponse()
    {
        $response_body = [
            "status" => true,
            "message" => "Verification successful",
            "data" => [
                "id" => 00000000,
                "domain" => "test",
                "status" => "success",
                "reference" => "mock_reference",
                "amount" => 500000,
                "message" => null,
                "gateway_response" => "Successful",
                "paid_at" => "2020-01-04T15:42:52.000Z",
                "created_at" => "2020-01-04T14:35:51.000Z",
                "channel" => "card",
                "currency" => "NGN",
                "ip_address" => "",
                "metadata" => "",
                "log" => [
                    "start_time" => 1578152563,
                    "time_spent" => 9,
                    "attempts" => 1,
                    "errors" => 0,
                    "success" => true,
                    "mobile" => false,
                    "input" => [],
                    "history" => [
                        [
                            "type" => "action",
                            "message" => "Attempted to pay with card",
                            "time" => 8
                        ],
                        [
                            "type" => "success",
                            "message" => "Successfully paid with card",
                            "time" => 9
                        ]
                    ]
                ],
                "fees" => 17500,
                "fees_split" => null,
                "authorization" => [
                    "authorization_code" => "mock_authorization_code",
                    "bin" => "000000",
                    "last4" => "0000",
                    "exp_month" => "12",
                    "exp_year" => "2020",
                    "channel" => "card",
                    "card_type" => "visa DEBIT",
                    "bank" => "Test Bank",
                    "country_code" => "NG",
                    "brand" => "visa",
                    "reusable" => true,
                    "signature" => "mock_sig"
                ],
                "customer" => [
                    "id" => 0000000,
                    "first_name" => null,
                    "last_name" => null,
                    "email" => "customer@email.com",
                    "customer_code" => "mock_customer_code",
                    "phone" => null,
                    "metadata" => null,
                    "risk_action" => "deny"
                ],
                "plan" => null,
                "order_id" => null,
                "paidAt" => "2020-01-04T15:42:52.000Z",
                "createdAt" => "2020-01-04T14:35:51.000Z",
                "transaction_date" => "2020-01-04T14:35:51.000Z",
                "plan_object" => [],
                "subaccount" => []
            ]

        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getFailedPaymentVerificationResponse()
    {
        $response_body = [
            "status" => true,
            "message" => "Verification successful",
            "data" => [
                "id" => 00000000,
                "domain" => "test",
                "status" => "failed",
                "reference" => "mock_reference",
                "amount" => 500000,
                "message" => null,
                "gateway_response" => "Insufficient funds",
                "channel" => "card",
                "ip_address" => "",
                "log" => [
                    "start_time" => 1578152563,
                    "time_spent" => 9,
                    "attempts" => 1,
                    "errors" => 0,
                    "success" => true,
                    "mobile" => false,
                    "input" => [],
                    "history" => [
                        [
                            "type" => "input",
                            "message" => "Filled these fields: card number, card expiry, card cvv",
                            "time" => 7
                        ],
                        [
                            "type" => "action",
                            "message" => "Attempted to pay",
                            "time" => 8
                        ],
                        [
                            "type" => "close",
                            "message" => "Page closed",
                            "time" => 9
                        ]
                    ]
                ],
                "fees" => 17500,
                "fees_split" => null,
                "authorization" => [
                    "authorization_code" => "mock_authorization_code",
                    "bin" => "000000",
                    "last4" => "0000",
                    "exp_month" => "12",
                    "exp_year" => "2020",
                    "channel" => "card",
                    "card_type" => "visa DEBIT",
                    "bank" => "Test Bank",
                    "country_code" => "NG",
                    "brand" => "visa",
                    "reusable" => true,
                    "signature" => "mock_sig"
                ],
                "customer" => [
                    "id" => 00000000,
                    "first_name" => null,
                    "last_name" => null,
                    "email" => "customer@email.com",
                    "customer_code" => "mock_customer_code",
                    "phone" => null,
                    "metadata" => null,
                    "risk_action" => "deny"
                ],
                "plan" => null,
                "order_id" => null,
                "paidAt" => "2020-01-04T15:42:52.000Z",
                "createdAt" => "2020-01-04T14:35:51.000Z",
                "transaction_date" => "2020-01-04T14:35:51.000Z",
                "plan_object" => [],
                "subaccount" => []
            ]

        ];
        return new Response(200, [], json_encode($response_body));
    }
}