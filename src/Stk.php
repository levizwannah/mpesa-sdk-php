<?php
    
    namespace LeviZwannah\MpesaSdk;

use Exception;

/**
 * Stk push class
 */
    class Stk extends Mpesa{

        /**
         * The Response of the STK push
         * 
         * @var object
         */
        public object $response;

        /**
         * Phone number making the payment
         * 
         * @var string
         */
        public string $phone;

        /**
         * The amount to pay
         * @var int
         */
        public int $amount;

        /**
         * The account Reference
         * @var string
         */
        public string $reference = "payment";

        /**
         * Callback Url
         * 
         * @var string
         */
        public string $callback;

        /**
         * Transaction description
         * @var string
         */
        public string $description = "payment";

        /**
         * The transaction type.
         * @var string
         */
        public string $type = Constant::PAY_BILL_ONLINE;

        /**
         * !!!Do not call this directly. It needs config from the main mpesa class.
         * User $mpesa->stk();
         * @param array<string,string> $config initial configurations for the stk push.
         * This is gotten from the parent Mpesa.
         */
        public function __construct(array $config)
        {
            $this->configure($config);
        }

        /**
         * Sets the phone to pay to.
         * Accepts 07xxxxxxxx, +2547xxxxxxxxx, or 2547xxxxxxxxx
         * @param string $phone
         * 
         */
        public function &phone(string $phone){
            if($phone[0] == "+") $phone = substr($phone, 1);
            if($phone[0] == "0") $phone = substr($phone, 1);
            if($phone[0] == "7") $phone = "254" . $phone;

            $this->phone = $phone;
            return $this;
        }

        /**
         * Sets the Amount. Kindly note, amount will be converted to int.
         * @param mixed $amount
         * 
         */
        public function &amount($amount){
            $this->amount = (int)$amount;
            return $this;
        }

        /**
         * Sets the transaction reference
         * @param string $reference
         * 
         */
        public function &reference(string $reference){
            $this->reference = $reference;
            return $this;
        }

        /**
         * Sets the transaction description
         * @param string $description
         * 
         */
        public function &description(string $description){
            $this->description = $description;
            return $this;
        }

        /**
         * Sets the command ID to buy goods.
         */
        public function &buygoods(){
            $this->type = Constant::BUY_GOODS_ONLINE;
            return $this;
        }

        /**
         * Sets the command ID to paybill
         */
        public function &paybill(){
            $this->type = Constant::PAY_BILL_ONLINE;
            return $this;
        }

        public function &callback(string $callback){
            $this->callback = $callback;
            return $this;
        }

        public function &push(){
            $this->okay();
            
            $timestamp = date("YmdHis");
            $password = base64_encode("$this->code$this->passkey$timestamp");
            
            $data = [
                "BusinessShortCode" => $this->code,
                "Password" => $password,
                "Timestamp" => $timestamp,
                "TransactionType" => $this->type,
                "Amount" => $this->amount,
                "PartyA" => $this->phone,
                "PartyB" => ($this->type == Constant::BUY_GOODS_ONLINE)? $this->till : $this->code,
                "PhoneNumber" => $this->phone,
                "CallBackURL" => $this->callback,
                "AccountReference" => $this->reference,
                "TransactionDesc" => $this->description
            ];

            $this->response = $this->request($data, "/mpesa/stkpush/v1/processrequest");
            return $this;
        }

        /**
         * Checks if all the required data to make an stk push exist and is not empty.
         * @return bool
         */
        public function okay(){
            parent::okay();
            
            if($this->type == Constant::BUY_GOODS_ONLINE 
            && empty($this->till)) {
                throw new Exception("Till Number is empty while using buy goods transaction type");
            }

            $this->assertExists("phone");
            $this->assertExists("amount");
            $this->assertExists("passkey");
            $this->assertExists("callback", "Callback URL");
            return true;
        }

        public function response(){
            return $this->response;
        }

        /**
         * If the request returned an error response, this method returns the error object.
         * @return RequestError|false
         */
        public function error(){
            return isset($this->response->errorCode) ?
                    new RequestError($this->response()->errorCode, 
                    $this->response()->errorDesc) 
                    : false;
        }

        /**
         * True if Mpesa accepted to make the STK push, false otherwise.
         * @return bool
         */
        public function success(){
            return isset($this->response()->ResultCode) 
            && $this->response()->ResultCode == 0;
        }
    }

?>
