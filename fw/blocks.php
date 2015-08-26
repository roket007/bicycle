<?php

class Fw_Blocks
{   
    public function __set($name, $value) 
    {
        $this->$name = $value;

        unset($name);
        unset($value);
    }

    public function __get($name) 
    {
        if ( isset($this->$name) ) {
            return $this->$name;
        }

        unset($name);

        return null;
    }

    public function render($name)
    {
        $name = str_replace('Block', '', $name);
        include APPLICATION_PATH . C::ds . 'blocks' . C::ds . $name . C::ds . 'view.phtml';
    }
}