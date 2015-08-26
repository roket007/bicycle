<?php

class Fw_Request
{

    public static $post_data;
    public static $get_data;

    public static function post($name, $default = null)
    {
        if( isset(self::$post_data[$name]) ) {
            return self::$post_data[$name];
        } else if( isset($_POST[$name]) ) {
            self::$post_data[$name] = $_POST[$name];
            unset($_POST[$name]);
            return self::$post_data[$name];
        } else {
            return $default;
        }
    }

    public static function setPost($name, $value)
    {
        self::$post_data[$name] = $value;

        if( isset($_POST[$name]) ) {
            unset($_POST[$name]);
        }

        return true;
    }

    public static function deletePost($name)
    {
        if( isset(self::$post_data[$name]) ){
            unset(self::$post_data[$name]);
        } else if( isset($_POST[$name]) ) {
            unset($_POST[$name]);
        }

        return true;
    }

    public static function getCharset($str)
    {
        $cp_list = array('UTF-8', 'Windows-1251');

        foreach ($cp_list as $k => $codepage) {
            if ( md5($str) === md5( iconv($codepage, $codepage, $str) ) ) {
                return $codepage;
            }
        }

        return null;
    }

    public static function setCharset($str, $charset)
    {
        $current_charset = self::getCharset($str);

        if( $current_charset !== $charset ) {
            return iconv($current_charset, $charset, $str);
        } else {
            return $str;
        }
    }

    public static function get($name, $default = null) 
    {

        if( isset(self::$get_data[$name]) ) {
            return self::$get_data[$name];
        } else if( isset($_GET[$name]) ) {
            self::$get_data[$name] = $_GET[$name];
            unset($_GET[$name]);
            return self::$get_data[$name];
        } else {
            return $default;
        }
    }

    public static function setGet($name, $value)
    {
        self::$get_data[$name] = $value;

        if( isset($_GET[$name]) ) {
            unset($_GET[$name]);
        }

        return true;
    }

    public static function getFile($name, $default = null)
    {
        if( isset($_FILES[$name]) ) {
            return $_FILES[$name];
        }

        return $default;
    }

    public static function deleteGet($name) 
    {   
        if( isset(self::$get_data[$name]) ) {
            unset(self::$get_data[$name]);
        } else if ( isset($_GET[$name]) ) {
            unset($_GET[$name]);
        }

        return true;
    }

    public static function postNoHtml($name, $min_len = 0, $max_len = 1000, $no_trim = false)
    {
        $value = self::post($name, null);

        if( mb_strlen($value, 'UTF-8') >= $min_len && mb_strlen($value, 'UTF-8') <= $max_len ) {
            $data = str_replace( array("\n"), array("<br />"), htmlentities( ( $no_trim ? strip_tags( $value ) : trim( strip_tags( $value ) ) ), ENT_QUOTES, 'UTF-8' ) );
            return (strlen($data) > 0 ? $data : null);
        } else {
            return null;
        }
    }

    public static function postInt($name, $default = null)
    {
        $value = self::post($name, null);

        if( is_numeric($value) ) {
            return $value;
        } else return $default;
    }

    public static function getInt($name, $default = null)
    {
        $value = self::get($name, null);

        if( is_numeric($value) ) {
            return $value;
        } else return $default;
    }

    public static function validLogin($login = null)
    {
        if( is_null($login) ) {
            $login = self::post('login', false);
        }

        if( preg_match("/^[a-z\d_\-]{2,50}$/ui", $login) ) {
            return true;
        } else return false;
    }

    public static function redirect($url, $code = false)
    {
        switch($code) {
            case 301:
                header("HTTP/1.1 301 Moved Permanently");
                break;
            case 302:
                header("HTTP/1.1 302 Moved Temporarily");
                break;
        }

        header("Location: " . C::site_host . $url);
        die;
    }
}
