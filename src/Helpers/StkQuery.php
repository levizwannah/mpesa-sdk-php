<?php 
namespace LeviZwannah\MpesaSdk\Helpers;

use LeviZwannah\MpesaSdk\Mpesa;

class StkQuery extends Mpesa {

    /**
     * The checkout request ID
     * @var string
     */
    public string $checkoutId;

    public string $type = Constant::PAY_BILL_ONLINE;
    
    /**
     * !!!Do not call this directly. It needs config from the parent STK class.
     * Use $mpesa->stk()->query();
     * @param array<string,string> $config initial configurations for the stk push.
     * This is gotten from the parent Mpesa.
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * Sets the checkout request ID
     * @param string $checkoutRequestId
     * 
     */
    public function checkoutId(string $checkoutRequestId) {
        $this->checkoutId = $checkoutRequestId;
        return $this;
    }

    /**
     * Querying for a Till account
     */
    public function buygoods(){
        $this->type = Constant::BUY_GOODS_ONLINE;
        return $this;
    }

    /**
     * Querying for a Paybill account
     */
    public function paybill(){
        $this->type = Constant::PAY_BILL_ONLINE;
        return $this;
    }

    public function okay()
    {
        parent::okay();
        $this->assertExists('checkoutId', 'checkout request ID');
        $this->assertExists('passkey');

        if($this->type === Constant::BUY_GOODS_ONLINE) {
            $this->assertExists('till', 'Till Number for Buy goods');
        }
    }

    /**
     * Make the STK Transaction Query request
     */
    public function make() {
        $this->okay();

        $timestamp = date("YmdHis");
        $password = base64_encode("$this->code$this->passkey$timestamp");
        
        $data = [
            "BusinessShortCode" => $this->type === Constant::PAY_BILL_ONLINE ? $this->code : $this->till,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "CheckoutRequestID" => $this->checkoutId,
        ];

        $this->response = $this->request($data, "/mpesa/stkpushquery/v2/query");

        return $this;
    }
}

?>