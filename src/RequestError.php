<?php
    namespace LeviZwannah\MpesaSdk;

    /**
     * Represents the Request Error Response from mpesa.
     */
    class RequestError{
        public string $code;
        public string $description;

        public function __construct($code, $description)
        {
            $this->code = $code;
            $this->description = $description;
        }
    }

?>