<?php

class Classes_Contacts extends Fw_Controller
{
    public $name = 'contacts';
    public $actionName = 'index';
    public $errors = array(
        'antispam' => false,
        'name' => false,
        'email' => false,
        'subject' => false,
        'text' => false
    );

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
        $data['name'] = Fw_Request::post('name', NULL);
        $data['email'] = Fw_Request::post('email', NULL);
        $data['subject'] = Fw_Request::post('subject', NULL);
        $data['text'] = trim( strip_tags( Fw_Request::post('text', NULL) ) );
        $data['antispam'] = Fw_Request::post('antispam', NULL);
        $error = false;
        $this->view->enable = C::getConfig('application');
        
        
        if( !is_null($data['antispam']) ) {
            if( !$this->checkAntispam($data['antispam']) ) {
                $this->errors['antispam'] = true;
                $error = true;
            }
            
            if( !preg_match("/^[a-zA-Zа-яА-Я\s\d\-_]{3,50}$/ui", $data['name']) ) {
                $this->errors['name'] = true;
                $error = true;
            }
            
            if( !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ) {
                $this->errors['email'] = true;
                $error = true;
            }
            
            if( !preg_match("/^[a-zA-Zа-яА-Я\s\d\-_]{3,255}$/ui", $data['subject']) ) {
                $this->errors['subject'] = true;
                $error = true;
            }
            
            if( empty( $data['text'] ) ) {
                $this->errors['text'] = true;
                $error = true;
            }
            
            if($error) {
                $this->view->errors = $this->errors;
                $this->view->data = $data;
            } else {
                $this->model->saveMessage($data);
                $this->sendMessage($data);
                Fw_Request::redirect( (C::$lang == 'ru' ? '/ru' : '') . '/contacts/send/');
            }
        }
        
        //Olala antispam
        $time = time();
        $this->view->data = $data;
        $super_secure = substr($time, -5) . substr($time, 0, -5) . $time;
        $this->view->text = Fw_Model::getInstance()->getModel('default')->loadStaticPage($this->name, $this->actionName);
        $this->view->antispam = base64_encode($super_secure);
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
    
    protected function sendMessage($data)
    {
        $config = C::getConfig('application');
        if($config['contacts']['enable']) {
            $to = $config['contacts']['email'];
            $subject = "Биржа благотворительности Lovechernivtsi";

            $message = ' 
            <html> 
                <head> 
                    <title>You have new feedback message!</title> 
                </head> 
                <body> 
                    <p>Name: ' . $data['name'] . '</p> 
                    <p>Email: ' . $data['email'] . '</p>
                    <p>Subject: ' . $data['subject'] . '</p>
                    <p>Text: ' . $data['text'] . '</p>
                </body> 
            </html>'; 

            $headers  = "Content-type: text/html; charset=UTF-8 \r\n"; 
            $headers .= "From: Love Chernivtsy <" . $config['contacts']['admin_email'] . ">\r\n";

            if( !mail($to, $subject, $message, $headers) ) {
                Log::add('Can\'t send email!', 500);
            }
        }
    }

    protected function checkAntispam($code)
    {
        $pre_code = base64_decode($code);
        $pre_end_code = substr($pre_code, 0, -10);
        $end_code = substr($pre_end_code, -5) . substr($pre_end_code, 0, -5);
        
        if( substr($pre_code, -10) == $end_code) {
            return true;
        }
        
        return false;
    }
    
    public function sendAction()
    {
        $this->view->setBlocks( array('breadcrumbs') );
        $this->view->render();
    }
}
