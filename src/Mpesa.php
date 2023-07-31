<?php

    namespace LeviZwannah\MpesaSdk;

    use BadMethodCallException;
    use Exception;
    use LeviZwannah\MpesaSdk\Helpers\BusinessToCustomer;
    use LeviZwannah\MpesaSdk\Helpers\Constant;
    use LeviZwannah\MpesaSdk\Helpers\RequestError;
    use LeviZwannah\MpesaSdk\Helpers\Reversal;
    use LeviZwannah\MpesaSdk\Helpers\Stk;
    use LeviZwannah\MpesaSdk\Helpers\Traits\FieldToPropertyTrait;
use LeviZwannah\MpesaSdk\Helpers\TransactionQuery;
use LeviZwannah\MpesaSdk\Helpers\UrlManager;

    /**
     * Main Mpesa Class
     * @package levizwannah/mpesa-sdk-php
     */
    class Mpesa{

        use FieldToPropertyTrait;
        /**
         * Base URL for Safaricom requests.
         * @var string
         */
        public string $baseUrl = "https://";

        /**
         * Consumer Key
         * 
         * @var string
         */
        public string $key = "";

        /**
         * Consumer Secret
         * 
         * @var string
         */
        public string $secret = "";

        
        /**
         * Mpesa Operation Environment
         * live or sandbox
         * @var string
         */
        public string $env = Constant::LIVE;

        
        /**
         * passkey for STK push.
         * 
         * @var string
         */
        public string $passkey = "";

        /**
         * Security Credentials from the mpesa portal (use the Daraja tools to generate your security credential)
         * 
         * @var string
         */
        public string $credential = "";

        /**
         * Initiator from the mpesa portal
         * @var string
         */
        public string $initiator = "";

        /**
         * Mpesa business short code
         * 
         * @var string
         */
        public string $code = "";

        /**
         * Mpesa till number
         * 
         * @var string
         */
        public string $till = "";

        /**
         * The JSON decoded response from a request.
         * 
         * @var object
         */
        public ?object $response = null;

        /**
         * The params are optional. You can set them later.
         * @param string $key consumer key
         * @param string $secret consumer secret
         * @param string $env environment live or sandbox
         */
        public function __construct(string $key = "", string $secret = "", string $env = Constant::LIVE)
        {
            $this->key = $key;
            $this->secret = $secret;
            $this->env = strtolower($env);

            $this->baseUrl .= ($this->env === Constant::SANDBOX) ? "sandbox.safaricom.co.ke" 
            : "api.safaricom.co.ke";

        }

         /**
         * The params are optional. You can set them later.
         * @param string $key consumer key
         * @param string $secret consumer secret
         * @param string $env environment live or sandbox
         */
        public static function new(string $key = "", string $secret = "", string $env = Constant::LIVE)
        {
           return new Mpesa($key, $secret, $env);
        }
        

        /**
         * Sets the configuration of the Mpesa object. You can pass any number of keys. 
         * You don't have to 
         * pass all they keys.
         * The config array contains key value pair.
         * The accepted keys are: `key` => Consumer Key, `secret` => Consumer Secret, `env` => live or
         * sandbox, `initiator` => the initiator name, 
         * `credential` => security credential.
         * 
         * @param array<string,string> $config
         * 
         */
        public function configure(array $config){
            foreach($config as $key => $val){
                $this->$key = $val;
            }
            return $this;
        }

        /**
         * Sets the consumer key
         * @param string $key
         * 
         */
        public function key(string $key){
            $this->key = $key;
            return $this;
        }

        /**
         * Sets the consumer secret
         * @param string $secret
         * 
         */
        public function secret(string $secret){
            $this->secret = $secret;
            return $this;
        }

        /**
         * Sets the operation environment for Mpesa: live or sandbox
         * @param string $env
         * 
         */
        public function env(string $env){
            $this->env = strtolower($env);
            return $this;
        }

        /**
         * Sets the initiator name from the Mpesa portal
         * @param string $initiator
         * 
         */
        public function initiator(string $initiator){
            $this->initiator = $initiator;
            return $this;
        }

        /**
         * STK Passkey
         * @param string $passkey
         * 
         */
        public function passkey(string $passkey){
            $this->passkey = $passkey;
            return $this;
        }


        /**
         * Sets the security credential from the mpesa portal
         * @param string $credential
         * 
         */
        public function credential(string $credential){
            $this->credential = $credential;
            return $this;
        }

        /**
         * Sets Mpesa Short code
         * @param string $code
         * 
         */
        public function code(string $code){
            $this->code = $code;
            return $this;
        }

        /**
         * Mpesa Till number
         * @param string $till
         * 
         */
        public function till(string $till){
            $this->till = $till;
            return $this;
        }

        /**
         * Checks if the minimum requirements for mpesa api are set.
         * @return bool
         */
        public function okay(){
            $this->assertExists("key", "Consumer Key");
            $this->assertExists("secret", "Consumer Secret");
            $this->assertExists("code", "Business short code");

            return true;
        }

        /**
         * Throws an exception if the property doesn't exist
         * @param string $property property to check for
         * @param string $meaning what the property means
         * @throws \Exception
         * @return void
         */
        public function assertExists(string $property, $meaning = ""){
            if(empty($meaning)) $meaning = $property;
            !empty($this->$property) or throw new Exception("$meaning is required but is empty");
        }

        /**
         * @param array $data data to send. Associative array.
         * @param string $route beginning with /mpesa
         * 
         * @return object the json decoded mpesa response.
         */
        public function request(array $data, string $route){
            $token = $this->token();
            
            $curl = curl_init($this->baseUrl . $route);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json",
            "Authorization: Bearer $token"]);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HEADER, false);
            $response = curl_exec($curl);

            return json_decode($response);
        }

        /**
         * Returns the access token
         * @return string
         */
        public function token(){
            $url = $this->baseUrl."/oauth/v1/generate?grant_type=client_credentials";
            $curl = curl_init($url);
            $credentials = base64_encode("$this->key:$this->secret");
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
            $response = curl_exec($curl);
            return json_decode($response)->access_token;      
        }

        /**
         * Returns a partially configured Reversal Object
         * @return Reversal
         */
        public function reversal(){
            return new Reversal([
                "key" => $this->key,
                "secret" => $this->secret,
                "code" => $this->code,
                "baseUrl" => $this->baseUrl,
                "credential" => $this->credential,
                "initiator" => $this->initiator
            ]);
        }

        /**
         * Returns a partially configured B2C Object.
         * @return BusinessToCustomer
         */
        public function b2c(){
            return new BusinessToCustomer([
                "key" => $this->key,
                "secret" => $this->secret,
                "code" => $this->code,
                "baseUrl" => $this->baseUrl,
                "credential" => $this->credential,
                "initiator" => $this->initiator
            ]);
        }

        /**
         * Returns a partially configured UrlManager to register URLs.
         * @return UrlManager
         */
        public function urls(){
            return new UrlManager([
                "code" => $this->code,
                "baseUrl" => $this->baseUrl,
                "key" => $this->key,
                "secret" => $this->secret,
                "env" => Constant::LIVE
            ]);
        }

        public function c2b(){

        }

        public function b2b(){

        }

        public function balance(){

        }

        /**
         * Gets the configured TransactionQuery Object
         * @return TransactionQuery
         */
        public function query(){
            return new TransactionQuery([
                "key" => $this->key,
                "secret" => $this->secret,
                "code" => $this->code,
                "baseUrl" => $this->baseUrl,
                "credential" => $this->credential,
                "initiator" => $this->initiator
            ]);
        }

        public function stk(){
            return new Stk([
                "key" => $this->key,
                "secret" => $this->secret,
                "passkey" => $this->passkey,
                "code" => $this->code,
                "till" => $this->till,
                "baseUrl" => $this->baseUrl
            ]);
        }

        /**
         * Sends a confirm transaction response to mpesa in the confirmation or validation handler.
         * @return void
         */
        public static function confirm(){
            self::header();
            echo json_encode([
                Constant::RESULT_DESC => "Accepted",
                Constant::RESULT_CODE => 0
            ]);
        }

        /**
         * sends a deny transaction response to mpesa in the confirmation or validation handler.
         * @return void
         */
        public static function deny(){
            self::header();
            echo json_encode([
                Constant::RESULT_DESC => "Not Accepted",
                Constant::RESULT_CODE => 1
            ]);
        }

        /**
         * If the request returned an error response, this method returns the error object.
         * @return RequestError|false
         */
        public function error(){
            return isset($this->response->errorCode) ?
                    new RequestError($this->response()->errorCode, 
                    $this->response()->errorMessage) 
                    : false;
        }

        /**
         * True if Mpesa accepted to make the STK push, false otherwise.
         * @return bool
         */
        public function accepted(){
            return isset($this->response()->ResponseCode) 
            && $this->response()->ResponseCode == 0;
        }

        /**
         * Gets the current response object.
         */
        public function response($response = null){
            if($response){
                $this->response = $response;
                return $this;
            }

            return $this->response;
        }

        /**
         * Gets the data from Mpesa call in the callback handler and returns it as an object.
         * @param bool $asArray returns data as associative array
         * 
         * @return object|array<string, mixed>
         */
        public static function data($asArray = false){
            return json_decode(file_get_contents("php://input"), $asArray);
        }

         /**
         * Sets the response header `application/json`
         */
        public static function header(){
            header("Content-Type: application/json");
        }

        /**
         * Return an array of the valid Mpesa IP addresses
         * @return array
         */
        public static function ips(){
            return [
                "196.201.214.200",
                "196.201.214.206",
                "196.201.213.114",
                "196.201.214.207",
                "196.201.214.208",
                "196.201.213.44",
                "196.201.212.127",
                "196.201.212.128",
                "196.201.212.129",
                "196.201.212.132",
                "196.201.212.136",
                "196.201.212.138",
                "196.201.212.69",
                "196.201.212.74"
            ];
        }

        /**
         * Gets the client IP
         * @return array
         */
        public static function clientIps(){

            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
                return  
                array_values( 
                    array_filter( 
                        explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']) 
                    ) 
                );
            }
            
            if (array_key_exists('REMOTE_ADDR', $_SERVER)) { 
                    return [ $_SERVER["REMOTE_ADDR"] ]; 
            }
            
            if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
                    return [ $_SERVER["HTTP_CLIENT_IP"] ]; 
            } 
        
            return '';
        }

        /**
         * verifies that the callback is from Mpesa Server
         * @return bool
         */
        public static function verifyOrigin(){
            if(empty(static::clientIps())) return false;

            return ! empty( array_intersect(static::clientIps(), static::ips()) );
        }
    }

?>
