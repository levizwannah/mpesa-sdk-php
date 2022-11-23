<?php
namespace LeviZwannah\MpesaSdk\Helpers;

use LeviZwannah\MpesaSdk\Mpesa;

class UrlManager extends Mpesa{
    /**
     * The URL registration type
     * @var string
     */
    public $type = Constant::URL_COMPLETE;

    /**
     * validation URL to register
     * @var string
     */
    public $validation;

    /**
     * Confirmation URL to register
     * @var string
     */
    public $confirmation;

    /**
     * You should not call this directly. Use $mpesa->url()
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * Sets the confirmation URL
     * @param string $confirmationUrl
     * 
     * @return $this
     */
    public function confirmation(string $confirmationUrl){
        $this->confirmation = $confirmationUrl;
        return $this;
    }

    
    /**
     * Sets the validation url;
     * @param string $validationUrl
     * 
     * @return $this
     */
    public function validation(string $validationUrl){
        $this->validation = $validationUrl;
        return $this;
    }

    /**
     * Sets the type of the response that should be sent to the registered URLs.
     * Must be one of Constant::URL_COMPLETE or Constant::URL_CANCELLED
     * @param string $type
     * 
     * @return $this
     */
    public function type(string $type = Constant::URL_COMPLETE){
        $this->type = $type;
        return $this;
    }

    /**
     * Checks if all the required properties are set.
     * @throws Exception
     * @return bool
     */
    public function okay()
    {
        parent::okay();
        $this->assertExists("confirmation", "Confirmation URL");
        $this->assertExists("validation", "Validation URL");
        return true;
    }

    public function register(){
        $this->okay();
        $data = [
            "ShortCode" => $this->code,
            "ResponseType" => $this->type,
            "ConfirmationURL" => $this->confirmation,
            "ValidationURL" => $this->validation
        ];

        $this->response = $this->request($data, "/mpesa/c2b/v2/registerurl");
        return $this;   
    }
}

?>