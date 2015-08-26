<?php
class userModel extends Fw_Model_Base
{
    public function chekEmailRepeat($email)
    {
        $this->sql->setQuery("SELECT u_id FROM users WHERE u_email = '{$email}'");
        $this->sql->sendQuery();
        $result = $this->sql->fetchRow();
        return ( isset($result['u_id']) && is_numeric($result['u_id']) ? true : false );
    }
    
    public function getUserByEmail($email)
    {
        $this->sql->setQuery("SELECT * FROM users WHERE u_email = '{$email}'");
        $this->sql->sendQuery();
        return $this->sql->fetchRow();
    }
    
    public function activateUser($id)
    {
        $this->sql->setQuery("UPDATE users SET u_activate = 1 WHERE u_id = {$id}");
        return $this->sql->sendQuery();
    }
    
    public function chekLoginRepeat($login)
    {
        $this->sql->setQuery("SELECT u_id FROM users WHERE u_login = '{$login}'");
        $this->sql->sendQuery();
        $result = $this->sql->fetchRow();
        return ( isset($result['u_id']) && is_numeric($result['u_id']) ? true : false );
    }
    
    public function updateUserData($data)
    {
        $sql_parts = array();
        $id = Fw_Auth::get('id');
        
        foreach($data as $key => $value) {
            $sql_parts[] = "`" . $key . "` = '" . $value . "'";
        }
        
        $this->sql->setQuery("UPDATE users SET " . implode(",", $sql_parts) . " WHERE u_id = " . $id);
        return $this->sql->sendQuery();
    }
    
    public function registrationNewUser($data) {
        $this->sql->setQuery(" 
            INSERT INTO users (
                u_activate,
                u_name,
                u_surname,
                u_login,
                u_email,
                u_password,
                u_img,
                u_date_reg,
                u_date_lastlogin,
                u_information,
                u_address
            ) VALUES (
                0,
                '" . $data['name'] . "',
                '" . $data['surname'] . "',
                '" . $data['login'] . "',
                '" . $data['email'] . "',
                '" . sha1($data['password']) . "',
                '" . C::default_user_img . "',
                DEFAULT,
                DEFAULT,
                '',
                DEFAULT
            )
        ");
        
        return $this->sql->sendQuery();
    }
}
