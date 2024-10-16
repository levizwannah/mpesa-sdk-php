# Mpesa SDK for PHP
A **Clean, Elegant, and Independent** PHP SDK for interacting with Safaricom Daraja APIs. The SDK is purely object-oriented and easy to understand and use. It has no dependency and hence can be used in any PHP project: plain or framework-based.

# Requirement
- PHP version >= 7.4
- You have read the documentation of the Daraja API: This SDK mainly abstracts everything for you and makes it easy to integrate M-Pesa. So, ensure you have looked at the Daraja documentation. Not to understand it, but just know what data are being sent and received. That makes it easy to understand this SDK.

## Get it with Composer
```composer require levizwannah/mpesa-sdk-php```

And then start using it.

## not using composer
Download the zip version of the code. Include the `self-autoload.php` file located in the downloaded `src` folder.

# Documentation
## Setting Up
Firstly create an Mpesa object with the necessary configurations.  

**The consumer key, consumer secret, and business short code are always required.**  

*If you are using a till number, then the `till` key is required, otherwise only the business short code is required.*  

> Note: *The business short code is the same as the Paybill number. For till numbers, it is different.*  

```php
require('path/to/vendor/autoload.php');

use LeviZwannah\MpesaSdk\Mpesa;

$config = [
  "key" => "consumer-key", // consumer key
  "secret" => "consumer-secret", // consumer secret
  "code" => "12345", // business short code
  
  "till" => "67891", // optional till number
  "initiator" => "levizwannah", // optional initiator name
  "credential" => "levi-cred++=="  // optional security credential
]

$mpesa = Mpesa::new()->configure($config);

```
### Method chaining vs configure($configure)
Every key in the `$config` array is a setter method for the object. For example, the below code is equivalent to the one above.  

```php 
require('path/to/vendor/autoload.php');

use LeviZwannah\MpesaSdk\Mpesa;

$mpesa = Mpesa::new();

$mpesa->key("consumer-key")
      ->secret("consumer-secret")
      ->code(12345)
      ->till(67891) // optional
      ->passkey('stk-passkey') // optional
      ->initiator("levizwannah") // optional
      ->credential("levi-cred++=="); // optional
```
Every object for interacting with a specific API extends the parent Mpesa object. Therefore, the same rule applies to them. You can use a config array and the configure method or just use the individual setter methods.  

>**Warning**: Do not directly create an object from a child class, always use the mpesa object to get the child object as you will see later in the doc.  

### Environment
You can use the `'env'` key in the `$config` array or the `env("env")` method to set the environment. The environment value can be `live` or `sandbox`. By default, the environment is `"live"`. There is a `Constant` class to save you from writing the literal strings.

```php
//...other code
use LeviZwannah\MpesaSdk\Helpers\Constant;

$mpesa->configure([
    'env' => Constant::SANDBOX, 
    // or
    'env' => Constant::LIVE, // default value
]);

// or
$mpesa->env(Constant::SANDBOX);
$mpesa->env(Constant::LIVE); // default value
```
> ***Remember***: Every setter method used below can be used as a key in the `$config` array and set using the `configure($config)` method. 

### Phone Numbers
The SDK adds 254 to the phone number. The below formats are supported
```
0746987654
+254746987654
+2540746987654
254746987654
746987654

```
### Exceptions
The SDK throws an exception if a required data is missing during requests. For example, the initiator name and security credentials are required before making Reversal requests. If they are not found, an exception is thrown with a semantic message telling you what the problem is.

### Handling Responses
The SDK provides a uniform way to check the immediate Mpesa responses. Use the below snippet when interacting with any API using the SDK.

```php
//... request made

if($client->accepted()) {
    // ... mpesa accepted the request for processing
    // This means the ResponseCode == 0
    $response = $client->response();
}
else {
    // The response code is not == 0 or there is an error.
    $error = $client->error(); // get an error object
    echo "error: $error->code, $error->message";
}

// you can always get the latest response using
$response = $client->response(); // returns an object

$conversationId = $response->ConversationID;
$responseCode = $response->ResponseCode;
// ...

```
>**Note:** The data in the response object is the same as it is in the Daraja Documentation. There is no additional formatting done.

## Security in Handling Callbacks
The SDK has two static helper methods to help you when handling mpesa responses in your callbacks.
1. `verifyOrigin():bool` : returns a true if the callback response is from Mpesa and false otherwise.
2. `data($asArray = false): object|array` : gets the callback data from mpesa and returns it as an object or array based on its argument.

```php
require('path/to/vendor/autoload.php');

use LeviZwannah\MpesaSdk\Mpesa;

$isFromMpesa = Mpesa::verifyOrigin();
$result = Mpesa::data();

//...
```
## Handling Callback Payloads
> Note: **Response** represents what your get from Mpesa when you make a request. **Result** is the payload sent to your callback URLs.

### Payload format
Please refer to the Daraja documentation at https://developer.safaricom.co.ke to see the expected payload. For forward compatibility, the SDK doesn't alter the responses or payload from Mpesa.

### The confusing part
In every **response**, there will be unique keys. For example, the `MerchantRequestID` and  `CheckoutRequestID` in the STK push response, and the `OriginatorConversationID` and `ConversationID` in the other APIs responses. These keys identify the transaction on Mpesa. Save these keys in your database or some storage alongside the pending transaction. 

In the **result** payload that will be sent to your callbacks, these keys will be present. Therefore, you can use them to update the corresponding transactions in your storage or database.

## Mpesa Express (STK Push) API
Used for initiating STK Push requests. 

### Requirements
Ensure these values were set as shown in the setup section:
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);
- Till Number (`till`) *For till numbers*
- Passkey (`passkey`)

### Usage
```php
//..setup...

$stk = $mpesa->stk();

// for till numbers
// ensure that the till number is set during setup
// or set it up using $stk->till(123455)

$stk->phone('0724786543')
    ->amount(1)
    ->buygoods() // for till numbers
    ->paybill() // for paybill numbers
    ->callback('https://my-domain.com/path/to/callback')
    ->description('optional description') // optional
    ->push();

// check if the request was accepted by mpesa.
if(!$stk->accepted()) {
    $error = $stk->error();
    echo "error: $error->code, $error->message";
    // exit...
}

// accepted for processing
$response = $stk->response(); // returns an object

$merchantRequestId = $response->MerchantRequestID;
$checkoutRequestId = $response->checkoutRequestID;

//...
```
Notice that the difference between using Till number and paybill number is the use of `paybill()` and `buygoods()` methods before calling the `push()` method. Also, ensure that `till` value is set when using Till numbers.

## Mpesa Express STK Query
Use for querying the status of STK push requests. This is different from normal transaction status queries.

### Requirements
Ensure these values were set as shown in the setup section:
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);
- Passkey (`passkey`)
- Till (`till`) for till accounts

### Usage
See the code snippet below
```php
//...setup...

$query = $mpesa->stk()->query();

$query->checkoutId('checkout-request-id')
      ->paybill() // for paybill number
      ->buygoods() // for till numbers
      ->make();

// check if it's not accepted
if(!$query->accepted()){
  $error = $query->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $query->response();
$resultCode = $response->ResultCode;
$merchantId = $response->MerchantRequestID;
// ...
```
> **Note**: Use `paybill()` if you are querying an STK request made for a paybill number, otherwise use `buygoods()`. By default, it queries for STK requests made for paybill numbers.  

## C2B URLs Registration API
Enables you to register your C2B urls. The SDK also provides an easy response method for your confirmation and validation scripts.

> The `validation url` is not required unless you explicitly ask the Mpesa team to enable it for you.  

### Requirements
Ensure these values were set as shown in the setup section:
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Registration

Look at the code snippet below.
```php
// ...setup...
// c2b url registration
$urls = $mpesa->urls();

$urls->confirmation('https://my.url/path/to/confirmation')
     ->validation('https://my.url/path/to/validation') // optional
     ->register();

if(!$urls->accepted()) {
  $error = $urls->error();
  echo "Error: $error->code - $error->message";
  // exit;
}

```
### Callback helpers
When Mpesa sends a payload to your confirmation or validation URLs, you need to send a formatted confirmation or denial payload. You can use the SDKs static methods for that. See below.

```php
require('path/to/vendor/autoload.php');

use LeviZwannah\MpesaSdk\Mpesa;

# confirmation.url

// your code ...
Mpesa::confirm();
// your code...
// send SMS ... etc

#================#

# validation.url

// your code ...
if(!true) {
    Mpesa::deny();
}
else {
    Mpesa::confirm();
}

// your code ...

```
> Note: You can only use `Mpesa::deny()` in the validation handler.

## Reversal API
Enables you to make Reversals of Mpesa Transactions.
### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

See the code snippet on how to use this SDK.
```php
// ...setup...

$reversal = $mpesa->reversal();

$reverser->timeoutUrl('https://my.url/path/to/reversal/timeout')
        ->resultUrl('https://my.url/path/to/reversal/result')
        ->transId('1X1Y1ZNME') // transaction ID to reverse
        ->amount(100) // amount paid
        ->remarks('optional remarks') // optional
        ->occasion('optional occasion') // optional
        ->make();

if(!$reversal->accepted()) {
  $error = $reversal->error();
  echo "$error->code - $error->message";
  // exit;
}

// reversal initiated
$response = $reversal->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...

```
> In your callbacks scripts, please ensure to follow the recommendation in the **security section** of this doc.

## Transaction Query API
The Transaction query API enable you to check the statuses of transactions made to or by your business short code.

### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
See the code snippet below on how to use this SDK.
```php
// Transaction query
$query = $mpesa->query();

$query->transId('1X1Y1ZNME')
      ->resultUrl('https://my.url/path/to/timeout')
      ->timeoutUrl('https://my.url/path/to/result')
      ->remarks('optional remarks') // optional
      ->occasion('optional occasion') // optional
      ->make();

if(!$query->accepted()) {
  $error = $query->error();
  echo "$error->code - $error->message";
  // exit;
}

$response = $query->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...
```
## Balance Query API
The balance Query API allows you to query the balance in a business account.

### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
See the below code snippet
```php
//...setup...

$balance = $mpesa->balance();

$balance->timeoutUrl('https://my.url/path/to/timeout')
        ->resultUrl('https://my.url/path/to/result')
        ->remarks('optional remarks') // optional
        ->check();

if(!$balance->accepted()){
  $error = $balance->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $balance->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...

```
## B2B API
The B2B API allows you to make payments to paybill or till numbers
from your business short code.

### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
Look at the code snippet below
```php
// ...setup...
$b2b = $mpesa->b2b();
$b2b->amount(100)
    ->receiver('123456') // business you are paying to
    ->resultUrl('https://my.url/path/to/result')
    ->timeoutUrl('https://my.url/path/to/timeout');

# if receiver is a paybill number
$b2b->paybill() // if receiver is a paybill number
    ->account('account-number'); 

# if receiver is a till number
$b2b->buygoods(); // if receiver is a till number

# optional
$b2b->remarks('optional remarks') // optional
    ->occasion('optional occasion') // optional
    ->requester('0712345678'); // optional - the customer on
                               // whose behalf the money is
                               // being paid.

# make payment
$b2b->pay();

if(!$b2b->accepted()) {
  $error = $b2b->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $b2b->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...

//... save to db, etc
```
## B2C API
The B2C API allows you to make payments mobile numbers
from your business short code.

### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
See the code snippet below:
```php
$b2c = $mpesa->b2c();

$b2c->amount(100)
    ->phone('0712345678')
    ->resultUrl('https://my.url/path/to/result')
    ->timeoutUrl('https://my.url/path/to/timeout');

# set payment purpose
$b2c->salary() // for salary payment
    ->promotion() // for promotion payment
    ->payment(); // for business payment

# optional
$b2c->remarks('optional-remarks')
    ->occasion('optional-occasion');

# pay
$b2c->pay();

if(!$b2c->accepted()) {
  $error = $b2c->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $b2c->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...

//... save to db, etc

```
## Tax Remittance API
The Tax Remittance API allows you to make Tax payment to KRA

### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
See the code snippet below
```php
// ...setup...

$remit = $mpesa->remitTax();

$remit->amount(1000)
      ->resultUrl('https://my.url/path/to/result')
      ->timeoutUrl('https://my.url/path/to/timeout')
      ->remarks('optional-remarks') // optional
      ->pay();

if(!$remit->accepted()) {
  $error = $remit->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $remit->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...

//... save to db, etc

```
## Dynamic QR Code API
Enables you to generate QR Code for different transactions. Please see the Daraja documentation

`
Use this API to generate a Dynamic QR which enables Safaricom M-PESA customers who have My Safaricom App or M-PESA app, to scan and capture till number and amount then authorize to pay for goods and services at select LIPA NA M-PESA (LNM) merchant outlets.` -- Daraja

### Requirements
Ensure these values were set as shown in the setup section:
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`)

### Usage
See the snippet below
```php
//...setup...
$qr = $mpesa->qr();

$qr->size(300) // QR Code size (300x300)
   ->merchantName('Business Name')
   ->reference('account-number') // transaction reference
   ->amount(100);

# the receiver of the payment
# can be Till Number, Agent number, phone number
# paybill number, or business number
# Correspond to CPI in the Daraja doc
$qr->receiver('123457');

# sets the receiver type
# corresponds to TrxCode in the Daraja Doc
$qr->buygoods(); // receiver is a till number
$qr->paybill(); // receiver is a paybill number
$qr->sendMoney(); // receiver is a phone number
$qr->withdraw(); // receiver is an agent number
$qr->sendToBusiness(); // receiver is a business number

# generate code
$qr->generate();

if(!$qr->accepted()) {
  $error = $qr->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $qr->response();
$qrCode = $response->QRCode;
$requestId = $response->RequestID;
//...
```

## Business To Bulk API
This API enables you to load funds from a working account directly to a utility account for B2C payments.

### Requirements
Ensure these values were set as shown in the setup section:
- Initiator name (`initiator`)
- Security credential (`credential`)
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
```php
// ...setup...
$btb = $mpesa->btb();
$btb->amount(100)
    ->receiver('123456') // short code of the receiver 
    ->resultUrl('https://my.url/path/to/result')
    ->timeoutUrl('https://my.url/path/to/timeout');

# if in the same organization use
$btb->amount(100)
    ->toSelf() // accepts an optional short code param for sub-organizations
    ->resultUrl('https://my.url/path/to/result')
    ->timeoutUrl('https://my.url/path/to/timeout');

# optional
$btb->remarks('optional remarks') // optional
    ->requester('0712345678'); // optional - the customer on
                               // whose behalf the transfer is
                               // being made.

# make the transfer
$btb->transfer();

if(!$btb->accepted()) {
  $error = $btb->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $btb->response();
$originatorId = $response->OriginatorConversationID;
$conversationId = $response->ConversationID;
//...

//... save to db, etc
```

## Mpesa Ratiba (Subscription)
This API enables you to create Mpesa Standing Orders. Take it as a subscription API for Mpesa

### Requirements
Ensure these values were set as shown in the setup section:
- Consumer Key (`key`)
- Consumer Secret(`secret`)
- Business Short Code (`code`);

### Usage
```php
// ...setup...
$subscription = $mpesa->subscription(); // same as $mpesa->ratiba();

$subscription->amount(100)
    ->plan("Gold Plan") // Standing order Name (same as $sub->name('Gold Plan'))
    ->phone('0740958756')
    ->startDate(10, 10, 2024) // month, day, year
    ->endDate(10, 11, 2024) // month, day, year
    ->frequency(Constant::FREQ_DAILY)
    ->callback('https://my.url/path/to/result');

# if your code is paybill number
$subscription->paybill() // if paybill number
    ->account('account-number'); 

# if using a till till number
$subscription->buygoods()
    ->till(1234567); // if till number

# optional
$subscription->description('optional description');

# create subscription
$subscription->create();

if(!$subscription->accepted()) {
  $error = $subscription->error();
  echo "$error->code $error->message";
  // exit;
}

$response = $subscription->response();
$refId = $response->responseRefID;
//...

//... save to db, etc
```

# Quick Note
If you are confused on how to handle the results in the callback, please read the earlier sections of this README file.

# Reporting Errors
Please open an issue in case there is a bug found.

# Show Love
- Star this repository
- if you love it, buy me coffee.
