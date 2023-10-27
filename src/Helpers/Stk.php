<?php
    
    namespace LeviZwannah\MpesaSdk\Helpers;

    use Exception;
    use LeviZwannah\MpesaSdk\Mpesa;

/**
 * Stk push class
 */
    class Stk extends Mpesa{

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
        public string $reference = "default";

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
            $phone = "254" . substr(preg_replace("/\s+/", "", $phone), -9);
            $this->phone = $phone;
            return $this;
        }

        /**
         * Sets the Amount. Kindly note, amount will be converted to int.
         * @param mixed $amount
         * 
         */
        public function amount($amount){
            $this->amount = (int)$amount;
            return $this;
        }

        /**
         * Sets the account number
         * @param string $number
         * 
         */
        public function reference(string $number){
            $this->reference = $number;
            return $this;
        }

        /**
         * Sets the account number for paybill
         * @param string $number
         * 
         */
        public function account(string $number){
            $this->reference = $number;
            return $this;
        }

        /**
         * Sets the transaction description
         * @param string $description
         * 
         */
        public function description(string $description){
            $this->description = $description;
            return $this;
        }

        /**
         * Sets the command ID to buy goods.
         */
        public function buygoods(){
            $this->type = Constant::BUY_GOODS_ONLINE;
            return $this;
        }

        /**
         * Sets the command ID to paybill
         */
        public function paybill(){
            $this->type = Constant::PAY_BILL_ONLINE;
            return $this;
        }

        public function callback(string $callback){
            $this->callback = $callback;
            return $this;
        }

        public function push(){
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
         * Makes the STK push
         * @return object response
         */
        public function __invoke()
        {
            $this->push();

            return $this->response();
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

        
    }

?>
