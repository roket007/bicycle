<?php

class Router
{
    public $_source_uri;

    public function __construct()
    {
        $this->_source_uri = $_SERVER['REQUEST_URI'];
    }

    public function parse()
    {
        $true_counter = 0;
        $parsed_source = array();
        $exploded = explode( '/', trim($this->_source_uri) );

        foreach($exploded as $key => $value) {
            if( !is_null($value) && strlen($value) >= 1 ) {
                $parsed_source[$true_counter] = $value;
                $true_counter++;
            }
        }

        Fw_Request::setGet('language', 'ua');
        $first = array_shift($parsed_source);

        if( strlen($first) == 2 ) {
            if($first == 'ru') {
                Fw_Request::setGet('language', $first);
            } else {
                if( empty($parsed_source) ) {
                    Fw_Request::redirect(C::ds, 301);
                } else {
                    Fw_Request::redirect(C::ds . implode("/", $parsed_source) . C::ds, 301);
                }
            }

            Fw_Request::setGet( 'controller', array_shift($parsed_source) );
        } else {
            //Fw_Request::redirect('/ua/');
            Fw_Request::setGet( 'controller', $first );
        }
        
        $hard_controller = Fw_Request::get('hard_controller', null);
        
        if( !is_null($hard_controller) ) {
            Fw_Request::setGet( 'controller', $hard_controller );
        }

        if( empty($parsed_source) ) {
            return;
        }

        $counter = 0;

        foreach($parsed_source as $key => $value) {
            Fw_Request::setGet($counter, $value);
            $counter++;
            unset($parsed_source[$key]);
        }

        unset($counter);
        unset($this->_source_uri);
        unset($parsed_source);
    }
}
