<?php
    namespace LeviZwannah\MpesaSdk\Helpers;

    
/**
 * Keeps the Constants to be used in order to avoid typos.
 * @package levizwannah/mpesa-sdk-php
 */
    class Constant{

        private function __construct(){}

        const RESULT_CODE = "ResultCode";
        const RESULT_DESC = "ResultDesc";

        // ENVIRONMENT
        const LIVE = "live";
        const SANDBOX = "sandbox";

        // STK
        const BUY_GOODS_ONLINE = "CustomerBuyGoodsOnline";
        const PAY_BILL_ONLINE = "CustomerPayBillOnline";

        // B2C
        const SALARY = "SalaryPayment";
        const BUSINESS = "BusinessPayment";
        const PROMOTION = "PromotionPayment";

        // Register URLs
        const URL_COMPLETE = "Completed";
        const URL_CANCELLED = "Cancelled";
    }

?>
