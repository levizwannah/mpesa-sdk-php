# Mpesa SDK for PHP
A **Clean, Elegant, and Independent** PHP SDK for interacting with Safaricom Daraja APIs. The SDK is purely object-oriented and easy to understand and use. It has no dependency and hence can be used in any PHP project: plain or framework-based.
# Requirement
- PHP version >= 7.4
## Get it with Composer
```composer require levizwannah/mpesa-sdk-php```

And then start using it.

## not using composer
Download the zip version of the code. Include the `self-autoload.php` file located in the downloaded `src` folder.

# Documentation
## Setting Up
Firstly create an Mpesa object with the necessary configurations.  

**The consumer key, consumer secret, and business short code is always required.**  

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
## Mpesa Express (STK Push) API
Used for initiating STK Push requests. 
> Remember this could throw an exception. Check the exceptions section under Setting up.

```php
//..setup...

$stk = $mpesa->stk();

// for till numbers
// ensure that the till number is set during setup
// or set it up using the below code
// $stk->till(123455)

$stk->phone('0724786543')
    ->amount(1)
    ->buygoods()
    ->callback('https://my-domain.com/path/to/callback')
    ->push();

// for paybill numbers
// change buygoods() to paybill()
// by default, it's paybill() anyway
$stk->phone('0724786543')
    ->amount(1)
    ->paybill()
    ->callback('https://my-domain.com/path/to/callback')
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

## C2B URLs Registration API
Enables you to register your C2B urls. The SDK also provides an easy response method for your confirmation and validation scripts.
> The validation url is not required unless you explicitly ask the Mpesa team to enable it for you.  

> This API requires the consumer key (`key`), consumer secret (`secret`), and business short code (`code`) to be set when making request.

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
### Handler helpers
When Mpesa sends a payload to your confirmation or validation URLs, you need to send a formatted confirmation or denial payload. You can use the SDKs static methods for that. See below.

```php
require('path/to/vendor/autoload.php');

use LeviZwannah\MpesaSdk\Mpesa;

# confirmation.url

// your code ...
Mpesa::confirm();
// your code...

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
## Transaction Query API
## Balance Query API
## B2B API
## B2C API
## Tax Remittance API
## Dynamic QR Code API


# Reporting Errors
Please open an issue in case there is a bug found.
