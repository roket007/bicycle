<?php

class Classes_User extends Fw_Controller
{
    public $name = 'user';
    public $actionName = 'index';

    public function setAction() 
    {
        $param_0 = Fw_Request::get(0, null);
        $param_1 = Fw_Request::get(1, null);

        if( is_null($param_0) ) {
            return true;
        }

        if( method_exists($this, $param_0 . 'Action') ) {
            $this->actionName = $param_0;
            
            if($param_0 == 'confirm') {
                if( !is_null($param_1) ) {
                    Fw_Request::setGet('key', $param_1);
                } else {
                    return false;
                }
            }
            
            return true;
        } 

        if( !is_null($param_0) ) {
            return false;
        }
    }

    public function loginAction()
    {
        $this->view->setLayout($this->name, 'index');
        $this->indexAction();
    }

    public function logoutAction()
    {
        Fw_Auth::end();
        Fw_Request::redirect( C::uri() );
    }

    public function indexAction()
    {
        $this->view->errorLogin = false;

        if( Fw_Auth::isValid() ) {
            Fw_Request::redirect( C::uri( array( 'donors', Fw_Auth::get('id') ) ) );
        } else {
            $login = Fw_Request::post('login', null);
            $password = Fw_Request::post('password', null);
            $referer = base64_decode( Fw_Request::post('referer', null) );
            
            if( !is_null($password) && !is_null($login) ) {

                if( Fw_Request::validLogin($login) ) {
                    if( Fw_Auth::start($login, $password) ) {
                        Fw_Request::redirect($referer);
                    } else {
                        $this->view->errorLogin = true;
                    }
                } else {
                    $this->view->errorLogin = true;
                }
                
                $this->view->referer = $referer;
            }
            
            $this->view->render();
        }
    }

    public function registrationAction()
    {
        if( Fw_Auth::isValid() ) {
            Fw_Request::redirect( C::uri( array( 'donors', Fw_Auth::get('id') ) ) );
        }
        
        $antispam = Fw_Request::post('antispam', NULL);
        $data = array();
        $error = array();
        
        if( !is_null($antispam) ) {
            if( $this->checkSecureKey($antispam) ) {
                $data['name'] = Fw_Request::post('name', NULL);
                $data['surname'] = Fw_Request::post('surname', NULL);
                $data['email'] = Fw_Request::post('email', NULL);
                $data['login'] = Fw_Request::post('login', NULL);
                $data['password'] = Fw_Request::post('password', NULL);
                $data['repassword'] = Fw_Request::post('repassword', NULL);
                
                if( !preg_match("/^[\p{L}]{2,50}$/ui", $data['name']) ) {
                    $error['name'] = C::getLanguageString('registration_only_chars_name');
                }
                
                if( !preg_match("/^[\p{L}]{2,50}$/ui", $data['surname']) ) {
                    $error['surname'] = C::getLanguageString('registration_only_chars_surname');
                }
                
                if( !preg_match("/^[-a-z0-9!#$%&'*+\/=?^_`{|}~]+(?:\.[-a-z0-9!#$%&'*+\/=?^_`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/ui", $data['email']) ) {
                    $error['email'] = C::getLanguageString('contact_error_email');
                } elseif( $this->model->chekEmailRepeat($data['email']) ) {
                    $error['email'] = C::getLanguageString('registration_repeat_email');
                }
                
                if( !preg_match("/^[a-z\d_\-]{2,50}$/ui", $data['login']) ) {
                    $error['login'] = C::getLanguageString('contact_error_login');
                } elseif( $this->model->chekLoginRepeat($data['login']) ) {
                    $error['login'] = C::getLanguageString('registration_repeat_login');
                }
                
                if( !preg_match("/^.{8,64}$/ui", $data['password']) ) {
                    $error['password'] = C::getLanguageString('registration_password_error');
                }
                
                if( sha1($data['password']) !== sha1($data['repassword']) ) {
                    $error['repassword'] = C::getLanguageString('registration_repassword_error');
                }
                
                if( empty($error) ) {
                    if( !$this->model->registrationNewUser($data) ) {
                        throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка сохранения пользователя', 500);
                    }
                    
                    $this->sendConfirmationMail($data);
                    Fw_Request::redirect( C::uri( array('user', 'success') ) );
                } else {
                    $this->view->error = $error;
                    $this->view->data = $data;
                }
            } else {
                die('You\'r bot');
            }
        }
        
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->antispam = $this->createSecureKey();
        $this->view->render();
    }
    
    public function successAction()
    {
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
    
    public function confirmAction()
    {
        $key = base64_decode( Fw_Request::get('key', NULL) );
        
        if( strpos($key, '_') === false ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка подтверждения регистрации type 1', 404);
        }
        
        list($email, $hash) = explode("_", $key);
        
        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка подтверждения регистрации type 2 - ' . $email, 404);
        }
        
        $user_data = $this->model->getUserByEmail($email);
        
        if( empty($user_data) ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка подтверждения регистрации type 3 - ' . $email, 404);
        }
        
        if( $user_data['u_activate'] ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Попытка повторного подтверждения регистрации ' . $email, 404);
        }
        
        if( $hash !== sha1($user_data['u_email'] . $user_data['u_login'] . C::site_host) ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка подтверждения регистрации type 4 - ' . $email, 404);
        }
        
        if( !$this->model->activateUser($user_data['u_id']) ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Ошибка активации пользователя u_id = ' . $user_data['u_id'], 500);
        }
        
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
    
    private function sendConfirmationMail($data)
    {
        $config = C::getConfig('application');
        
        if($config['contacts']['enable']) {
            $to = $data['email'];
            $subject = "No replay. Подтверждение регистрации на бирже LoveChernivtsi";
            $link_confirm = C::site_host . C::uri( array( 'user', 'confirm', base64_encode( $data['email'] . "_" . sha1($data['email'] . $data['login'] . C::site_host) ) ) );
            $message = ' 
            <html> 
                <head> 
                    <title>Вы зарегистрировались на бирже благотворительности LoveChernivtsi</title> 
                </head> 
                <body> 
                    <p>Для подтверждения регистрации пройдите по ссылке указанной ниже:</p> 
                    <p><a href="' . $link_confirm . '">' . $link_confirm . '</a></p>
                    <p>Если ссылка не открывается скопируйте ее и вставьте в адресную строку браузера.</p>
                    <p style="font-size: 10px;">Если вы не регистрировались на данном ресурсе, игнорируйте письмо.</p>
                </body> 
            </html>'; 

            $headers  = "Content-type: text/html; charset=UTF-8 \r\n"; 
            $headers .= "From: Love Chernivtsy <" . $config['contacts']['admin_email'] . ">\r\n";

            if( !mail($to, $subject, $message, $headers) ) {
                Log::add('Can\'t send email!', 500);
            }
        }
    }
    
    private function checkSecureKey($key)
    {
        if( !preg_match("/^[\w]{32}$/ui", $key) ) {
            return false;
        }
        
        $antispam_path = APPLICATION_PATH . C::ds . 'cache' . C::ds . 'antispam';
        
        if( is_file($antispam_path . C::ds . $key) ) {
            $file_list = scandir($antispam_path);
            
            if( is_array($file_list) ) {
                foreach( scandir($antispam_path) as $per_file ) {
                    if( is_file($antispam_path . C::ds . $per_file) && ( time() - filemtime($antispam_path . C::ds . $per_file) ) > 600 ) {
                        unlink($antispam_path . C::ds . $per_file);
                    }
                }
            }
            
            unlink($antispam_path . C::ds . $key);
            return true;
        } else {
            return false;
        }
    }


    private function createSecureKey()
    {
        $path_to_cache = APPLICATION_PATH . C::ds . 'cache' . C::ds . 'antispam';
        
        if( !is_dir($path_to_cache) ) {
            mkdir($path_to_cache, 0750, true);
        }
        
        $antispam_key = md5( time() );
        $antispam_path = $path_to_cache . C::ds . $antispam_key;
        
        if( file_put_contents($antispam_path, ' ') ) {
            return $antispam_key;
        } else {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Нельза записать антиспам ключ', 500);
        }
    }
}
