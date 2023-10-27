<?php 
namespace LeviZwannah\MpesaSdk\Helpers;

use LeviZwannah\MpesaSdk\Mpesa;
/*
{    
   "MerchantName":"TEST SUPERMARKET",
   "RefNo":"Invoice Test",
   "Amount":1,
   "TrxCode":"BG",
   "CPI":"373132",
   "Size":"300"
}
*/
class QrCode extends Mpesa {
 
    public string $merchantName;
    public string $reference;
    public $amount;
    protected string $type;
    public $receiver;
    public $size = 300;

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
    }

    /**
     * Makes the Mpesa Request to get
     * the QR Code
     */
    public function generate() {
        $this->okay();
    }
}


?>