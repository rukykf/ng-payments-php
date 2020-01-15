---


---

<h1 id="ngpayments">NgPayments</h1>
<p><a href="https://travis-ci.com/rukykf/ng-payments-php"><img src="https://travis-ci.com/rukykf/ng-payments-php.png?branch=master" alt="Build Status"></a> <a href="https://packagist.org/packages/kofi/ngpayments"><img src="https://poser.pugx.org/kofi/ngpayments/v/stable" alt="Latest Stable Version"></a>  <a href="https://packagist.org/packages/kofi/ngpayments"><img src="https://poser.pugx.org/kofi/ngpayments/license" alt="License"></a></p>
<p>Simple integration with Nigerian payment gateways - Paystack and Rave (Flutterwave). This package simplifies and streamlines the process of building requests to these APIs  and retrieving their responses.</p>
<h2 id="installation--configuration">Installation &amp; Configuration</h2>
<p>Install using Composer:</p>
<pre><code>composer require kofi/ngpayments
</code></pre>
<p>This package uses 4 configuration values. You are required to provide 2 of these configuration values while the package has defaults for the other two. You can set your configurations using either .env, PHP Constants or configuration caching</p>
<p><strong>Using .env (recommended)</strong></p>
<pre><code>NG_PAYMENT_PROVIDER=paystack 			#if this variable is not set, paystack is the default
PAYSTACK_PUBLIC_KEY=**your public key** 	#the public key is required. 
PAYSTACK_PRIVATE_KEY=**your private key** 	#the private key is required
APP_ENV=production 				#if not set, this will default to testing
</code></pre>
<p>If you are using Rave (Flutterwave) instead of Paystack, replace the PAYSTACK prefix with RAVE and set your payment provider like so:</p>
<pre><code>NG_PAYMENT_PROVIDER=rave 		#important, if you don't set this, it will default to paystack and search for Paystack keys
RAVE_PUBLIC_KEY=**your public key**
RAVE_PRIVATE_KEY=**your private key**
APP_ENV=production
</code></pre>
<p><strong>Using constants in a config.php file (not recommended)</strong></p>
<pre><code>define("NG_PAYMENT_PROVIDER", "rave");
define("RAVE_PUBLIC_KEY", "your rave public key");
define("RAVE_PRIVATE_KEY", "your rave private key");
</code></pre>
<p><strong>Caching configurations (relevant for Laravel users)</strong><br>
If you are using the Laravel Artisan config:cache command to cache your configurations in production, Laravel will not load your .env file so you’d need to configure the package in one of your service providers.</p>
<p>Firstly load the configurations from .env into Laravel’s config by going to config/services.php and doing this</p>
<pre><code>'paystack' =&gt; [
    'public_key' =&gt; env('PAYSTACK_PUBLIC_KEY'), 
    'private_key' =&gt; env('PAYSTACK_PRIVATE_KEY')
];
</code></pre>
<p>Then in your AppServiceProvider’s boot() method ( you could place this in any other service provider) do this:</p>
<pre><code>public function boot(){
    //Create configuration
    $payment_provider_config = [
	    "provider" =&gt; "paystack", //If the provider is not set, default is Paystack
	    "public_key" =&gt; config("services.paystack.public_key"),
	    "secret_key" =&gt; config("services.paystack.private_key"),
	    "app_env" =&gt; config("app.env")
     ];
	
   //Tell the package to use this configuration
   PaymentProviderFactory::setPaymentProviderConfig($payment_provider_config);
}
</code></pre>
<p><strong>Configuring Http Exceptions and Payment Exceptions</strong><br>
By default http exceptions are disabled for requests made by the package. But if you would like to deal with <code>BadResponseException</code> exceptions thrown by the Guzzle Http Client for 4xx and 5xx level http responses, you can enable http exceptions this way:</p>
<pre><code>PaymentProviderFactory::enableHttpExceptions();
</code></pre>
<h2 id="setting-request-parameters">Setting Request Parameters</h2>
<p>The package provides you with some flexibility in how you set your request parameters before they are sent to the configured Payment Provider.</p>
<p>For example, to bill a customer using Paystack, after configuring Paystack using the instructions above you could do this:</p>
<pre><code>$bill = new Bill("customer@email.com", 3000);     		//The Bill class always works with amounts in naira.
$payment_reference = $bill-&gt;charge()-&gt;getPaymentReference();
savePaymentReference($payment_reference); 			// save the generated reference to your database
$payment_page_url = $bill-&gt;getPaymentPageUrl(); 
header("Location: " . $payment_page_url);   			//redirect to Paystack's checkout page
</code></pre>
<p>Using the Bill constructor allows you to set the parameters that are required for that request but you might have additional parameters you’d like to send along with your request. For instance, you might want to set your own payment reference rather than use the reference that Paystack generates for you. In that instance you could do any of these:</p>
<p><strong>Using Magic set methods</strong></p>
<pre><code>$bill = new Bill();
$bill-&gt;setCustomerEmail("customer@email.com")
     -&gt;setReference("reference")
     -&gt;setAmount("40000")
     -&gt;setCallbackUrl($callback_url);
$payment_page_url = $bill-&gt;charge()-&gt;getPaymentPageUrl()
header("Location: ". $payment_page_url);
</code></pre>
<p>Parameters set this way will automatically be rendered and sent to the configured payment provider with snake_case.</p>
<p>You can use kebab-case like so:</p>
<pre><code>$bill-&gt;setCallbackUrl($callback_url, 'kebab-case'); 
</code></pre>
<p><strong>Using Magic properties</strong></p>
<pre><code>$bill = new Bill();
$bill-&gt;customer_email = "customer@email.com";
$bill-&gt;reference = "unique_reference";
$bill-&gt;callback_url = $callback_url;  

//if you are working with Paystack, 
//set the amount in kobo, 
//if you want to work with naira amounts (with Paystack)
//set the naira_amount property instead like so:
$bill-&gt;amount = 40000; 
$bill-&gt;naira_amount = 400;

$payment_page_url = $bill-&gt;charge()-&gt;getPaymentPageUrl()

//Redirect to the payment page. 
//If you are using a framework, use your framework's redirect method
header("Location: ". $payment_page_url); 
</code></pre>
<p><strong>Using an associative array of parameters</strong></p>
<pre><code>$bill_data = [
    "customer_email" =&gt; "customer@email.com",
    "reference" =&gt; "unique_reference",
    "amount" =&gt; "4000",
    "callback_url" =&gt; $callback_url
  ];
$bill = new Bill($bill_data);
</code></pre>
<p>Check the documentation for the provider you are integrating with to learn more<br>
about the request options needed for various endpoints.</p>
<h2 id="billing">Billing</h2>
<p>To charge a customer an amount of naira for a product, say N7000, follow these steps:</p>
<p><strong>1) Initialize the payment and redirect the customer to the payment page</strong></p>
<pre><code>$bill = new Bill($customer_email, 7000); 
$reference = $bill-&gt;charge()-&gt;getPaymentReference(); //make sure to call the charge() method on bill first
savePaymentReferenceToDB($reference);
$payment_page_url = $bill-&gt;getPaymentPageUrl();
header("Location: ". $payment_page_url);
</code></pre>
<p><strong>2) Verify that that the customer paid what they were expected to pay</strong></p>
<p>You would typically do this step in response to a request from either <a href="https://developers.paystack.co/docs/paystack-standard">Paystack</a>  or <a href="https://developer.flutterwave.com/docs/rave-standard">Rave</a> to the callback_url you set in your API dashboard or with your request using <code>$bill-&gt;setCallbackUrl($url)</code> (see their docs for more information).</p>
<pre><code>$reference = getPaymentReferenceFromSomewhere(); 
if(Bill::isPaymentValid($reference, 7000)){
    //send product to customer or activate account etc.
}
</code></pre>
<p>If the Payment is not valid, by default a <code>FailedPaymentException</code> is thrown which you can use to log details about the request and the response received from the PaymentProvider like so:</p>
<pre><code>try{
  if(Bill::isPaymentValid($reference, 7000)){
    //do something
   }
}catch(FailedPaymentException $e){
  logFailedPayments($reference, $e-&gt;getResponse());
  //do any additional processing
}
</code></pre>
<p>If you don’t want to handle this type of exceptions, you can disable this using</p>
<pre><code>PaymentProviderFactory::disablePaymentExceptions(); 
</code></pre>
<h2 id="recurring-billing">Recurring Billing</h2>
<p>Both Rave and Paystack support recurring billing through the use of tokens. To charge a customer with recurring billing, you have to ensure that customer has made at least one payment to you so that your PaymentProvider can generate the token to send you for use in future billings.</p>
<p>To charge a customer using Recurring Billing follow these steps:</p>
<p><strong>1) Initialize a regular payment</strong></p>
<pre><code>$bill = new Bill($customer_email, 7000);
$reference = $bill-&gt;charge()-&gt;getPaymentReference();
$payment_page_url = $bill-&gt;getPaymentPageUrl();
redirect_to_url($payment_page_url) //if you are using a framework, use your framework's redirect method
</code></pre>
<p><strong>2) Validate the payment and store the authorization code or token from your payment provider</strong></p>
<pre><code>$authorization_code = Bill::getPaymentAuthorizationCode($reference, 7000); //This method validates the payment made by the customer and returns the authorization code (token) if the payment is valid
</code></pre>
<p><strong>3) Create an AuthBill and charge the customer anytime you need to</strong><br>
Note that the authorization_code you get from your provider is only valid for the specified customer’s email.</p>
<pre><code>$auth_bill = new AuthBill($authorization_code, $customer_email, 3000); 
$reference = $auth_bill-&gt;charge(); //if the payment is successful, this returns a reference, if it fails, it returns null
</code></pre>
<h2 id="split-billing">Split Billing</h2>
<p>To share a customer’s payment with a merchant you need to first create a sub account for the merchant, and then use the subaccount id when billing the customer. Check your payment provider’s documentation to learn more about subaccounts.</p>
<p><strong>1) Creating and storing the subaccount</strong></p>
<pre><code>$subaccount = new Subaccount($business_name, $settlement_bank, $account_number, $percentage_charge); 

//the save method will create a subaccount 
//with your payment provider 
//and return the id or code of the created subaccount
$subaccount_id = $subaccount-&gt;save();
 
saveSubaccountIdToDatabase($subaccount_id)
</code></pre>
<p>To get a list of banks and their corresponding codes to use when creating subaccounts, you can call the <code>Subaccount::fetchBanks()</code> method.</p>
<p>Paystack requires you to pass in the bank name when creating the subaccounts while Rave requires you to pass in the bank code</p>
<p>Like with bills, you could also use magic setters and magic properties to set your request variables for subaccounts.</p>
<p><strong>2) Split a bill with the created subaccount</strong></p>
<pre><code>$subaccount_id = retrieveSubaccountIdFromDatabase()
$bill = new Bill($customer_email, 4000);
$reference = $bill-&gt;splitCharge($subaccount_id)-&gt;getPaymentReference();
$payment_page_url = $bill-&gt;getPaymentPageUrl();
</code></pre>
<p>You could also split Authorization code payments with subaccounts</p>
<pre><code>$subaccount_id = retrieveSubaccountIdFromDatabase()
$auth_bill = new AuthBill($authorization_code, $customer_email, 4000);
$reference = $auth_bill-&gt;splitCharge($subaccount_id);
</code></pre>
<p><strong>3) Validate the payment</strong></p>
<pre><code>$is_valid = Bill::isPaymentValid($reference, 4000); 
</code></pre>
<h2 id="plans-and-subscriptions">Plans and Subscriptions</h2>
<p>You could also create payment plans with your payment provider and subscribe customers to those payment plans.</p>
<blockquote>
<p>Note that, if you do this, the customer will be able to cancel their<br>
subscriptions directly with either Paystack or Rave, so you’d need to regularly<br>
check to ensure that a customer has not cancelled their subscription<br>
before you give value.</p>
</blockquote>
<p><strong>1) Create the Payment Plan</strong></p>
<pre><code>$plan = new Plan("My Plan", 3000, "weekly");
$plan_id = $plan-&gt;save(); 
</code></pre>
<p><strong>2) Subscribe a customer to the payment plan</strong></p>
<pre><code>$bill = new Bill($customer_email, 3000);            //the plan amount will always override the bill amount in this case
$reference = $bill-&gt;subscribe($bill)-&gt;getPaymentReference(); 
$payment_page_url = $bill-&gt;getPaymentPageUrl();   
header("Location: " . $payment_page_url);          //redirect to the payment page
</code></pre>
<h2 id="integrating-with-paystack">Integrating with Paystack</h2>
<p>When you create a Plan or a Subaccount with Paystack, Paystack returns two forms of identifying these objects: An alphanumeric code and an id.</p>
<p>When you make a call to either <code>$plan-&gt;save()</code> or <code>$subaccount-&gt;save()</code> methods, you are going to be getting the alphanumeric code. The reason for this is that while you can identify plans and subaccounts with the numeric id, you can’t use that numeric id to initialize payments. You need the code for that.</p>
<p>So when you save a subaccount or plan, you’ll be getting the alphanumeric code which you can then store in your database for use in future transactions.</p>
<h2 id="integrating-with-rave">Integrating with Rave</h2>
<p><strong>Creating Subaccounts with Rave</strong><br>
The Subaccount class that ships with this package has 4 default arguments in its constructor</p>
<pre><code>class Subaccount implements ApiDataMapperInterface
{
  ...
  public function __construct(
      $business_name = null,
      string $settlement_bank = null,
      string $account_number = null,
      $percentage_charge = null
  ){...}
...
</code></pre>
<p>In the documentation for Rave’s <a href="https://developer.flutterwave.com/v2.0/reference#create-subaccount">Create Subaccount</a> endpoint you will find that Rave requires you to set more parameters than this. The package helps you set the <code>split_type</code> to <code>percentage</code> and the <code>country</code> to <code>NG</code>.  The <code>$percentage_charge</code> in the constructor is used to set the <code>split_value</code> required by Rave. This is fine if you want to work with percentage splits.</p>
<p>To create a Subaccount with Rave you would still need to set the <code>business_mobile</code> and the <code>business_email</code> yourself, in order to successfully create a subaccount when you call the <code>$subaccount-&gt;save()</code> method.  So to create a subaccount with Rave you would do this:</p>
<pre><code>$subaccount = new Subaccount(
	     "Business Name", 
	      $settlement_bank, 
	      $merchant_account_number, 	
	      $percentage_charge
	   );
$subaccount-&gt;business_mobile = $business_mobile;
$subaccount-&gt;business_email = $business_email;
$subaccount_id = $subaccount-&gt;save(); 
</code></pre>
<p>Also note that when you save a subaccount, you are going to be getting the subaccount’s alphanumeric id which you can use to initialize payments later with either <code>Bill</code> or <code>AuthBill</code>.</p>

