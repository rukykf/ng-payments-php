# NgPayments
[![Build Status](https://travis-ci.com/rukykf/ng-payments-php.png?branch=master)](https://travis-ci.com/rukykf/ng-payments-php) [![Latest Stable Version](https://poser.pugx.org/kofi/ngpayments/v/stable)](https://packagist.org/packages/kofi/ngpayments)  [![License](https://poser.pugx.org/kofi/ngpayments/license)](https://packagist.org/packages/kofi/ngpayments)

Simple integration with Nigerian payment gateways - Paystack and Rave (Flutterwave). This package simplifies the process of building requests to these APIs and retrieving their responses for processing.

## Requirements, Installation & Configuration
This package requires at least php7.1

Install using Composer:

    composer require kofi/ngpayments

This package uses 4 configuration values. You are required to provide 2 of these configuration values while the package has defaults for the other two. You can set your configurations using either .env, PHP Constants or configuration caching

**Using .env (recommended)**
```
NG_PAYMENT_PROVIDER=paystack 		        #if this variable is not set, paystack is the default
PAYSTACK_PUBLIC_KEY=**your public key** 	#the public key is required. 
PAYSTACK_PRIVATE_KEY=**your private key** 	#the private key is required
APP_ENV=production 				#if not set, this will default to testing
```
If you are using Rave (Flutterwave) instead of Paystack, replace the PAYSTACK prefix with RAVE and set your payment provider like so:
```
NG_PAYMENT_PROVIDER=rave 		#important, if you don't set this, it will default to paystack and search for Paystack keys
RAVE_PUBLIC_KEY=**your public key**
RAVE_PRIVATE_KEY=**your private key**
APP_ENV=production
```
**Using constants in a config.php file (not recommended)**
 ```php
define("NG_PAYMENT_PROVIDER", "rave");
define("RAVE_PUBLIC_KEY", "your rave public key");
define("RAVE_PRIVATE_KEY", "your rave private key");
```
**Caching configurations (relevant for Laravel users)**

If you are using the Laravel `artisan config:cache` command to cache your configurations in production, the package will not have access to the variables from your .env file so you'd need to configure the package in one of your service providers. 

Firstly load the configurations from .env into Laravel's config by going to config/services.php and doing this

```php
'paystack' => [
   'public_key' => env('PAYSTACK_PUBLIC_KEY'), 
   'private_key' => env('PAYSTACK_PRIVATE_KEY')
 ];
```
Then in your AppServiceProvider's boot() method ( you could place this in any other service provider) do this:

```php
public function boot(){
    //Create configuration
    $payment_provider_config = [
	    "provider" => "paystack", //If the provider is not set, default is Paystack
	    "public_key" => config("services.paystack.public_key"),
	    "secret_key" => config("services.paystack.private_key"),
	    "app_env" => config("app.env")
     ];

   //Tell the package to use this configuration
   PaymentProviderFactory::setPaymentProviderConfig($payment_provider_config);
}
```

**Configuring Http Exceptions and Payment Exceptions**

By default http exceptions are disabled for requests made by the package. But if you would like to deal with `BadResponseException` exceptions thrown by the Guzzle Http Client for 4xx and 5xx level http responses, you can enable http exceptions this way:

```php
PaymentProviderFactory::enableHttpExceptions();
```

## Setting Request Parameters
The package provides you with some flexibility in how you set your request parameters before they are sent to the configured Payment Provider. 

For example, to bill a customer using Paystack, after configuring Paystack using the instructions above you could do this:

```php
$bill = new Bill("customer@email.com", 3000);     		//The Bill class always works with amounts in naira.
$payment_reference = $bill->charge()->getPaymentReference();
savePaymentReference($payment_reference); 			// save the generated reference to your database
$payment_page_url = $bill->getPaymentPageUrl(); 
header("Location: " . $payment_page_url);   			//redirect to Paystack's checkout page
 ```
 
 Using the Bill constructor allows you to set the parameters that are required for that request but you might have additional parameters you'd like to send along with your request. For instance, you might want to set your own payment reference rather than use the reference that Paystack generates for you. In that instance you could do any of these:
 
**Using Magic set methods**

```php
$bill = new Bill();
$bill->setCustomerEmail("customer@email.com")
     ->setReference("reference")
     ->setAmount(40000)
     ->setCallbackUrl($callback_url);
$payment_page_url = $bill->charge()->getPaymentPageUrl()
header("Location: ". $payment_page_url);
```

Parameters set this way will automatically be rendered and sent to the configured payment provider with snake_case. 

You can use kebab-case like so:

```php
$bill->setCallbackUrl($callback_url, 'kebab-case'); 
```

If you don't want any type of case applied use
```php
$bill->setCallbackUrl($callback_url, 'none');
```

**Using Magic properties**

```php
$bill = new Bill();
$bill->customer_email = "customer@email.com";
$bill->reference = "unique_reference";
$bill->callback_url = $callback_url;  

//if you are working with Paystack, 
//set the amount in kobo, 
//if you want to work with naira amounts (with Paystack)
//set the naira_amount property instead like so:
$bill->amount = 40000; 
$bill->naira_amount = 400;

$payment_page_url = $bill->charge()->getPaymentPageUrl()

//Redirect to the payment page. 
//If you are using a framework, use your framework's redirect method
header("Location: ". $payment_page_url); 
```

**Using an associative array of parameters**

```php
$bill_data = [
    "customer_email" => "customer@email.com",
    "reference" => "unique_reference",
    "amount" => 4000,
    "callback_url" => $callback_url
];
$bill = new Bill($bill_data);
```

Check the documentation for the provider you are integrating with to learn more
about the request options needed for various endpoints. 

## Billing
To charge a customer an amount of naira for a product, say N7000, follow these steps:

**1) Initialize the payment and redirect the customer to the payment page**
```php
$bill = new Bill($customer_email, 7000); 
$reference = $bill->charge()->getPaymentReference(); //make sure to call the charge() method on bill first
savePaymentReferenceToDB($reference);
$payment_page_url = $bill->getPaymentPageUrl();
header("Location: ". $payment_page_url);
 ```  
   
**2) Verify that that the customer paid what they were expected to pay**

You would typically do this step in response to a request from either [Paystack](https://developers.paystack.co/docs/paystack-standard)  or [Rave](https://developer.flutterwave.com/docs/rave-standard) to the callback_url you set in your API dashboard or with your request using `$bill->setCallbackUrl($url)` (see their docs for more information).

```php
$reference = getPaymentReferenceFromSomewhere(); 
if(Bill::isPaymentValid($reference, 7000)){
    //send product to customer or activate account etc.
}
```

If the Payment is not valid, by default a `FailedPaymentException` is thrown which you can use to log details about the request and the response received from the PaymentProvider like so:

```php
try{
  if(Bill::isPaymentValid($reference, 7000)){
    //do something
   }
}catch(FailedPaymentException $e){
  logFailedPayments($reference, $e->getResponse());
  //do any additional processing
}
```

If you don't want to handle this type of exceptions, you can disable this using

```php
PaymentProviderFactory::disablePaymentExceptions(); 
```

## Recurring Billing
Both Rave and Paystack support recurring billing through the use of tokens. To charge a customer with recurring billing, you have to ensure that customer has made at least one payment to you so that your PaymentProvider can generate the token to send you for use in future billings.

To charge a customer using Recurring Billing follow these steps:

**1) Initialize a `Bill` payment**

```php
$bill = new Bill($customer_email, 7000);
$reference = $bill->charge()->getPaymentReference();
$payment_page_url = $bill->getPaymentPageUrl();
redirect_to_url($payment_page_url) //if you are using a framework, use your framework's redirect method
```

**2) Validate the payment and store the authorization code or token from your payment provider**

```php
$authorization_code = Bill::getPaymentAuthorizationCode($reference, 7000); //This method validates the payment made by the customer and returns the authorization code (token) if the payment is valid
```

**3) Create an AuthBill and charge the customer anytime you need to**

Note that the authorization_code you get from your provider is only valid for the specified customer's email. 

```php
$auth_bill = new AuthBill($authorization_code, $customer_email, 3000); 
$reference = $auth_bill->charge(); //if the payment is successful, this returns a reference, if it fails, it returns null
```

## Split Billing
To share a customer's payment with a merchant you need to first create a sub account for the merchant, and then use the subaccount id when billing the customer. Check your payment provider's documentation to learn more about subaccounts.

**1) Creating and storing the subaccount**

```php
$subaccount = new Subaccount($business_name, $settlement_bank, $account_number, $percentage_charge); 

//the save method will create a subaccount 
//with your payment provider 
//and return the id or code of the created subaccount
$subaccount_id = $subaccount->save();

saveSubaccountIdToDatabase($subaccount_id)
```

To get a list of banks and their corresponding codes to use when creating subaccounts, you can call the `Subaccount::fetchBanks()` method. 

Paystack requires you to pass in the bank name when creating the subaccounts while Rave requires you to pass in the bank code

Like with bills, you could also use magic setters and magic properties to set your request variables for subaccounts.

**2) Split a bill with the created subaccount**
  
```php
$subaccount_id = retrieveSubaccountIdFromDatabase()
$bill = new Bill($customer_email, 4000);
$reference = $bill->splitCharge($subaccount_id)->getPaymentReference();
$payment_page_url = $bill->getPaymentPageUrl();
```

You could also split `AuthBill` payments with subaccounts

```php
$subaccount_id = retrieveSubaccountIdFromDatabase()
$auth_bill = new AuthBill($authorization_code, $customer_email, 4000);
$reference = $auth_bill->splitCharge($subaccount_id);
```


**3) Validate the payment**

```php
$is_valid = Bill::isPaymentValid($reference, 4000); 
```

## Plans and Subscriptions
You could also create payment plans with your payment provider and subscribe customers to those payment plans. 

> Note that, if you do this, the customer will be able to cancel their subscriptions directly with either Paystack or Rave, so you'd need to regularly check to ensure that a customer has not cancelled their subscription before you give value.

**1) Create the Payment Plan**

```php
$plan = new Plan("My Plan", 3000, "weekly");
$plan_id = $plan->save(); 
```

**2) Subscribe a customer to the payment plan**

```php
$bill = new Bill($customer_email, 3000);            //the plan amount will always override the bill amount in this case
$reference = $bill->subscribe($plan_id)->getPaymentReference(); 
$payment_page_url = $bill->getPaymentPageUrl();   
header("Location: " . $payment_page_url);          //redirect to the payment page
```

    
## Integrating with Paystack
When you create a Plan or a Subaccount with Paystack, Paystack returns two forms of identifying these objects: An alphanumeric code and an id. 

When you make a call to either `$plan->save()` or `$subaccount->save()` methods, you are going to be getting the alphanumeric code. The reason for this is that while you can identify plans and subaccounts with the numeric id, you can't use that numeric id to initialize payments. You need the code for that. 

So when you save a subaccount or plan, you'll be getting the alphanumeric code which you can then store in your database for use in future transactions. 

## Integrating with Rave
**Creating Subaccounts with Rave**

The Subaccount class that ships with this package has 4 default arguments in its constructor

```php
class Subaccount implements ApiDataMapperInterface
{
   ...
   public function __construct(
      $business_name = null,
      string $settlement_bank = null,
      string $account_number = null,
      $percentage_charge = null
   ){...}
   ...
```

In the documentation for Rave's [Create Subaccount](https://developer.flutterwave.com/v2.0/reference#create-subaccount) endpoint you will find that Rave requires you to set more parameters than this. The package helps you set the `split_type` to `percentage` and the `country` to `NG`.  The `$percentage_charge` in the constructor is used to set the `split_value` required by Rave. This is fine if you want to work with percentage splits. 

To create a Subaccount with Rave you would still need to set the `business_mobile` and the `business_email` yourself, in order to successfully create a subaccount when you call the `$subaccount->save()` method.  So to create a subaccount with Rave you would do this:

```php
$subaccount = new Subaccount("Business Name", $settlement_bank, $merchant_account_number, $percentage_charge);
$subaccount->business_mobile = $business_mobile;
$subaccount->business_email = $business_email;
$subaccount_id = $subaccount->save(); 
```

Also note that when you save a subaccount, you are going to be getting the subaccount's alphanumeric id which you can use to initialize payments later with either `Bill` or `AuthBill`. 
