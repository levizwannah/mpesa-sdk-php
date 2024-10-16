<?php

namespace LeviZwannah\MpesaSdk\Helpers;

class BusinessToBulk extends MpesaWithInitiator
{
    /**
     * Receiver Short code
     * @var string
     */
    public string $receiver;

    /**
     * Amount to transfer to the working account.
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
    public string $remarks = "utility top-up";

    /**
     * Command ID
     */
    public string $type = Constant::BUSINESS_TO_BULK;

    protected string $resourcePath = "/mpesa/b2b/v1/paymentrequest";

    protected $identifierType = 4;
    protected $receiverIdentifierType = 4;

    /**
     * You should not call this directly. Use $mpesa->btb()
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    public function configure(array $config)
    {
        parent::configure($config);
        if (isset($config["requester"])) $this->requester($config["requester"]);
        return $this;
    }

    /**
     * @param int $number Paybill or Till Number
     */
    public function receiver($number)
    {
        $this->receiver = $number;
        return $this;
    }

    public function toSelf(string $code = null)
    {
        $code ??= $this->code;
        $this->receiver = $code;
        $this->type = Constant::BUSINESS_MMF_UTILITY;

        return $this;
    }

    /**
     * Sets the customer phone number on behalf of whom you are paying.
     * Accepts 07xxxxxxxx, +2547xxxxxxxxx, or 2547xxxxxxxxx
     * @param string $phone
     * 
     */
    public function requester(string $phone)
    {
        $phone = "254" . substr(preg_replace("/\s+/", "", $phone), -9);
        $this->requester = $phone;
        return $this;
    }

    /**
     * Sets the amount
     * @param mixed $amount
     * 
     */
    public function amount($amount)
    {
        $this->amount = (int)$amount;
        return $this;
    }

    /**
     * Sets Result URL
     * @param string $resultUrl
     * 
     */
    public function resultUrl(string $resultUrl)
    {
        $this->resultUrl = $resultUrl;
        return $this;
    }

    /**
     * Sets the timeout URL
     * @param string $timeoutUrl
     * 
     */
    public function timeoutUrl(string $timeoutUrl)
    {
        $this->timeoutUrl = $timeoutUrl;
        return $this;
    }

    /**
     * Sets the remarks
     * @param string $remarks
     * 
     */
    public function remarks(string $remarks)
    {
        $this->remarks = $remarks;
        return $this;
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

        return true;
    }

    /**
     * Makes the B2B request to Mpesa
     */
    public function transfer(){
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
            "Requester" => $this->requester,
            "ResultURL" => $this->resultUrl,
            "QueueTimeOutURL" => $this->timeoutUrl,
            "Remarks" => $this->remarks,
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
        $this->transfer();
        return $this->response();
    }
}
