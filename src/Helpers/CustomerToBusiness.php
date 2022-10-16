<?php
    namespace LeviZwannah\MpesaSdk\Helpers;


    class CustomerToBusiness extends MpesaWithInitiator{
        /**
         * Since C2B cannot be initiated from the server,
         * This method simulates a C2B customer request. Note that it is the confirmation and validation urls that deal with the logic for C2B as
         * it is not like STK Push.
         */
        public function simulate(){
            
        }
    }

?>