<?php
    namespace LeviZwannah\MpesaSdk\Helpers;

    /**
     * Reversal class
     * @package levizwannah/mpesa-sdk-php
     */
    class Reversal extends MpesaWithInitiator{

        /**
         * The Mpesa Transaction ID
         * 
         * @var string
         */
        public string $transId;


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
        public string $remarks = "normal reversal";

        /**
         * Occasion for reversal
         * @var string
         */
        public string $occasion = "reversal";

        /**
         * Receiver Identifier Type
         * @var string
         */
        public string $type = "11";
        /**
         * Do not call this directly.
         * use $mpesa->reversal()
         * @param array $config
         */
        public function __construct(array $config)
        {
            $this->configure($config);
        }

        /**
         * Sets the transaction ID
         * @param string $transactionId
         * 
         */
        public function transId(string $transactionId){
            $this->transactionId = $transactionId;
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
         * Sets the receiver Identifier Type
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
            $this->assertExists("transId", "Transaction ID");
            $this->assertExists("amount", "Amount");
            $this->assertExists("resultUrl", "Result URL");
            $this->assertExists("timeoutUrl", "Timeout URL");
            $this->assertExists("type", "Receiver Identifier Type");
            $this->assertExists("remarks", "Remarks");

            return true;
        }

        /**
         * Makes the reversal request
         */
        public function make(){
            $this->okay();

            $data = [
                "CommandID" => "TransactionReversal",
                "Initiator" => $this->initiator,
                "SecurityCredential" => $this->credential,
                "TransactionID" => $this->transId,
                "Amount" => $this->amount,
                "ReceiverParty" => $this->code,
                "ReceiverIdentifierType" => $this->type,
                "ResultURL" => $this->resultUrl,
                "QueueTimeOutURL" => $this->timeoutUrl,
                "Remarks" => $this->remarks,
                "Occasion" => $this->occasion
            ];

            $this->response = $this->request($data, "/mpesa/reversal/v1/request");

            return $this;
        }

        /**
         * Makes the reversal request
         * @return object
         */
        public function __invoke()
        {
            $this->make();
            return $this->response();
        }

    }

?>