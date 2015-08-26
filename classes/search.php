<?php

class Classes_Search extends Fw_Controller
{
    public $name = 'search';
    public $actionName = 'index';

    public function setAction() 
    {
        $query = Fw_Request::post('query', null);
        
        if( !is_null($query) ) {
            Fw_Request::redirect( C::uri( array('search', urlencode($query) ) ) );
        }
        
        $param_0 = Fw_Request::get(0, null);
        $param_1 = Fw_Request::get(1, null);

        if( is_null($param_0) ) {
            return true;
        }
        
        if( method_exists($this, $param_0 . 'Action') ) {
            $this->actionName = $param_0;
            return true;
        }
        
        if( !is_null($param_0) && !method_exists($this, $param_0 . 'Action') ) {
            
            if( !is_null($param_1) && $param_1 !== 'error') {
                throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Страница не найдена', 404);
            }
            
            $search_query = urldecode($param_0);

            if( preg_match("/^[\p{L}\d\-_\.\,\?\!\s]+$/Sui", $search_query) ) {
                Fw_Request::setGet('search_query', $search_query);
                Fw_Request::setGet('error', false);
                return true;
            } else {
                Fw_Request::setGet('search_query', $search_query);
                Fw_Request::setGet('error', true);
                
                if($param_1 !== 'error') {
                    Fw_Request::redirect( C::uri( array('search', urlencode($search_query), 'error' ) ) );
                    die;
                }
                
                return true;
            }
        }
    }

    public function indexAction()
    {
        $this->view->error = Fw_Request::get('error', null);
        $this->view->query = Fw_Request::get('search_query', null);
        
        if(!$this->view->error) {
            $this->view->data = $this->model->searchByQuery($this->view->query);  
        }
        
        $this->view->render();
    }
}
