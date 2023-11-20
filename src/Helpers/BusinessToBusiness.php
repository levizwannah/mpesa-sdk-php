<?php
namespace LeviZwannah\MpesaSdk\Helpers;

class BusinessToBusiness extends MpesaWithInitiator {

    /**
     * Receiver Short code
     * @var string
     */
    public string $receiver;

    /**
     * Amount that was paid.
     * @var int
     */
    public int $amount;


    /**
     * The customer on whose behalf the money
     * is paid.
     * @var string
     */
    public string $requester = "";

    /**
     * The account number
     * @var string
     */
    public string $account = "default";

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
    public string $remarks = "business payment";

    /**
     * Occasion for reversal
     * @var string
     */
    public string $occasion = "business payment";

    /**
     * Command ID
     */
    public string $type = Constant::BUSINESS_BUYGOODS;

    protected string $resourcePath = "/mpesa/b2b/v1/paymentrequest";

    protected $identifierType = 4;

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
        if(isset($config["requester"])) $this->requester($config["requester"]);
        return $this;
    }

    /**
     * @param int $number Paybill or Till Number
     */
    public function receiver($number) {
        $this->receiver = $number;
        return $this;
    }

    /**
     * @param string $number The account Number of paybill
     */
    public function account(string $number) {
        $this->account = $number;
        return $this;
    }

    /**
     * @param string $number The account Number of paybill
     */
    public function reference(string $number) {
        $this->account = $number;
        return $this;
    }

    /**
     * Sets the customer phone number on behalf of whom you are paying.
     * Accepts 07xxxxxxxx, +2547xxxxxxxxx, or 2547xxxxxxxxx
     * @param string $phone
     * 
     */
    public function requester(string $phone){
        $phone = "254" . substr(preg_replace("/\s+/", "", $phone), -9);
        $this->requester = $phone;
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
     * Sets the type. Must be one of: BusinessBuyGoods or BusinessPayBill
     * @param string $type
     * 
     */
    protected function type(string $type){
        $this->type = $type;
        return $this;
    }

    /**
     * Sets the type to Business BuyGoods
     */
    public function buygoods() {
        $this->identifierType = 2;
        return $this->type(Constant::BUSINESS_BUYGOODS);
    }

    /**
     * Sets the type to Business Paybill
     */
    public function paybill() {
        $this->identifierType = 4;
        return $this->type(Constant::BUSINESS_PAYBILL);
    }

    public function okay()
    {
        parent::okay();
        $this->assertExists("receiver", "Receiver Paybill/Till");
        $this->assertExists("amount", "Amount");
        $this->assertExists("type", "Type of Payment");
        $this->assertExists("resultUrl", "Result URL");
        $this->assertExists("timeoutUrl", "Timeout URL");
        $this->assertExists("remarks", "Remarks");
        $this->assertExists("account", "Account Number");

        return true;
    }

    /**
     * Makes the B2B request to Mpesa
     */
    public function pay(){
        $this->okay();

        $data = [
            "Initiator" => $this->initiator,
            "SecurityCredential" => $this->credential,
            "CommandID" => $this->type,
            "SenderIdentifierType" => 4,
            "RecieverIdentifierType" => $this->identifierType,
            "Amount" => $this->amount,
            "PartyA" => $this->code,
            "PartyB" => $this->receiver,
            "AccountReference" => $this->account,
            "Requester" => $this->requester,
            "ResultURL" => $this->resultUrl,
            "QueueTimeOutURL" => $this->timeoutUrl,
            "Remarks" => $this->remarks,
            "Occasion" => $this->occasion 
        ];

        $this->response = $this->request($data, $this->resourcePath);

        return $this;
    }

    /**
     * Makes the B2B request and returns the response.
     * @return object
     */
    public function __invoke()
    {
        $this->pay();
        return $this->response();
    }
}