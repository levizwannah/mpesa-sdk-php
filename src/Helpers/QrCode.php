<?php 
namespace LeviZwannah\MpesaSdk\Helpers;

use LeviZwannah\MpesaSdk\Mpesa;


class QrCode extends Mpesa {
 
    public string $merchantName;
    public string $reference = 'invoice';
    public $amount;
    protected string $type;
    public $receiver;
    public $size = 300;

    /**
     * Do not call this directly.
     * use $mpesa->qr()
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * Sets the merchant name
     * @param string $merchantName
     * 
     */
    public function merchantName(string $merchantName) {
        $this->merchantName = $merchantName;
        return $this;
    }

    /**
     * Sets the reference number
     * @param string $referenceNumber
     * 
     */
    public function reference(string $referenceNumber) {
        $this->reference = $referenceNumber;
        return $this;
    }

    /**
     * Sets the amount
     * @param int $amount
     * 
     */
    public function amount(int $amount) {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Sets the receiver number
     * @param string $receiverNumber
     * 
     */
    public function receiver(string $receiverNumber) {
        $this->receiver = $receiverNumber;
        return $this;
    }

    /**
     * Sets the size of the returned QR code
     * @param int $size
     * 
     */
    public function size(int $size) {
        $this->size = $size;
        return $this;
    }

    /**
     * Sets the phone to pay to.
     * Accepts 07xxxxxxxx, +2547xxxxxxxxx, or 2547xxxxxxxxx
     * @param string $phone
     * 
     */
    protected function phone(string $phone){
        $phone = "254" . substr(preg_replace("/\s+/", "", $phone), -9);
        $this->receiver = $phone;
        return $this;
    }

    /**
     * Sets the TrxCode to BG, meaning buy goods.
     */
    public function buygoods() {
        $this->type = "BG";
        return $this;
    }

    /**
     * Sets the TrxCode to PB, meaning paybill
     */
    public function paybill() {
        $this->type = "PB";
        return $this;
    }

    /**
     * Sets the TrxCode to WA, meaning withdraw at Agent.
     */
    public function withdraw() {
        $this->type = "WA";
        return $this;
    }

    /**
     * Sets the TrxCode to SM, meaning Send Money.
     * And the receiver type must be a mobile number
     * which is valid.
     */
    public function sendMoney() {
        $this->type = "SM";
        return $this;
    }

    /**
     * Sets the TrxCode to SB, meaning Send to Business.
     */
    public function sendToBusiness() {
        $this->type = "SB";
        return $this;
    }

    public function okay()
    {
        parent::okay();

        $this->assertExists('merchantName', 'Merchant Name');
        $this->assertExists('receiver', 'Receiving Party Number');
        $this->assertExists('size');
        $this->assertExists('type', 'Transaction Type');
        $this->assertExists('reference', 'Reference Number');
        $this->assertExists('amount', "Amount");
    }

    /**
     * True if Mpesa accepted the request, false otherwise.
     * @return bool
     */
    public function accepted(){
        return isset($this->response()->ResponseCode); 
    }

    /**
     * Makes the Mpesa Request to get
     * the QR Code
     */
    public function generate() {
        $this->okay();
        
        if($this->type === "SM") {
            $this->phone($this->receiver);
        }

        $data = [
            "MerchantName" => $this->merchantName,
            "RefNo" => $this->reference,
            "Amount" => $this->amount,
            "TrxCode" => $this->type,
            "CPI" => $this->receiver,
            "Size" => $this->amount
        ];

        $this->response = $this->request($data, "/mpesa/qrcode/v1/generate");
        return $this;
    }

    public function __invoke()
    {
        $this->generate();
        return $this->response();
    }
}


?>