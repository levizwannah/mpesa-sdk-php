<?php
namespace LeviZwannah\MpesaSdk\Helpers;

class AccountBalance extends MpesaWithInitiator {

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
    public string $remarks = "balance check";

    /**
     * Command ID
     */
    protected string $type = Constant::ACCOUNT_BALANCE;

    /**
     * You should not call this directly. Use $mpesa->b2c()
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configure($config);
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

    public function okay()
    {
        parent::okay();
        $this->assertExists("type", "Type/CommandID");
        $this->assertExists("resultUrl", "Result URL");
        $this->assertExists("timeoutUrl", "Timeout URL");
        $this->assertExists("remarks", "Remarks");

        return true;
    }

    /**
     * Makes the Account Balance Query to Mpesa.
     */
    public function check(){
        $this->okay();

        $data = [
            "Initiator" => $this->initiator,
            "SecurityCredential" => $this->credential,
            "CommandID" => $this->type,
            "IdentifierType" => 4,
            "PartyA" => $this->code,
            "ResultURL" => $this->resultUrl,
            "QueueTimeOutURL" => $this->timeoutUrl,
            "Remarks" => $this->remarks,
        ];

        $this->response = $this->request($data, "/mpesa/accountbalance/v1/query");

        return $this;
    }

    /**
     * Makes the Account request and returns the response.
     * @return object
     */
    public function __invoke()
    {
        $this->check();
        return $this->response();
    }
}