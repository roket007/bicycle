<?php

class Classes_Index extends Fw_Controller
{
    public $name = 'index';
    public $actionName = 'index';

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

    public function indexAction()
    {
        $this->view->text = Fw_Model::getInstance()->getModel('default')->loadStaticPage($this->name, $this->actionName);
        $this->view->data = $this->model->getData();
        $this->view->setBlocks( array('moneycount', 'slider') );
        $this->view->render();
    }
}
