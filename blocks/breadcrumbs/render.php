<?php

class breadcrumbsBlock extends Fw_Blocks
{        
    public function render()
    {
        if( !isset($this->data) && empty($this->data) ) {
            $this->controller = Fw_Request::get('controller', 'index');
            $this->action = Fw_Request::get('action', 'index');
            $model = Fw_Model::getInstance();
            $this->data = $model->getModel('blocks')->getBreadCrumbs($this->controller, $this->action);
        }
        
        parent::render(__CLASS__);
    }
}
