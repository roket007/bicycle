<?php

class Fw_Auth
{        
    public static function start($login, $password)
    {
        $password = sha1($password);
        $model = Fw_Model::getInstance();
        $model->setQuery("
            SELECT * 
            FROM users 
            LEFT JOIN admin_users ON au_u_id = u_id
            WHERE 
                u_login = '{$login}'
                AND u_password = '{$password}'
                AND u_activate = 1 
            LIMIT 1
        ");
        $model->sendQuery(false);
        $data = $model->fetchRow();

        if(!$data) {
            return false;
        } else {
            $model->setQuery("UPDATE users SET u_date_lastlogin = NOW() WHERE u_id = " . $data['u_id']);
            $model->sendQuery(false);
            $_SESSION['login'] = $data['u_login'];
            $_SESSION['id'] = $data['u_id'];

            if( !empty($data['au_u_id']) ) {
                $_SESSION['admin'] = true;
            } else {
                $_SESSION['admin'] = false;
            }
        }

        return true;
    }

    public static function get($name)
    {
        if( isset($_SESSION[$name]) ) {
            return $_SESSION[$name];
        } else return null;
    }

    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function isValid()
    {
        if( !isset($_SESSION['login']) || !isset($_SESSION['id']) ) {
            return false;
        }

        return true;
    }

    public static function isValidAdmin()
    {
        if( isset($_SESSION['admin']) && $_SESSION['admin'] === true ) {
            return true;
        }

        return false;
    }

    public static function end()
    {
        foreach($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }

        session_destroy();
    }
}
