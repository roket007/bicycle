<?php

class Classes_Help extends Fw_Controller
{
    public $name = 'help';
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
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
    
    public function rulesAction()
    {
        $this->view->text = Fw_Model::getInstance()->getModel('default')->loadStaticPage($this->name, $this->actionName);
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
}
