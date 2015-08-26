<?php

class Classes_Done extends Fw_Controller
{
    public $name = 'done';
    public $actionName = 'index';
    
    public function preActionEvent()
    {
        $this->model = Fw_Model::getInstance()->getModel('projects');
    }

    public function setAction() 
    {
        $param_0 = Fw_Request::get(0, null);

        if( is_null($param_0) ) {
            return true;
        }

        if( method_exists($this, $param_0 . 'Action') ) {
            $this->actionName = $param_0;

            if($param_0 == 'order') {
                $param_1 = Fw_Request::get(1, null);
                $param_2 = Fw_Request::get(2, 0);

                if($param_1 == 'vote' || $param_1 == 'new') {
                    if( is_numeric($param_2) ) {
                        Fw_Request::setGet('offset', $param_2);
                    }

                    Fw_Request::setGet('order_by', $param_1);
                    return true;
                } else {
                    return false;
                }
            }

            return true;
        }

        if( !is_null($param_0) && !method_exists($this, $param_0 . 'Action') ) {
            $per_project = explode("-", $param_0);

            if( is_numeric($per_project[0]) ) {
                Fw_Request::setGet('id', $per_project[0]);
                Fw_Request::setGet('alias', substr( $param_0, strlen($per_project[0] . '-') ) );
                return true;
            } else return false;
        }
    }

    public function orderAction()
    {
        $order_by = Fw_Request::get('order_by', 'new');
        $offset = Fw_Request::get('offset', 0);
        $limit = Fw_Request::get('limit', 10);
        $this->view->data = $this->model->getOrderProjects($order_by, $offset, $limit, true);
        $this->view->render(false);
    }

    public function indexAction()
    {
        $id = Fw_Request::get('id', null);
        $alias = Fw_Request::get('alias', null);
        
        if( !is_null($id) ) {
            $this->view->setLayout('done', 'project');
            $this->view->data = $this->model->getProject($id, true);

            if( !isset($this->view->data['pl_alias']) || $this->view->data['pl_alias'] !== $alias ) {
                throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Страница не найдена', 404);
            }
            
            $this->view->ru_lang_url = '/ru/done/' . $this->view->data['p_id'] . '-' . $this->view->data['ru_alias'] . '/';
            $this->view->ua_lang_url = '/ua/done/' . $this->view->data['p_id'] . '-' . $this->view->data['ua_alias'] . '/';
            $this->view->donors_data = $this->model->getDonorsByProject($id, 0);
            $this->view->setHead( 'title', array(), $this->view->data['pl_title'], true );
            $this->view->setHead( 'meta', array('name' => 'description', 'content' => $this->view->data['pl_description']), '', false );
            $this->view->setHead( 'meta', array('name' => 'keywords', 'content' => $this->view->data['pl_keywords']), '', false );
            $breadcrumb = unserialize( Fw_Model::getInstance()->getModel('blocks')->getBreadCrumbs($this->name, $this->actionName) );
            array_push( $breadcrumb, array('name' => $this->view->data['pl_title']) );
            $this->view->setBlocks( array( 'breadcrumbs' => array('data' => serialize($breadcrumb) ) ) );
        } else {
            $this->view->data = $this->model->getOrderProjects('new', 0, 10, true);
            $this->view->setBlocks( array('breadcrumbs') );
        }
        
        $this->view->render();
    }
}
