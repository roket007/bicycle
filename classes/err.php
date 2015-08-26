<?php

class Classes_Err extends Fw_Controller
{
    public $name = 'err';
    public $actionName = 'error404';

    public function setAction() 
    {
        $param_0 = Fw_Request::get(0, null);

        if( is_null($param_0) ) {
            return true;
        }

        if( method_exists($this, $param_0 . 'Action') ) {
            $this->actionName = $param_0;
            return true;
        } 

        if( !is_null($param_0) ) {
            return false;
        }
    }

    public function error404Action()
    {
        $headers = array('HTTP/1.0 404 Page Not Found');
        $this->view->addHeaders($headers);
        $this->view->render();
    }

    public function error500Action()
    {
        $headers = array('HTTP/1.0 500 Internal Server Error');
        $this->view->addHeaders($headers);
        $this->view->render();
    }
}
