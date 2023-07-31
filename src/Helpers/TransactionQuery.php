<?php 
namespace LeviZwannah\MpesaSdk\Helpers;

class TransactionQuery extends MpesaWithInitiator {
    /**
     * Mpesa Transaction ID
     * @var string
     */
    public $transId;

    /**
     * Mpesa Result URL
     * @var string
     */
    public $resultUrl;

    /**
     * Mpesa Queue Timeout URL
     * @var string
     */
    public $timeoutUrl;

    /**
     * Remarks
     * @var string
     */
    public $remarks = "transaction query";

    /**
     * Occasion
     * @var string
     */
    public $occasion = "transaction query";

    /**
     * Mpesa Originator Conversation ID
     * @var string
     */
    public $conversationId;

    /**
     * Party A (short code or phone number)
     * @var string
     */
    public $partyA = null;


    /**
     * Receiver Identifier type
     * @var int
     */
    public $type = 4;

    /**
     * Do not call this directly.
     * use $mpesa->query()
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
        $this->transId = $transactionId;
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
     * Sets the receiver of the fund (partyA)
     * @param string $partyA
     * 
     */
    public function receiver(string $partyA){
        $this->partyA = $partyA;
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

    /**
     * Sets the originator conversation ID for the transaction
     * @param string $originatorConversationId
     */
    public function conversationId(string $originatorConversationId){
        $this->conversationId = $originatorConversationId;
        return $this;
    }

    public function okay()
    {
        parent::okay();
        $this->assertExists("transId", "Transaction ID");
        $this->assertExists("resultUrl", "Result URL");
        $this->assertExists("timeoutUrl", "Timeout URL");
        $this->assertExists("remarks", "Remarks");
        $this->assertExists("occasion", "Query Occasion");

        return true;
    }

    public function make(){
        $this->okay();

        $data = [
            "CommandID" => "TransactionStatusQuery",
            "Initiator" => $this->initiator,
            "SecurityCredential" => $this->credential,
            "TransactionID" => $this->transId,
            "OriginatorConversationID" => $this->conversationId,
            "PartyA" => !empty($this->partyA) ? $this->partyA : $this->code,
            "IdentifierType" => $this->type,
            "ResultURL" => $this->resultUrl,
            "QueueTimeOutURL" => $this->timeoutUrl,
            "Remarks" => $this->remarks,
            "Occasion" => $this->occasion
        ];

        $this->response = $this->request($data, "/mpesa/transactionstatus/v1/query");

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
