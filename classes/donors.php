<?php

class Classes_Donors extends Fw_Controller
{
    public $name = 'donors';
    public $actionName = 'index';

    public function setAction() 
    {
        $param_0 = Fw_Request::get(0, NULL);

        if( is_null($param_0) ) {
            return true;
        }

        if( method_exists($this, $param_0 . 'Action') ) {
            $this->actionName = $param_0;

            if($param_0 == 'order') {
                $param_1 = Fw_Request::get(1, null);
                $param_2 = Fw_Request::get(2, 0);

                if($param_1 == 'project' || $param_1 == 'new' || $param_1 == 'count') {
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
            if( is_numeric($param_0) && $param_0 > 0 ) {
                Fw_Request::setGet('id', $param_0);
                return true;
            } else return false;
        }
    }

    public function orderAction()
    {
        $order_by = Fw_Request::get('order_by', 'new');
        $offset = Fw_Request::get('offset', 0);
        $limit = Fw_Request::get('limit', 10);
        $this->view->data = $this->model->getOrderDonors($order_by, $offset, $limit);
        $this->view->render(false);
    }

    public function indexAction()
    {
        $id = Fw_Request::get('id', null);

        if( !is_null($id) ) {
            $this->view->setLayout('donors', 'donor');
            $this->view->data = $this->model->getDonor($id);
            
            if( !isset($this->view->data['u_id']) || !is_numeric($this->view->data['u_id']) ) {
                throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Страница не найдена', 404);
            }
            
            $name_bread = $this->view->data['u_surname'] . ' ' . $this->view->data['u_name'];
            
            if( Fw_Auth::isValid() && Fw_Auth::get('id') == $id ) {
                $this->view->error = array();
                $data = array();
                $name = Fw_Request::post('u_name', null);
                $antispam = Fw_Request::post('antispam', null);
                $surname = Fw_Request::post('u_surname', null);
                $address = Fw_Request::post('u_address', null);
                $img = Fw_Request::getFile('u_img', null);
                $information = Fw_Request::postNoHtml('u_information', 0, 1500);                

                if( !is_null($antispam) ) {
                    if( !preg_match("/^[\p{L}]{2,100}$/ui", $name) ) {
                        $this->view->error['u_name'] = C::getLanguageString('registration_only_chars_name');
                    }
                    
                    $data['u_name'] = $name;
                    
                    if( !preg_match("/^[\p{L}]{2,100}$/ui", $surname) ) {
                        $this->view->error['u_surname'] = C::getLanguageString('registration_only_chars_surname');
                    }
                    
                    $data['u_surname'] = $surname;
                    
                    if( !empty($address) ) {
                        if( !preg_match("/^[^\s][\.\p{L}\d\,\-\s]{0,100}$/ui", $address) ) {
                            $this->view->error['u_address'] = C::getLanguageString('registration_only_chars_name');
                        }
                        
                        $data['u_address'] = $address;
                    } else {
                        $data['u_address'] = '';
                    }
                    
                    $data['u_information'] = ( !is_null($information) ? $information : '' );
                    
                    if( !is_null($img) && isset($img['name']) && !empty($img['name']) ) {
                        $type = array_pop( explode(".", $img['name']) );
                        
                        if( in_array( strtolower($type), array('png', 'jpeg', 'jpg', 'gif') ) ) {
                            if( $img['size'] > (1024 * 1024 * 2) ) {
                                $this->view->error['u_img'] = C::getLanguageString('wrong_file_size');
                            } else {
                                $new_file_name = $data['u_img'] = sha1( $img['name'] . time() ) . '.' .$type;
                                $to_path = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images') . C::ds . $new_file_name;
                                
                                if( move_uploaded_file($img['tmp_name'], $to_path) === false) {
                                    throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка загрузки аватара', 500);
                                }
                                
                                if( !empty($this->view->data['u_img']) && $this->view->data['u_img'] !== 'anonim_donor.jpg' ) {
                                    $config = C::getConfig('img');
                                    $del_file = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images') . C::ds . $this->view->data['u_img'];
                                    
                                    if( is_file($del_file) ) {
                                        unlink($del_file);
                                    }
                                    
                                    foreach($config['size'] as $sizes) {
                                        $del_file = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images/cropr_' . $sizes) . C::ds . $this->view->data['u_img'];
                                            
                                        if( is_file($del_file) ) {
                                            unlink($del_file);
                                        }
                                    }
                                }
                            }
                        } else {
                            $this->view->error['u_img'] = C::getLanguageString('wrong_file_format');
                        }
                    }
                    
                    if( empty($this->view->error) ) {
                        if( !Fw_Model::getInstance()->getModel('user')->updateUserData($data) ) {
                            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка обновления данных пользователя', 500);
                        }

                        Fw_Request::redirect( C::uri( array('donors', $id) ) );
                    }
                }
                
                $this->view->antispam = base64_encode( time() );
            }
            
            if( !empty($this->view->data['pl_description']) ) {
                $description = strip_tags($this->view->data['pl_description']);
                $description = trim($description);
                
                if( !empty($description) ) {
                    if( mb_strlen($description, 'UTF-8') > 240 ) {
                        $description = substr($description, 0, 240) . '...';
                    }
                    
                    $this->view->setHead( 'meta', array('name' => 'description', 'content' => $description), '', false );
                }
            }
            
            $this->view->ru_lang_url = '/ru/donors/' . $this->view->data['u_id'] . DS;
            $this->view->ua_lang_url = '/ua/donors/' . $this->view->data['u_id'] . DS;
            $this->view->projects_data = $this->model->getProjectsByDonor($id);
            $this->view->setHead( 'title', array(), $name_bread . ' ' . C::getLanguageString('donor_h1'), true );
            $this->view->setHead( 'meta', array('name' => 'keywords', 'content' => strtolower( C::getLanguageString('donor') ) . ', ' . $name_bread), '', false );
            $breadcrumb = unserialize( Fw_Model::getInstance()->getModel('blocks')->getBreadCrumbs($this->name, $this->actionName) );
            
            if($breadcrumb) {
                array_push( $breadcrumb, array('name' => $name_bread) );
                $this->view->setBlocks( array( 'breadcrumbs' => array('data' => serialize($breadcrumb) ) ) );
            }
        } else {
            $this->view->data = $this->model->getOrderDonors('new', 0, 10);
            $this->view->setBlocks( array('breadcrumbs') );
        }
        
        $this->view->render();
    }
}
