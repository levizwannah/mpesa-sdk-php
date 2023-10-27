<?php 
namespace LeviZwannah\MpesaSdk\Helpers;

class RemitTax extends BusinessToBusiness {
    
    protected string $resourcePath = "/mpesa/b2b/v1/remittax";

    /**
     * Makes the tax payment to KRA;
     */
    public function pay()
    {
        $this->receiver(Constant::KRA_PAYBILL)
             ->type(Constant::KRA_TAX);

        return parent::pay();
    }

}


?>