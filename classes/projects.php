<?php

class Classes_Projects extends Fw_Controller
{
    public $name = 'projects';
    public $actionName = 'index';
    
    public function preActionEvent()
    {
        $this->view->setJs('jquery.lightbox-0.5.js');
        $this->view->setCss('jquery.lightbox-0.5.css');
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

                if($param_1 == 'vote' || $param_1 == 'need' || $param_1 == 'new') {
                    if( is_numeric($param_2) ) {
                        Fw_Request::setGet('offset', $param_2);
                    }

                    Fw_Request::setGet('order_by', $param_1);
                    return true;
                } else {
                    return false;
                }
            } else if($param_0 == 'donors') {
                $param_1 = Fw_Request::get(1, null);
                $param_2 = Fw_Request::get(2, 0);
                
                if( is_numeric($param_1) ) {
                     Fw_Request::setGet('id', $param_1);
                } else {
                    return false;
                }
                
                if( is_numeric($param_2) ) {
                    Fw_Request::setGet('offset', $param_2);
                } else {
                    return false;
                }
                
                return true;
            } else if($param_0 == 'vote') {
                $param_1 = Fw_Request::get(1, null);
                
                if( is_numeric($param_1) ) {
                     Fw_Request::setGet('id', $param_1);
                     return true;
                } else {
                    return false;
                }
            } else if($param_0 == 'createpay') {
                $param_1 = Fw_Request::get(1, null);
                $param_2 = Fw_Request::get(2, null);
                
                if( is_numeric($param_1) && is_numeric($param_2) ) {
                     Fw_Request::setGet('id', $param_1);
                     Fw_Request::setGet('money', $param_2);
                     return true;
                } else {
                    return false;
                }
            } else if($param_0 == 'printblank') {
                $param_1 = Fw_Request::get(1, null);
                $param_2 = Fw_Request::get(2, null);
                $param_3 = Fw_Request::get(3, null);
                
                if( is_numeric($param_1) && is_numeric($param_2) && preg_match( "/^[0-9]+([\.][\d]{1,2})?$/ui", base64_decode($param_3) ) ) {
                     Fw_Request::setGet('id', $param_1);
                     Fw_Request::setGet('user', $param_2);
                     Fw_Request::setGet( 'sum', base64_decode($param_3) );
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
            } else {
                return false;
            }
        }
    }
    
    public function printblankAction()
    {
        $this->view->id = Fw_Request::get('id', NULL);
        $this->view->user_id = Fw_Request::get('user', NULL);
        $this->view->sum = Fw_Request::get('sum', NULL);
        
        $this->view->render(false);
    }
    
    public function successspayAction()
    {
        $this->view->text = Fw_Model::getInstance()->getModel('default')->loadStaticPage($this->name, $this->actionName);
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
    
    public function createpayAction()
    {
        $id_project = Fw_Request::get('id', null);
        $money_sum = Fw_Request::get('money', null);
        
        if( is_numeric($id_project) && preg_match("/^[0-9]+([\.][\d]{1,2})?$/ui", $money_sum) && $money_sum >= 0.02 ) {
            $user_url = C::site_host . C::uri( array('projects', 'successspay') );
            $server_url = C::site_host . '/test.php';
            $merchant_id = 'i6677775472';
            $merchant_signature = 'vn0J7VHJxltX8nOxus1RZvYCgOhIQV6WDEOsv2';
            $order_id = 'ORDER_' . $id_project . '_' . ( Fw_Auth::isValid() ? Fw_Auth::get('id') : 0 ) . '_' . time();
            $xml = "<request>"
                . "<version>1.2</version>"
                . "<merchant_id>{$merchant_id}</merchant_id>"
                . "<result_url>{$user_url}</result_url>"
                . "<server_url>{$server_url}</server_url>"
                . "<order_id>{$order_id}</order_id>"
                . "<amount>{$money_sum}</amount>"
                . "<currency>UAH</currency>"
                . "<description>Project_loveChernivtsi_num_" . $id_project . "_by_" . ( Fw_Auth::isValid() ? Fw_Auth::get('id') : 0 ) . "_user</description>"
                . "<default_phone></default_phone>"
                . "<pay_way>card</pay_way>"
                . "<goods_id>{$id_project}</goods_id>"
            . "</request>";
            $this->view->sum = $money_sum;
            $this->view->operation_xml = base64_encode($xml);
            $this->view->id = $id_project;
            $this->view->user_id = ( Fw_Auth::isValid() ? Fw_Auth::get('id') : 0 );
            $this->view->signature = base64_encode( sha1( $merchant_signature . $xml . $merchant_signature, 1 ) );
        } else {
            die;
        }
        
        $this->view->render(false);
    }

    public function orderAction()
    {
        $order_by = Fw_Request::get('order_by', 'new');
        $offset = Fw_Request::get('offset', 0);
        $limit = Fw_Request::get('limit', 10);
        $this->view->data = $this->model->getOrderProjects($order_by, $offset, $limit);
        $this->view->render(false);
    }
    
    public function voteAction()
    {
        $id = Fw_Request::get('id', 10);
        
        if( Fw_Auth::isValid() ) {
            $user_id = Fw_Auth::get('id');
            
            if( $this->model->voteByProject($id, $user_id) ) {
                $this->model->updateVoteByProject($id);
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
        
        die;
    }

    public function indexAction()
    {
        $id = Fw_Request::get('id', null);
        $alias = Fw_Request::get('alias', null);

        if( !is_null($id) ) {
            $this->view->setLayout('projects', 'project');
            $this->view->data = $this->model->getProject($id);
            
            if( !isset($this->view->data['pl_alias']) || $this->view->data['pl_alias'] !== $alias ) {
                throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Страница не найдена', 404);
            }
            
            if($this->view->data['p_current'] >= $this->view->data['p_need']) {
                Fw_Request::redirect( C::uri( array('done', $this->view->data['p_id'] . '-' . $this->view->data['pl_alias']) ), 301 );
            }
            
            $this->view->ru_lang_url = '/ru/projects/' . $this->view->data['p_id'] . '-' . $this->view->data['ru_alias'] . '/';
            $this->view->ua_lang_url = '/projects/' . $this->view->data['p_id'] . '-' . $this->view->data['ua_alias'] . '/';
            $this->view->donors_data = $this->model->getDonorsByProject($id, 0);
            $this->view->setHead( 'title', array(), $this->view->data['pl_title'], true );
            $this->view->setHead( 'meta', array('name' => 'description', 'content' => $this->view->data['pl_description']), '', false );
            $this->view->setHead( 'meta', array('name' => 'keywords', 'content' => $this->view->data['pl_keywords']), '', false );
            $breadcrumb = unserialize( Fw_Model::getInstance()->getModel('blocks')->getBreadCrumbs($this->name, $this->actionName) );
            array_push( $breadcrumb, array('name' => $this->view->data['pl_title']) );
            $this->view->setBlocks( array( 'breadcrumbs' => array('data' => serialize($breadcrumb) ) ) );
        } else {
            $this->view->data = $this->model->getOrderProjects('new', 0, 10);
            $this->view->setBlocks( array('breadcrumbs') );
        }
        
        $this->view->render();
    }
    
    public function donorsAction()
    {
        $id = Fw_Request::get('id', NULL);
        $offset = Fw_Request::get('offset', 0);
        $this->view->donors_data = $this->model->getDonorsByProject($id, $offset);
        $this->view->render(false);
    }
}
