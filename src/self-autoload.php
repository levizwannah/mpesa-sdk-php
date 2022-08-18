<?php
  // loads the SDK functions
  spl_autoload_register(function($name){
      $className = str_replace("LeviZwannah\\MpesaSdk\\", "", $name);
      include_once(__DIR__ . "/$className.php");
    }
  );

?>
