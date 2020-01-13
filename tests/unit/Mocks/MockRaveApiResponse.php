<?php


namespace Kofi\NgPayments\Tests\unit\Mocks;

use GuzzleHttp\Psr7\Response;

class MockRaveApiResponse
{
    public static function getSuccessfulInitializePaymentResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "Hosted Link",
            "data" => [
                "link" => "https://example.com/checkout"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulVerifyPaymentResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "Tx Fetched",
            "data" => [
                "txid" => 0000,
                "txref" => "mock_reference",
                "flwref" => "mock_flw_ref",
                "devicefingerprint" => "fingerprint",
                "cycle" => "one-time",
                "amount" => 5000,
                "currency" => "NGN",
                "chargedamount" => 5000,
                "appfee" => 70,
                "merchantfee" => 0,
                "merchantbearsfee" => 1,
                "chargecode" => "00",
                "chargemessage" => "Please enter the OTP sent to your mobile number 080****** and email te**@rave**.com",
                "authmodel" => "PIN",
                "ip" => "127.0.0.0",
                "narration" => "CARD Transaction ",
                "status" => "successful",
                "vbvcode" => "00",
                "vbvmessage" => "successful",
                "authurl" => "N/A",
                "acctcode" => null,
                "acctmessage" => null,
                "paymenttype" => "card",
                "paymentid" => "6490",
                "fraudstatus" => "ok",
                "chargetype" => "normal",
                "createdday" => 6,
                "createddayname" => "SATURDAY",
                "createdweek" => 2,
                "createdmonth" => 0,
                "createdmonthname" => "JANUARY",
                "createdquarter" => 1,
                "createdyear" => 2020,
                "createdyearisleap" => true,
                "createddayispublicholiday" => 0,
                "createdhour" => 19,
                "createdminute" => 4,
                "createdpmam" => "pm",
                "created" => "2020-01-11T19:04:13.000Z",
                "customerid" => 251377,
                "custphone" => null,
                "custnetworkprovider" => "N/A",
                "custname" => "Anonymous customer",
                "custemail" => "customer@socio.com",
                "custemailprovider" => "COMPANY EMAIL",
                "custcreated" => "2020-01-11T19:04:13.000Z",
                "accountid" => 27357,
                "acctbusinessname" => "Mock Business",
                "acctcontactperson" => "Mock Name",
                "acctcountry" => "NG",
                "acctbearsfeeattransactiontime" => 1,
                "acctparent" => 2410,
                "acctvpcmerchant" => "N/A",
                "acctalias" => null,
                "acctisliveapproved" => 0,
                "orderref" => "mock_order_ref",
                "paymentplan" => null,
                "paymentpage" => null,
                "raveref" => "mock_rave_ref",
                "amountsettledforthistransaction" => 4930,
                "card" => [
                    "expirymonth" => "00",
                    "expiryyear" => "22",
                    "cardBIN" => "000000",
                    "last4digits" => "0000",
                    "brand" => " CREDIT",
                    "issuing_country" => "NIGERIA NG",
                    "card_tokens" => [
                        [
                            "embedtoken" => "mock_embed_token",
                            "shortcode" => "0000",
                            "expiry" => "9999999999999"
                        ]
                    ],
                    "type" => "MASTERCARD",
                    "life_time_token" => "mock_lifetime_token"
                ],
                "meta" => [
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getFailedVerifyPaymentResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "Tx Fetched",
            "data" => [
                "txid" => 1003765,
                "txref" => "mock_reference",
                "flwref" => "mock_flw_ref",
                "devicefingerprint" => "fingerprint",
                "cycle" => "one-time",
                "amount" => 5000,
                "currency" => "NGN",
                "chargedamount" => 5000,
                "appfee" => 195,
                "merchantfee" => 0,
                "merchantbearsfee" => 1,
                "chargecode" => "RR-51",
                "chargemessage" => "Insufficient Funds: Your card cannot be charged due to insufficient funds. Please try another card or fund your card and try again.",
                "authmodel" => "VBVSECURECODE",
                "ip" => "127.0.0.0",
                "narration" => "CARD Transaction ",
                "status" => "failed",
                "vbvcode" => "RR-51",
                "vbvmessage" => "Insufficient Funds: Your card cannot be charged due to insufficient funds. Please try another card or fund your card and try again.",
                "authurl" => "https://example.com/checkout",
                "acctcode" => null,
                "acctmessage" => null,
                "paymenttype" => "card",
                "paymentid" => "2113",
                "fraudstatus" => "ok",
                "chargetype" => "normal",
                "createdday" => 6,
                "createddayname" => "SATURDAY",
                "createdweek" => 2,
                "createdmonth" => 0,
                "createdmonthname" => "JANUARY",
                "createdquarter" => 1,
                "createdyear" => 2020,
                "createdyearisleap" => true,
                "createddayispublicholiday" => 0,
                "createdhour" => 19,
                "createdminute" => 21,
                "createdpmam" => "pm",
                "created" => "2020-01-11T19:21:54.000Z",
                "customerid" => 251382,
                "custphone" => null,
                "custnetworkprovider" => "N/A",
                "custname" => "Anonymous customer",
                "custemail" => "customer@socio.com",
                "custemailprovider" => "COMPANY EMAIL",
                "custcreated" => "2020-01-11T19:21:54.000Z",
                "accountid" => 27357,
                "acctbusinessname" => "Mock Business",
                "acctcontactperson" => "Mock Name",
                "acctcountry" => "NG",
                "acctbearsfeeattransactiontime" => 1,
                "acctparent" => 2410,
                "acctvpcmerchant" => "N/A",
                "acctalias" => null,
                "acctisliveapproved" => 0,
                "orderref" => "URF_1578770514957_8605035",
                "paymentplan" => null,
                "paymentpage" => null,
                "raveref" => "RV315787705137617ABB2E4EEB",
                "card" => [
                    "expirymonth" => "00",
                    "expiryyear" => "21",
                    "cardBIN" => "000000",
                    "last4digits" => "0000",
                    "brand" => "ABU DHABI ISLAMIC BANK P.J.S.C DEBITPREPAID",
                    "issuing_country" => "UNITED ARAB EMIRATES AE",
                    "card_tokens" => [
                    ],
                    "type" => "MASTERCARD"
                ],
                "meta" => [
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulChargeAuthResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "Charge success",
            "data" => [
                "id" => 1003774,
                "txRef" => "mock_reference",
                "orderRef" => "mock_order_ref",
                "flwRef" => "mock_flw_ref",
                "redirectUrl" => "http://127.0.0",
                "device_fingerprint" => "N/A",
                "settlement_token" => null,
                "cycle" => "one-time",
                "amount" => 10000,
                "charged_amount" => 10000,
                "appfee" => 140,
                "merchantfee" => 0,
                "merchantbearsfee" => 1,
                "chargeResponseCode" => "00",
                "raveRef" => null,
                "chargeResponseMessage" => "Approved",
                "authModelUsed" => "noauth",
                "currency" => "NGN",
                "IP" => "::127.0.0.1",
                "narration" => "Mock Business",
                "status" => "successful",
                "modalauditid" => "modal_audit_id",
                "vbvrespmessage" => "Approved",
                "authurl" => "N/A",
                "vbvrespcode" => "00",
                "acctvalrespmsg" => null,
                "acctvalrespcode" => null,
                "paymentType" => "card",
                "paymentPlan" => null,
                "paymentPage" => null,
                "paymentId" => "6490",
                "fraud_status" => "ok",
                "charge_type" => "normal",
                "is_live" => 0,
                "retry_attempt" => null,
                "getpaidBatchId" => null,
                "createdAt" => "2020-01-11T19:32:45.000Z",
                "updatedAt" => "2020-01-11T19:32:45.000Z",
                "deletedAt" => null,
                "customerId" => 251377,
                "AccountId" => 27357,
                "customer" => [
                    "id" => 251377,
                    "phone" => null,
                    "fullName" => "Anonymous customer",
                    "customertoken" => null,
                    "email" => "customer@socio.com",
                    "createdAt" => "2020-01-11T19:04:13.000Z",
                    "updatedAt" => "2020-01-11T19:04:13.000Z",
                    "deletedAt" => null,
                    "AccountId" => 27357
                ],
                "chargeToken" => [
                    "user_token" => "mock_token",
                    "embed_token" => "mock_embed_token"
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getFailedChargeAuthResponse()
    {
        $response_body = [
            "status" => "error",
            "message" => "Wrong token or email passed",
            "data" => [
                "is_error" => true,
                "code" => "ERR",
                "message" => "Wrong token or email passed"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulCreatePlanResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "CREATED-PAYMENTPLAN",
            "data" => [
                "id" => 3704,
                "name" => "Test Plan",
                "amount" => 7000,
                "interval" => "daily",
                "duration" => 0,
                "status" => "active",
                "currency" => "NGN",
                "plan_token" => "mock_plan_token",
                "date_created" => "2020-01-11T20:08:44.000Z"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchAllPlansResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "QUERIED-PAYMENTPLANS",
            "data" => [
                "page_info" => [
                    "total" => 4,
                    "current_page" => 1,
                    "total_pages" => 1
                ],
                "paymentplans" => [
                    [
                        "id" => 3707,
                        "name" => "Test Plan",
                        "amount" => 7000,
                        "interval" => "monthly",
                        "duration" => 0,
                        "status" => "active",
                        "currency" => "NGN",
                        "plan_token" => "mock_plan_token",
                        "date_created" => "2020-01-11T20:11:01.000Z"
                    ],
                    [
                        "id" => 3706,
                        "name" => "Test Plan",
                        "amount" => 7000,
                        "interval" => "monthly",
                        "duration" => 0,
                        "status" => "active",
                        "currency" => "NGN",
                        "plan_token" => "mock_plan_token",
                        "date_created" => "2020-01-11T20:10:34.000Z"
                    ],
                    [
                        "id" => 3705,
                        "name" => "Test Plan",
                        "amount" => 7000,
                        "interval" => "weekly",
                        "duration" => 0,
                        "status" => "active",
                        "currency" => "NGN",
                        "plan_token" => "mock_plan_token",
                        "date_created" => "2020-01-11T20:09:43.000Z"
                    ],
                    [
                        "id" => 3704,
                        "name" => "Test Plan",
                        "amount" => 7000,
                        "interval" => "daily",
                        "duration" => 0,
                        "status" => "active",
                        "currency" => "NGN",
                        "plan_token" => "mock_plan_token",
                        "date_created" => "2020-01-11T20:08:44.000Z"
                    ]
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulUpdatePlanResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "PLAN-EDITED",
            "data" => [
                "id" => 3707,
                "name" => "Edited Test Plan",
                "uuid" => "mock_plan_token",
                "status" => "active",
                "start" => null,
                "stop" => null,
                "initial_charge_amount" => null,
                "currency" => "NGN",
                "amount" => 7000,
                "duration" => 0,
                "interval" => "monthly",
                "createdAt" => "2020-01-11T20:11:01.000Z",
                "updatedAt" => "2020-01-11T20:26:39.000Z",
                "deletedAt" => null,
                "AccountId" => 27357,
                "paymentpageId" => null
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchPlanResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "QUERIED-PAYMENTPLANS",
            "data" => [
                "page_info" => [
                    "total" => 1,
                    "current_page" => 1,
                    "total_pages" => 1
                ],
                "paymentplans" => [
                    [
                        "id" => 3707,
                        "name" => "Test Plan",
                        "amount" => 7000,
                        "interval" => "monthly",
                        "duration" => 0,
                        "status" => "active",
                        "currency" => "NGN",
                        "plan_token" => "mock_plan_token",
                        "date_created" => "2020-01-11T20:11:01.000Z"
                    ]
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulDeletePlanResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "PLAN-CANCELED",
            "data" => [
                "id" => 3707,
                "name" => "Edited Test Plan",
                "uuid" => "mock_plan_token",
                "status" => "cancelled",
                "start" => null,
                "stop" => null,
                "initial_charge_amount" => null,
                "currency" => "NGN",
                "amount" => 7000,
                "duration" => 0,
                "interval" => "monthly",
                "createdAt" => "2020-01-11T20:11:01.000Z",
                "updatedAt" => "2020-01-11T20:33:48.000Z",
                "deletedAt" => null,
                "AccountId" => 27357,
                "paymentpageId" => null
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulCreateSubAccountResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "SUBACCOUNT-CREATED",
            "data" => [
                "id" => 2114,
                "account_number" => "1234567890",
                "account_bank" => "044",
                "business_name" => "Test Business",
                "fullname" => "Bale Gary",
                "date_created" => "2020-01-11T21:08:43.000Z",
                "account_id" => 87705,
                "split_ratio" => 1,
                "split_type" => "percentage",
                "split_value" => "4",
                "subaccount_id" => "mock_subaccount_id",
                "bank_name" => "ACCESS BANK NIGERIA",
                "country" => "NG"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulUpdateSubAccountResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "SUBACCOUNT-EDITED",
            "data" => [
                "id" => 2114,
                "account_number" => "1234567890",
                "account_bank" => "044",
                "business_name" => "Edited Test Business",
                "fullname" => "Bale Gary",
                "date_created" => "2020-01-11T21:08:43.000Z",
                "meta" => null,
                "account_id" => 87705,
                "split_ratio" => 1,
                "split_type" => "percentage",
                "split_value" => 4,
                "subaccount_id" => "mock_subaccount_id",
                "bank_name" => "ACCESS BANK NIGERIA",
                "country" => "NG"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchAllSubAccountsResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "SUBACCOUNTS",
            "data" => [
                "page_info" => [
                    "total" => 4,
                    "current_page" => 1,
                    "total_pages" => 1
                ],
                "subaccounts" => [
                    [
                        "id" => 2115,
                        "account_number" => "1234567890",
                        "account_bank" => "044",
                        "business_name" => "Test Business",
                        "fullname" => "Bale Gary",
                        "date_created" => "2020-01-11T21:19:48.000Z",
                        "meta" => null,
                        "account_id" => 87706,
                        "split_ratio" => 1,
                        "split_type" => "percentage",
                        "split_value" => 4,
                        "subaccount_id" => "mock_subaccount_id",
                        "bank_name" => "ACCESS BANK NIGERIA",
                        "country" => "NG"
                    ],
                    [
                        "id" => 2114,
                        "account_number" => "5678901234",
                        "account_bank" => "044",
                        "business_name" => "Edited Test Business",
                        "fullname" => "Bale Gary",
                        "date_created" => "2020-01-11T21:08:43.000Z",
                        "meta" => null,
                        "account_id" => 87705,
                        "split_ratio" => 1,
                        "split_type" => "percentage",
                        "split_value" => 4,
                        "subaccount_id" => "mock_subaccount_id",
                        "bank_name" => "ACCESS BANK NIGERIA",
                        "country" => "NG"
                    ],
                    [
                        "id" => 2113,
                        "account_number" => "0690000041",
                        "account_bank" => "044",
                        "business_name" => "Test Business",
                        "fullname" => "Alexis Rogers",
                        "date_created" => "2020-01-11T21:06:20.000Z",
                        "meta" => null,
                        "account_id" => 87703,
                        "split_ratio" => 1,
                        "split_type" => "percentage",
                        "split_value" => 4,
                        "subaccount_id" => "mock_subaccount_id",
                        "bank_name" => "ACCESS BANK NIGERIA",
                        "country" => "NG"
                    ],
                    [
                        "id" => 2112,
                        "account_number" => "0690000031",
                        "account_bank" => "044",
                        "business_name" => "Test Business",
                        "fullname" => "Forrest Green",
                        "date_created" => "2020-01-11T21:05:52.000Z",
                        "meta" => null,
                        "account_id" => 87702,
                        "split_ratio" => 1,
                        "split_type" => "percentage",
                        "split_value" => 4,
                        "subaccount_id" => "mock_subaccount_id",
                        "bank_name" => "ACCESS BANK NIGERIA",
                        "country" => "NG"
                    ]
                ]
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulFetchSubAccountResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "SUBACCOUNT",
            "data" => [
                "id" => 2115,
                "account_number" => "1234567890",
                "account_bank" => "044",
                "business_name" => "Test Business",
                "fullname" => "Bale Gary",
                "date_created" => "2020-01-11T21:19:48.000Z",
                "meta" => null,
                "account_id" => 87706,
                "split_ratio" => 1,
                "split_type" => "percentage",
                "split_value" => 4,
                "subaccount_id" => "mock_subaccount_id",
                "bank_name" => "ACCESS BANK NIGERIA",
                "country" => "NG"
            ]
        ];
        return new Response(200, [], json_encode($response_body));
    }

    public static function getSuccessfulDeleteSubAccountResponse()
    {
        $response_body = [
            "status" => "success",
            "message" => "SUBACCOUNT-DELETED",
            "data" => "Deleted"
        ];
        return new Response(200, [], json_encode($response_body));
    }
}
