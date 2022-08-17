<?php
    namespace LeviZwannah\MpesaSdk;

    /**
     * Represents the Request Error Response from mpesa.
     */
    class RequestError{
        public string $code;
        public string $message;

        public function __construct($code, $message)
        {
            $this->code = $code;
            $this->message = $message;
        }
    }

?>