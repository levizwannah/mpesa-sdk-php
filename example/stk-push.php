<?php



    require(__DIR__ ."/../vendor/autoload.php");

    use LeviZwannah\MpesaSdk\Mpesa;

    $mpesa = Mpesa::new();
    $stk = $mpesa->key("123456")->secret("abcde")->stk()->passkey("abcde");

    $stk->code(123456)->phone("0734567890")->amount(100)->paybill()->callback("https://url.com")->push();

    $response = (array)$response

?>