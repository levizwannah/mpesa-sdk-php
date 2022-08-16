<?php
    namespace LeviZwannah\MpesaSdk;

    
/**
 * Keeps the Constants to be used in order to avoid typos.
 * @package levizwannah/mpesa-sdk-php
 */
    class Constant{

        private function __construct(){}

        const RESULT_CODE = "ResultCode";
        const RESULT_DESC = "ResultDesc";

        const LIVE = "live";
        const SANDBOX = "sandbox";

        const BUY_GOODS_ONLINE = "CustomerBuyGoodsOnline";
        const PAY_BILL_ONLINE = "CustomerPayBillOnline";
    }

?>
