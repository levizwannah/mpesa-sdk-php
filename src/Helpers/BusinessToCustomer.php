<?php

    namespace LeviZwannah\MpesaSdk\Helpers;

/**
 * Represents the B2C transaction
 * @package levizwannah/mpesa-sdk-php
 */
    class BusinessToCustomer extends MpesaWithInitiator{
       
        /**
         * Phone number of the customer
         * @var string
         */
        public string $phone;

        /**
         * Amount that was paid.
         * @var int
         */
        public int $amount;


        /**
         * The result URL
         * 
         * @var string
         */
        public string $resultUrl;

        /**
         * The timeout URL
         * @var string
         */
        public string $timeoutUrl;

        /**
         * Remarks for the reversal
         * @var string
         */
        public string $remarks = "payment";

        /**
         * Occasion for reversal
         * @var string
         */
        public string $occasion = "payment";

        public string $type = Constant::BUSINESS;

        /**
         * You should not call this directly. Use $mpesa->b2c()
         * @param array $config
         */
        public function __construct(array $config)
        {
            $this->configure($config);
        }

        public function configure(array $config)
        {
            parent::configure($config);
            if(isset($config["phone"])) $this->phone($config["phone"]);
            return $this;
        }

        /**
         * Sets the phone to pay to.
         * Accepts 07xxxxxxxx, +2547xxxxxxxxx, or 2547xxxxxxxxx
         * @param string $phone
         * 
         */
        public function phone(string $phone){
            if($phone[0] == "+") $phone = substr($phone, 1);
            if($phone[0] == "0") $phone = substr($phone, 1);
            if($phone[0] == "7") $phone = "254" . $phone;

            $this->phone = $phone;
            return $this;
        }

        /**
         * Sets the amount
         * @param mixed $amount
         * 
         */
        public function amount($amount){
            $this->amount = (int)$amount;
            return $this;
        }

        /**
         * Sets Result URL
         * @param string $resultUrl
         * 
         */
        public function resultUrl(string $resultUrl){
            $this->resultUrl = $resultUrl;
            return $this;
        }

        /**
         * Sets the timeout URL
         * @param string $timeoutUrl
         * 
         */
        public function timeoutUrl(string $timeoutUrl){
            $this->timeoutUrl = $timeoutUrl;
            return $this;
        }

        /**
         * Sets the remarks
         * @param string $remarks
         * 
         */
        public function remarks(string $remarks){
            $this->remarks = $remarks;
            return $this;
        }

        /**
         * Sets the occasion
         * @param string $occasion
         * 
         */
        public function occasion(string $occasion){
            $this->occasion = $occasion;
            return $this;
        }

        /**
         * Sets the type. Must be one of: SalaryPayment, BusinessPayment, PromotionPayment
         * @param string $type
         * 
         */
        public function type(string $type){
            $this->type = $type;
            return $this;
        }

        public function okay()
        {
            parent::okay();
            $this->assertExists("phone", "Phone Number");
            $this->assertExists("amount", "Amount");
            $this->assertExists("type", "Type of Payment");
            $this->assertExists("resultUrl", "Result URL");
            $this->assertExists("timeoutUrl", "Timeout URL");
            $this->assertExists("remarks", "Remarks");

            return true;
        }

        /**
         * Makes the B2C request to Mpesa
         */
        public function pay(){
            $this->okay();

            $data = [
                "InitiatorName" => $this->initiator,
                "SecurityCredential" => $this->credential,
                "CommandID" => $this->type,
                "Amount" => $this->amount,
                "PartyA" => $this->code,
                "PartyB" => $this->phone,
                "ResultURL" => $this->resultUrl,
                "QueueTimeOutURL" => $this->timeoutUrl,
                "Remarks" => $this->remarks,
                "Occasion" => $this->occasion 
            ];

            $this->response = $this->request($data, "/mpesa/b2c/v1/paymentrequest");

            return $this;
        }

        /**
         * Makes the B2C request and returns the response.
         * @return object
         */
        public function __invoke()
        {
            $this->pay();
            return $this->response();
        }
        
    }

?>