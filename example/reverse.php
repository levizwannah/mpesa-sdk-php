<?php
     require(__DIR__ ."/../vendor/autoload.php");

     use LeviZwannah\MpesaSdk\Mpesa;

     $config = [
        "key" => "z0ZVvE0CTRFhq168MFRxcax9z1X2lg89",
        "secret" => "ykqGekHShdWYS09a",
        "code" => 4036231,
        "passkey" => "93b9f7c32f790cebdfd3590b842f965001481252ab9c24cb6da6f6862e8619e6",
        "till" => ""
    ];

    $mpesa = Mpesa::new()->configure($config);

    $reversal = $mpesa->reversal();

    $reversal->make();

    $response = $reversal();

?>