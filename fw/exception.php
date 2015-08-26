<?php

class Fw_Exception extends Exception
{
    public function __construct($message, $code)
    {
        
        Log::add($message . 'URI: ' . $_SERVER['REQUEST_URI'], $code);

        if(!DEV) {
            switch($code) {
                case 500:
                    Fw_Request::setGet('hard_controller', 'err');
                    Fw_Request::setGet('hard_action', 'error' . $code);
                    $error_app = new Application();
                    $error_app->run();
                    die();
                    break;
                case 404:
                default :
                    Fw_Request::setGet('hard_controller', 'err');
                    Fw_Request::setGet('hard_action', 'error' . $code);
                    $error_app = new Application();
                    $error_app->run();
                    die();
                    break;
            }
        }

        $code = 0;
        parent::__construct($message);
    }
}
