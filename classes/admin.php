<?php

class Classes_Admin extends Fw_Controller
{
    public $name = 'admin';
    public $actionName = 'index';
    private $dirs = array();
    
    public function init()
    {
        if( !Fw_Auth::isValidAdmin() ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Страница не найдена', 404);
        }
    }
    
    public function preActionEvent()
    {
        $this->view->setTemplate('admin');
        $this->view->langs = array('ua', 'ru');
    }
    
    public function projectsAction()
    {
        $this->view->users = $this->model->getProjectUsers();
        $this->view->data = $this->model->getProjects();
        $this->view->render();
    }
    
    public function projectsaddcustompayAction()
    {
        $data['pp_p_id'] = Fw_Request::post('pp_p_id', NULL);
        $data['pp_u_id'] = Fw_Request::post('pp_u_id', NULL);
        $data['pp_data'] = date( "Y-m-d H:i:s", time() );
        $data['pp_sum'] = ceil( Fw_Request::post('pp_sum', NULL) );
        $this->view->result = $this->model->addPay($data);
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache');
        $this->view->render(false);
    }
    
    public function projectsdropcustompayAction()
    {
        $id = Fw_Request::post('id', null);
        $this->view->result = false;
        
        if( is_numeric($id) ) {
            $this->view->result = $this->model->dropPay($id);
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache');
        $this->view->render(false);
    }
    
    public function settingsAction()
    {
        if( Fw_Request::post('doit', FALSE) ) {
            $html = array();
            $top_config = Fw_Request::post( 'top', array() );
            $data = Fw_Request::post( 'data', array() );
            
            foreach($top_config as $per_part) {
                $html[] = '[' . $per_part . ']';
                
                if( isset($data[$per_part]) ) {
                    foreach( $data[$per_part] as $per_name => $per_value ) {
                        $html[] = $per_name . ' = "' . $per_value . '"';
                    }
                }
            }
            
            C::saveConfig( 'application', implode("\n", $html) );
        }
        
        $this->view->data = C::getConfig('application');
        $this->view->render();
    }
    
    public function updateprojectAction()
    {
        $data = array();
        $id = Fw_Request::post('p_id', 0);
        $data['p_state'] = Fw_Request::post('p_state', 0);
        $data['p_need'] = Fw_Request::post('p_need', 0);
        $this->view->result = $this->model->updateMainProjectData($id, $data);
        
        if($this->view->result) {
            $data = array();
            $data['pl_description'] = Fw_Request::post('ru_pl_description', NULL);
            $data['pl_keywords'] = Fw_Request::post('ru_pl_keywords', NULL);
            $data['pl_text'] = Fw_Request::post('ru_pl_text_pre', '') . '{read_more}' . Fw_Request::post('ru_pl_text_end', '');
            $data['pl_alias'] = Fw_Request::post('ru_pl_alias', NULL);
            $data['pl_title'] = Fw_Request::post('ru_pl_title', NULL);
            $this->view->result = $this->model->updateProjectLangData($id, 'ru', $data);
            
            $data = array();
            $data['pl_description'] = Fw_Request::post('ua_pl_description', NULL);
            $data['pl_keywords'] = Fw_Request::post('ua_pl_keywords', NULL);
            $data['pl_text'] = Fw_Request::post('ua_pl_text_pre', '') . '{read_more}' . Fw_Request::post('ua_pl_text_end', '');
            $data['pl_alias'] = Fw_Request::post('ua_pl_alias', NULL);
            $data['pl_title'] = Fw_Request::post('ua_pl_title', NULL);
            $this->view->result = $this->model->updateProjectLangData($id, 'ua', $data);
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache');
        $this->view->render(false);
    }
    
    public function switchprojectphotoAction()
    {
        $data = Fw_Request::post('data', null);
        $id = Fw_Request::post('id', null);
        
        if( is_array($data) && $this->model->switchProjectPhoto($data) ) {
            Fw_Request::redirect('/admin/projectphotos/' . $id .'/');
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'project');
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'done');
        $this->view->render(false);
    }
    
    public function addprojectphotoAction()
    {
        $img = Fw_Request::getFile('pp_src', null);
        $id = Fw_Request::post('pp_p_id', null);
        
        if( !is_null($img) && isset($img['name']) && !empty($img['name']) ) {
            $type = array_pop( explode(".", $img['name']) );

            if( in_array( strtolower($type), array('png', 'jpeg', 'jpg', 'gif') ) ) {
                $new_file_name = sha1( $img['name'] . time() ) . '.' .$type;
                $to_path = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images') . C::ds . $new_file_name;

                if( move_uploaded_file($img['tmp_name'], $to_path) === false) {
                    throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка загрузки фотографии', 500);
                }
                
                $this->model->addProjectPhoto($id, $new_file_name);
                Fw_Request::redirect('/admin/projectphotos/' . $id .'/');
            } else {
                $this->view->error['u_img'] = C::getLanguageString('wrong_file_format');
            }
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'project');
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'done');
        $this->view->render(false);
    }
    
    public function dropprojectphotoAction()
    {
        $img = Fw_Request::post('pp_src', NULL);
        $id = Fw_Request::post('pp_id', NULL);
        $p_id = Fw_Request::post('pp_p_id', NULL);
        
        if( !$this->model->dropProjectPhoto($id) ) {
            return;
        }
        
        if( !empty($img) ) {
            $config = C::getConfig('img');
            $del_file = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images') . C::ds . $img;

            if( is_file($del_file) ) {
                unlink($del_file);
            }

            foreach($config['size'] as $sizes) {
                $del_file = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images/cropr_' . $sizes) . C::ds . $img;

                if( is_file($del_file) ) {
                    unlink($del_file);
                }
            }
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'project');
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'done');
        Fw_Request::redirect('/admin/projectphotos/' . $p_id . DS);
        $this->view->render(false);
    }
    
    public function projectphotosAction()
    {
        $id = Fw_Request::get('p_id', null);
        
        if(is_null($id) ) {
            $this->view->data = '';
        } else {
            $this->view->data = $this->model->getProjectPhoto($id);
        }
        
        $this->view->p_id = $id;
        $this->view->render(false);
    }
    
    public function saveprojectAction()
    {
        $data = array();
        $data['p_state'] = Fw_Request::post('p_state', 0);
        $data['p_need'] = Fw_Request::post('p_need', 0);
        $new_id = $this->model->saveMainProjectData($data);
        
        if($new_id) {
            $data = array();
            $data['pl_p_id'] = $new_id;
            $data['pl_lang'] = 'ru';
            $data['pl_description'] = Fw_Request::post('ru_pl_description', NULL);
            $data['pl_keywords'] = Fw_Request::post('ru_pl_keywords', NULL);
            $data['pl_text'] = Fw_Request::post('ru_pl_text_pre', '') . '{read_more}' . Fw_Request::post('ru_pl_text_end', '');
            $data['pl_alias'] = Fw_Request::post('ru_pl_alias', NULL);
            $data['pl_title'] = Fw_Request::post('ru_pl_title', NULL);
            $this->view->result = $this->model->saveProjectLangData($data);
            
            $data = array();
            $data['pl_p_id'] = $new_id;
            $data['pl_lang'] = 'ua';
            $data['pl_description'] = Fw_Request::post('ua_pl_description', NULL);
            $data['pl_keywords'] = Fw_Request::post('ua_pl_keywords', NULL);
            $data['pl_text'] = Fw_Request::post('ua_pl_text_pre', NULL) . '{read_more}' . Fw_Request::post('ua_pl_text_end', NULL);
            $data['pl_alias'] = Fw_Request::post('ua_pl_alias', NULL);
            $data['pl_title'] = Fw_Request::post('ua_pl_title', NULL);
            $this->view->result = $this->model->saveProjectLangData($data);
        } else {
            $this->view->result = false;
        }
        
        if($this->view->result) {
            $this->view->result = $new_id;
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache');
        $this->view->render(false);
    }
    
    public function getprojectpaysAction()
    {
        $id = Fw_Request::post('p_id');
        $this->view->data = $this->model->getProjectPays($id);
        $this->view->render(false);
    }
    
    public function deleteuserAction()
    {
        $id = Fw_Request::post('u_id');
        $this->view->result = false;
        
        if( !$this->model->testUser($id) ) {
            $this->view->result = $this->model->deleteUser($id);
        }
        
        $this->view->render(false);
    }
    
    public function getuserpayAction()
    {
        $id = Fw_Request::post('u_id');
        $this->view->data = $this->model->getUserPay($id);
        $this->view->render(false);
    }
    
    public function updateuserAction()
    {
        $data = array();
        $id = Fw_Request::post('u_id', null);
        $data['u_activate'] = Fw_Request::post('u_activate', null);
        $data['u_name'] = Fw_Request::post('u_name', null);
        $data['u_surname'] = Fw_Request::post('u_surname', null);
        $data['u_login'] = Fw_Request::post('u_login', null);
        $data['u_email'] = Fw_Request::post('u_email', null);
        $new_password = Fw_Request::post('new_password', null);
        $data['u_password'] = ( !empty($new_password) ? sha1($new_password) : Fw_Request::post('u_password', null) );
        $data['u_img'] = Fw_Request::post('u_img', null);
        $data['u_date_reg'] = Fw_Request::post('u_date_reg', null);
        $data['u_date_lastlogin'] = Fw_Request::post('u_date_lastlogin', null);
        $data['u_information'] = Fw_Request::post('u_information', null);
        $data['u_address'] = Fw_Request::post('u_address', null);
        $this->view->result  = $this->model->updateUser($id, $data);
        
        if( $this->view->result && !empty($new_password) ) {
            $this->view->result = sha1($new_password);
        }
        
        $this->view->render(false);
    }
    
    public function makeadminAction()
    {
        $id = Fw_Request::post('au_u_id', null);
        $admin = Fw_Request::post('admin', null);
        $this->view->result = $this->model->makeAdmin($id, $admin);
        $this->view->render(false);
    }
    
    public function usersAction()
    {
        $this->view->data = $this->model->getUsers();
        $this->view->render();
    }
    
    public function savestaticpageAction()
    {
        $data = array();
        $data['sp_body'] = Fw_Request::post('sp_body', null);
        $data['sp_mdc_id'] = Fw_Request::post('sp_mdc_id', null);
        $data['sp_mda_id'] = Fw_Request::post('sp_mda_id', null);
        $data['sp_lang'] = Fw_Request::post('sp_lang', null);
        $this->view->result = $this->model->saveStaticPage($data);
        $this->view->render(false);
    }
    
    public function updatestaticpageAction()
    {
        $data = array();
        $id = Fw_Request::post('sp_id', null);
        $data['sp_body'] = Fw_Request::post('sp_body', null);
        $data['sp_mdc_id'] = Fw_Request::post('sp_mdc_id', null);
        $data['sp_mda_id'] = Fw_Request::post('sp_mda_id', null);
        $data['sp_lang'] = Fw_Request::post('sp_lang', null);
        $this->view->result = $this->model->updateStaticPage($id, $data);
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'default');
        $this->view->render(false);
    }
    
    public function staticpageAction()
    {
        $this->view->paths = $this->model->getAllSystemPaths();
        $this->view->data = $this->model->getStaticPages();
        
        foreach($this->view->data as $value) {
            if( !in_array($value->sp_lang, $this->view->langs) ) {
                $this->view->langs[] = $value->sp_lang;
            }
        }
        
        $this->view->render();
    }
    
    public function savemetadataAction()
    {
        $data = array();
        $data['md_mdc_id'] = Fw_Request::post('md_mdc_id', null);
        $data['md_mda_id'] = Fw_Request::post('md_mda_id', null);
        $data['md_lang'] = Fw_Request::post('md_lang', null);
        $data['md_type'] = Fw_Request::post('md_type', null);
        $data['md_data'] = Fw_Request::post('md_data', null);
        $this->view->result = $this->model->saveMetaData($data);
        $this->view->render();
    }
    
    public function updatemetadataAction()
    {
        $data = array();
        $id = Fw_Request::post('md_id', null);
        $data['md_mdc_id'] = Fw_Request::post('md_mdc_id', null);
        $data['md_mda_id'] = Fw_Request::post('md_mda_id', null);
        $data['md_lang'] = Fw_Request::post('md_lang', null);
        $data['md_type'] = Fw_Request::post('md_type', null);
        $data['md_data'] = Fw_Request::post('md_data', null);
        $this->view->result = $this->model->updateMetaData($id, $data);
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'default');
        $this->view->render();
    }

    public function metadataAction()
    {
        $pre_types = $this->model->getMetaDataTypes();
        $this->view->types = explode( ",", str_replace(array('enum', '(', ')', "'"), '', $pre_types->COLUMN_TYPE ) );
        $this->view->paths = $this->model->getAllSystemPaths();
        $this->view->data = $this->model->getMetaData();
        
        foreach($this->view->data as $value) {
            if( !in_array($value->md_lang, $this->view->langs) ) {
                $this->view->langs[] = $value->md_lang;
            }
        }
        
        $this->view->render();
    }

    public function setAction() 
    {
        $param_0 = Fw_Request::get(0, null);
        $param_1 = Fw_Request::get(1, null);

        if( is_null($param_0) ) {
            return true;
        }

        if( method_exists($this, $param_0 . 'Action') ) {
            $this->actionName = $param_0;
            
            if($this->actionName == 'projectphotos') {
                Fw_Request::setGet('p_id', $param_1);
            }
            
            return true;
        }
        
        return false;
    }
    
    private function getCacheDir($dir)
    {
        if ( $objs = glob(  $dir . "/*") ) {
            foreach($objs as $obj) {
                if( is_dir($obj) ){
                    $this->dirs[$dir][] = $obj;
                    $this->getCacheDir($obj);
                } else {
                    continue;
                }
            }
        }
    }
    
    public function cacheAction()
    {
        $finder = APPLICATION_PATH . C::ds . 'cache';
        $this->getCacheDir($finder);
        $this->view->stripped = $finder . C::ds;
        $this->view->data = $this->dirs;
        $this->view->render();
    }
    
    private function rmCache($dir)
    {
        if ( $objs = glob($dir . "/*") ) {
            foreach($objs as $obj) {
                if( is_dir($obj) ){
                    $this->rmCache($obj);
                } else {
                    unlink($obj);
                }
            }
        }
        
        return ( array_pop( explode('/', $dir) ) == 'cache' ? true : rmdir($dir) );
    }
    
    public function flushcacheAction()
    {
        $path = Fw_Request::post('path');
        $this->view->result = $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . $path);
        $this->view->render(false);
    }
    
    public function breadcrumbsAction()
    {
        $this->view->data = $this->model->getBreadCrumbs();
        $this->view->paths = $this->model->getAllSystemPaths();
        
        foreach($this->view->data as $value) {
            if( !in_array($value->mb_lang, $this->view->langs) ) {
                $this->view->langs[] = $value->mb_lang;
            }
        }
        
        $this->view->render();
    }
    
    public function savebreadcrubsAction()
    {
        $data = array();
        $data['mb_mdc_id'] = Fw_Request::post('mb_mdc_id', NULL);
        $data['mb_mda_id'] = Fw_Request::post('mb_mda_id', NULL);
        $data['mb_lang'] = Fw_Request::post('mb_lang', NULL);
        $data['mb_data'] = Fw_Request::post('mb_data', NULL);
        $data['mb_data'] = ( !empty($data['mb_data']) ? serialize($data['mb_data']) : '' );
        $this->view->result = $this->model->saveBreadCrumb($data);
        $this->view->render(false);
    }
    
    public function updatebreadcrubsAction()
    {
        $data = array();
        $id = Fw_Request::post('mb_id', NULL);
        $data['mb_mdc_id'] = Fw_Request::post('mb_mdc_id', NULL);
        $data['mb_mda_id'] = Fw_Request::post('mb_mda_id', NULL);
        $data['mb_lang'] = Fw_Request::post('mb_lang', NULL);
        $data['mb_data'] = Fw_Request::post('mb_data', NULL);
        $data['mb_data'] = ( !empty($data['mb_data']) ? serialize($data['mb_data']) : '' );
        $this->view->result = $this->model->updateBreadCrumb($id, $data);
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'blocks');
        $this->view->render(false);
    }

    public function indexAction()
    {        
        $this->view->render();
    }
    
    public function systemAction()
    {
        $this->view->controllers = $this->model->getSystemControllers();
        $this->view->render();
    }
    
    public function systemgetactionsAction()
    {
        $id = Fw_Request::post('id', null);
        $this->view->actions = $this->model->getControllerActions($id);
        $this->view->render(false);
    }
    
    public function savenewactionAction()
    {
        $id = Fw_Request::post('id', null);
        $path = Fw_Request::post('path', null);
        $name = Fw_Request::post('name', null);
        
        if( !empty($path) && !empty($name) ) {
            $this->view->result = $this->model->saveNewAction($id, $path, $name);
        } else {
            $this->view->result = false;
        }
        
        $this->view->render(false);
    }
    
    public function menuAction()
    {
        $save = Fw_Request::post('save', false);
        
        if($save) {
            $data = Fw_Request::post('data', null);
            
            if( !is_null($data) ) {
                $this->model->saveMenuData($data);
            }
            
            $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'index');
        }
        
        $this->view->data = $this->model->loadMenuData();
        $this->view->render();
    }
    
    public function switchmenuAction()
    {
        $from = Fw_Request::post('from', null);
        $to = Fw_Request::post('to', null);
        
        if( !is_null($from) && !is_null($to) ) {
            $this->view->result = $this->model->switchMenuPoint($from, $to);
        } else {
            $this->view->result = false;
        }
        
        $this->rmCache(APPLICATION_PATH . C::ds . 'cache' . C::ds . 'blocks');
        $this->view->render(false);
    }
    
    public function menudeleteAction()
    {
        $id = Fw_Request::post("id", null);
        
        if( !is_null($id) ) {
            $this->view->result = $this->model->deleteMenuPoint($id);
        }
        
        $this->view->render(false);
    }
    
    public function savenewcontrollerAction()
    {
        $path = Fw_Request::post('path', null);
        
        if( !empty($path) ) {
            $this->view->result = $this->model->saveNewController($path);
        } else {
            $this->view->result = false;
        }
        
        $this->view->render(false);
    }
    
    public function updateactionAction()
    {
        $action_id = Fw_Request::post('id', null);
        $path = Fw_Request::post('path', null);
        $name = Fw_Request::post('name', null);
        
        if( !empty($path) && !empty($name) ) {
            $this->view->result = $this->model->updateAction($action_id, $path, $name);
        } else {
            $this->view->result = false;
        }
        
        $this->view->render(false);
    }
    
    public function updatecontrollerAction()
    {
        $controller_id = Fw_Request::post('id', null);
        $path = Fw_Request::post('path', null);
        
        if( !empty($path) ) {
            $this->view->result = $this->model->updateController($controller_id, $path);
        } else {
            $this->view->result = false;
        }
        
        $this->view->render(false);
    }
    
    public function langAction()
    {
        $this->view->const = array();
        
        if( Fw_Request::post('save', false) ) {
            $data = Fw_Request::post('data', NULL);
            $html = array();
            
            foreach($data as $lang => $consts) {
                $html[] = '[' . $lang . ']';
                 
                foreach($consts as $const => $value) {
                    $html[] = $const . ' = "' . stripslashes( str_replace(array('"'), array("'"), html_entity_decode($value, ENT_QUOTES, "UTF-8") ) ) . '"';
                }
            }
            
            C::saveConfig( 'language', implode("\n", $html) );
        }
        
        $langs = C::getConfig('language', false);
        
        foreach($langs as $lang => $value) {
            foreach($value as $const => $translate) {
                $this->view->const[$const][$lang] = htmlentities($translate, ENT_QUOTES, "UTF-8");
            }
            
            
            if( !in_array($lang, $this->view->langs) ) {
                $this->view->langs[] = $lang;
            }
        }
        
        $this->view->render();
    }
}
