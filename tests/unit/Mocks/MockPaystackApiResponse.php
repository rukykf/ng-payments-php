<?php


namespace Kofi\NgPayments\Tests\unit\Mocks;

use GuzzleHttp\Psr7\Response;

class MockPaystackApiResponse
{
    public static function getSuccessfulInitializePaymentResponse()
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

    public static function getSuccessfulVerifyPaymentResponse()
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

    public static function getFailedVerifyPaymentResponse()
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

    public static function getSuccessfulChargeAuthResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Charge attempted',
            'data' =>
                [
                    'amount' => 500000,
                    'currency' => 'NGN',
                    'transaction_date' => '2020-01-09T19:47:03.000Z',
                    'status' => 'success',
                    'reference' => 'mock_reference',
                    'domain' => 'test',
                    'metadata' => '',
                    'gateway_response' => 'Approved',
                    'message' => null,
                    'channel' => 'card',
                    'ip_address' => null,
                    'log' => null,
                    'fees' => 17500,
                    'authorization' =>
                        [
                            'authorization_code' => 'mock_authorization_code',
                            'bin' => '408408',
                            'last4' => '4081',
                            'exp_month' => '12',
                            'exp_year' => '2020',
                            'channel' => 'card',
                            'card_type' => 'visa DEBIT',
                            'bank' => 'Test Bank',
                            'country_code' => 'NG',
                            'brand' => 'visa',
                            'reusable' => true,
                            'signature' => 'SIG_zj7IDkk4xJv7kckHZJLu',
                        ],
                    'customer' =>
                        [
                            'id' => 18454980,
                            'first_name' => null,
                            'last_name' => null,
                            'email' => 'customer@email.com',
                            'customer_code' => 'CUS_3nszoamue6jy3qy',
                            'phone' => null,
                            'metadata' => null,
                            'risk_action' => 'deny',
                        ],
                    'plan' => 0,
                ],
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getFailedChargeAuthResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Charge attempted',
            'data' =>
                [
                    'amount' => 500000,
                    'currency' => 'NGN',
                    'transaction_date' => '2020-01-09T19:47:03.000Z',
                    'status' => 'failed',
                    'reference' => 'mock_reference',
                    'domain' => 'test',
                    'metadata' => '',
                    'gateway_response' => 'Insufficient funds',
                    'message' => null,
                    'channel' => 'card',
                    'ip_address' => null,
                    'log' => null,
                    'fees' => 17500,
                    'authorization' =>
                        [
                            'authorization_code' => 'mock_authorization_code',
                            'bin' => '408408',
                            'last4' => '4081',
                            'exp_month' => '12',
                            'exp_year' => '2020',
                            'channel' => 'card',
                            'card_type' => 'visa DEBIT',
                            'bank' => 'Test Bank',
                            'country_code' => 'NG',
                            'brand' => 'visa',
                            'reusable' => true,
                            'signature' => 'SIG_zj7IDkk4xJv7kckHZJLu',
                        ],
                    'customer' =>
                        [
                            'id' => 18454980,
                            'first_name' => null,
                            'last_name' => null,
                            'email' => 'customer@email.com',
                            'customer_code' => 'CUS_3nszoamue6jy3qy',
                            'phone' => null,
                            'metadata' => null,
                            'risk_action' => 'deny',
                        ],
                    'plan' => 0,
                ],
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulCreatePlanResponse()
    {
        $response_body = [
            "status" => true,
            "message" => "Plan created",
            "data" => [
                "name" => "Monthly retainer",
                "interval" => "monthly",
                "amount" => 500000,
                "integration" => 402187,
                "domain" => "test",
                "currency" => "NGN",
                "plan_code" => "plan_code",
                "invoice_limit" => 0,
                "send_invoices" => true,
                "send_sms" => true,
                "hosted_page" => false,
                "migrate" => false,
                "id" => 37425,
                "createdAt" => "2020-01-07T11:55:40.076Z",
                "updatedAt" => "2020-01-07T11:55:40.076Z"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchAllPlansResponse()
    {
        $response_body = [
            "status" => true,
            "message" => "Plans retrieved",
            "data" => [
                [
                    "subscriptions" => [],
                    "name" => "Monthly retainer",
                    "interval" => "monthly",
                    "amount" => 500000,
                    "integration" => 402187,
                    "domain" => "test",
                    "currency" => "NGN",
                    "plan_code" => "plan_code",
                    "description" => null,
                    "invoice_limit" => 0,
                    "send_invoices" => true,
                    "send_sms" => true,
                    "hosted_page" => false,
                    "hosted_page_url" => null,
                    "hosted_page_summary" => null,
                    "migrate" => false,
                    "id" => 37425,
                    "createdAt" => "2020-01-07T11:55:40.076Z",
                    "updatedAt" => "2020-01-07T11:55:40.076Z"
                ],
                [
                    "subscriptions" => [
                        [
                            "customer" => 63,
                            "plan" => 27,
                            "integration" => 100032,
                            "domain" => "test",
                            "start" => 1458505748,
                            "status" => "complete",
                            "quantity" => 1,
                            "amount" => 100000,
                            "subscription_code" => "SUB_birvokwpp0sftun",
                            "email_token" => "9y62mxp4uh25das",
                            "authorization" => 79,
                            "easy_cron_id" => null,
                            "cron_expression" => "0 0 * * 0",
                            "next_payment_date" => "2016-03-27T07:00:00.000Z",
                            "open_invoice" => null,
                            "id" => 8,
                            "createdAt" => "2016-03-20T20:29:08.000Z",
                            "updatedAt" => "2016-03-22T16:23:52.000Z"
                        ]
                    ],
                    "name" => "Monthly retainer",
                    "interval" => "monthly",
                    "amount" => 500000,
                    "integration" => 402187,
                    "domain" => "test",
                    "currency" => "NGN",
                    "plan_code" => "plan_code",
                    "description" => null,
                    "invoice_limit" => 0,
                    "send_invoices" => true,
                    "send_sms" => true,
                    "hosted_page" => false,
                    "hosted_page_url" => null,
                    "hosted_page_summary" => null,
                    "migrate" => false,
                    "id" => 37425,
                    "createdAt" => "2020-01-07T11:55:40.076Z",
                    "updatedAt" => "2020-01-07T11:55:40.076Z"
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulUpdatePlanResponse()
    {
        $response_body = [
            "status" => true,
            "message" => "Plan updated. 1 subscription(s) affected"
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchPlanResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Plan retrieved',
            'data' =>
                [
                    'subscriptions' => [],
                    'integration' => 100032,
                    'domain' => 'test',
                    'name' => 'Monthly retainer',
                    'plan_code' => 'plan_code',
                    'description' => null,
                    'amount' => 50000,
                    'interval' => 'monthly',
                    'send_invoices' => true,
                    'send_sms' => true,
                    'hosted_page' => false,
                    'hosted_page_url' => null,
                    'hosted_page_summary' => null,
                    'currency' => 'NGN',
                    'id' => 28,
                    'createdAt' => '2016-03-29T22:42:50.000Z',
                    'updatedAt' => '2016-03-29T22:42:50.000Z',
                ],
        ];

        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulCreateSubAccountResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Subaccount created',
            'data' =>
                [
                    'integration' => 100973,
                    'domain' => 'test',
                    'subaccount_code' => 'subaccount_code',
                    'business_name' => 'Sunshine Studios',
                    'description' => null,
                    'primary_contact_name' => null,
                    'primary_contact_email' => null,
                    'primary_contact_phone' => null,
                    'metadata' => null,
                    'percentage_charge' => 18.2,
                    'is_verified' => false,
                    'settlement_bank' => 'Access Bank',
                    'account_number' => '0193274682',
                    'settlement_schedule' => 'AUTO',
                    'active' => true,
                    'migrate' => false,
                    'id' => 55,
                    'createdAt' => '2016-10-05T13:22:04.000Z',
                    'updatedAt' => '2016-10-21T02:19:47.000Z',
                ],
        ];

        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulUpdateSubAccountResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Subaccount updated',
            'data' =>
                [
                    'integration' => 100973,
                    'domain' => 'test',
                    'subaccount_code' => 'subaccount_code',
                    'business_name' => 'Sunshine Studios',
                    'description' => null,
                    'primary_contact_name' => null,
                    'primary_contact_email' => 'dafe@aba.com',
                    'primary_contact_phone' => null,
                    'metadata' => null,
                    'percentage_charge' => 18.9,
                    'is_verified' => false,
                    'settlement_bank' => 'Access Bank',
                    'account_number' => '0193274682',
                    'settlement_schedule' => 'AUTO',
                    'active' => true,
                    'migrate' => false,
                    'id' => 55,
                    'createdAt' => '2016-10-05T13:22:04.000Z',
                    'updatedAt' => '2016-10-21T02:19:47.000Z',
                ],
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchAllSubAccountsResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Subaccounts retrieved',
            'data' =>
                [
                    0 =>
                        [
                            'integration' => 129938,
                            'domain' => 'test',
                            'subaccount_code' => 'ACCT_cljt3j4cp0kb2gq',
                            'business_name' => 'Business 2',
                            'description' => null,
                            'primary_contact_name' => null,
                            'primary_contact_email' => null,
                            'primary_contact_phone' => null,
                            'metadata' => null,
                            'percentage_charge' => 20,
                            'is_verified' => false,
                            'settlement_bank' => 'Zenith Bank',
                            'account_number' => '0193274382',
                            'active' => true,
                            'migrate' => false,
                            'id' => 53,
                            'createdAt' => '2016-10-05T12:55:47.000Z',
                            'updatedAt' => '2016-10-05T12:55:47.000Z',
                        ],
                    1 =>
                        [
                            'integration' => 129938,
                            'domain' => 'test',
                            'subaccount_code' => 'ACCT_vwy3d1gck2c9gxi',
                            'business_name' => 'Sunshine Studios',
                            'description' => null,
                            'primary_contact_name' => null,
                            'primary_contact_email' => null,
                            'primary_contact_phone' => null,
                            'metadata' => null,
                            'percentage_charge' => 20,
                            'is_verified' => false,
                            'settlement_bank' => 'Access Bank',
                            'account_number' => '0128633833',
                            'active' => true,
                            'migrate' => false,
                            'id' => 35,
                            'createdAt' => '2016-10-04T09:06:00.000Z',
                            'updatedAt' => '2016-10-04T09:06:00.000Z',
                        ],
                    2 =>
                        [
                            'integration' => 129938,
                            'domain' => 'test',
                            'subaccount_code' => 'ACCT_5mikcokeaknxk1f',
                            'business_name' => 'Business 2',
                            'description' => null,
                            'primary_contact_name' => null,
                            'primary_contact_email' => null,
                            'primary_contact_phone' => null,
                            'percentage_charge' => 20,
                            'is_verified' => false,
                            'settlement_bank' => 'Access Bank',
                            'account_number' => '0000000000',
                            'active' => true,
                            'migrate' => false,
                            'id' => 34,
                            'createdAt' => '2016-10-04T08:46:18.000Z',
                            'updatedAt' => '2016-10-04T08:46:18.000Z',
                        ],
                ],
            'meta' =>
                [
                    'total' => 20,
                    'skipped' => 0,
                    'perPage' => '3',
                    'page' => 1,
                    'pageCount' => 7,
                ],
        ];

        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchSubAccountResponse()
    {
        $response_body = [
            'status' => true,
            'message' => 'Subaccount retrieved',
            'data' =>
                [
                    'integration' => 100973,
                    'domain' => 'test',
                    'subaccount_code' => 'ACCT_4hl4xenwpjy5wb',
                    'business_name' => 'Sunshine Studios',
                    'description' => null,
                    'primary_contact_name' => null,
                    'primary_contact_email' => 'dafe@aba.com',
                    'primary_contact_phone' => null,
                    'metadata' => null,
                    'percentage_charge' => 18.9,
                    'is_verified' => false,
                    'settlement_bank' => 'Access Bank',
                    'account_number' => '0193274682',
                    'settlement_schedule' => 'AUTO',
                    'active' => true,
                    'migrate' => false,
                    'id' => 55,
                    'createdAt' => '2016-10-05T13:22:04.000Z',
                    'updatedAt' => '2016-10-21T02:19:47.000Z',
                ],
        ];
        return new Response(200, [], json_encode($response_body));
    }
}
