<?php
    namespace LeviZwannah\MpesaSdk\Helpers;

use LeviZwannah\MpesaSdk\Mpesa;

    class MpesaWithInitiator extends Mpesa{
        public function okay()
        {
            parent::okay();
            $this->assertExists("initiator", "Initiator name");
            $this->assertExists("credential", "Security credential");

            return true;
        }
    }

?>