<?php
namespace LeviZwannah\MpesaSdk\Helpers\Traits;

use BadMethodCallException;

trait FieldToPropertyTrait{
    public function __call($name, $arguments)
    {
        if(!property_exists($this, $name)) throw new BadMethodCallException();

        if(empty($arguments)) return $this->$name;

        $this->$name = $arguments[0];

        return $this;
    }
}

?>