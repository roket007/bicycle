<?php

class Fw_Model
{
    protected static $instance;
    protected $_load_cache = false;
    protected $_cache_actual = false;
    protected $_cache_tags = array();
    protected $_cache;
    protected $_cache_key;
    protected $_cache_time;
    protected $_cache_file;
    protected $_cache_dir;
    public $_query = '';
    public $_models_pack = array();
    public $_adaptor_result = false;
    public $_result;
    public $_name;

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public static function getInstance() 
    {
        if ( !isset(self::$instance) ) {
            $class = __CLASS__;
            self::$instance = new $class();
            return self::$instance;
        }

        return self::$instance;
    }

    public function getModel($name = null)
    {
        if( isset($this->_models_pack[$name . 'Model']) ) {
            return $this->_models_pack[$name . 'Model'];
        } else {
            $file = APPLICATION_PATH . '/models/' . $name . '.php';
            if( !is_file($file) ) throw new Fw_AniException('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Невозможно загрузить модель ' . $file, 500);
            include_once $file;
            $name = $name . 'Model';
            $this->_models_pack[$name] = new $name($this);
            return $this->_models_pack[$name];
        }
    }

    public function setQuery($query)
    {
        $this->_cache = false;
        $this->_load_cache = false;
        $this->_cache_actual = false;
        $this->_cache_tags = array();
        $this->_cache_dir = '';
        $this->_result = array();
        $this->_query = $query;
    }

    public function sendQuery( $cache = false, $cache_time = 0, $cache_tags = array() )
    {
        $this->_cache = $cache;

        if($cache && $cache_time > 0) {
            $this->_cache_key = md5($this->_query);
            $this->_cache_time = $cache_time;
            $this->_cache_tags = $cache_tags;
            $this->_cache_dir = APPLICATION_PATH . C::ds . 'cache' . C::ds . implode(C::ds, $this->_cache_tags);
            $this->_cache_file = $this->_cache_dir . C::ds . $this->_cache_key;

            if( $this->cacheTest() ) {
                $this->_load_cache = true;

                if( $this->cacheActual() ) {
                    $this->_cache_actual = true;
                    return;
                }
            }
        }

        $this->_adaptor_result = @mysql_query($this->_query);

        $trimmer_query = trim($this->_query);

        if( stripos($trimmer_query, 'INSERT') >= 0) {
            return $this->_adaptor_result;
        }
        
        if( stripos($trimmer_query, 'REPLACE') >= 0) {
            return $this->_adaptor_result;
        }

        if( stripos($trimmer_query, 'UPDATE') >= 0) {
            return $this->_adaptor_result;
        }
        
        if( stripos($trimmer_query, 'DELETE') >= 0) {
            return $this->_adaptor_result;
        }

        if(!$this->_adaptor_result) {
            throw new Fw_Exception(
                '[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Невозможно выполнить запрос! "' . $this->_query . '" ' . mysql_error(), 
                500
            );
        }
    }

    public function cacheTest()
    {
        if( file_exists($this->_cache_file) ) {
            return true;
        }

        return false;
    }

    public function cacheActual()
    {
        if( ( time() - filemtime($this->_cache_file) ) < $this->_cache_time ) {
            return true;
        }

        return false;
    }

    public function cacheCreate($data)
    {
        if( empty($data) ) {
            return;
        }

        if( !is_dir($this->_cache_dir) ) {
            mkdir($this->_cache_dir, 0750, true);
        }

        if( file_put_contents( $this->_cache_file, serialize($data) ) === false ) {
            throw new Fw_Exception(
                '[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Невозможно создать кэш базы данынх! "' . $this->_query . '" ' . mysql_error(), 
                500
            );
        } else {
            return;
        }
    }

    public function cacheLoad()
    {
        return unserialize( file_get_contents($this->_cache_file) );
    }

    public function fetchRows()
    {
        if($this->_cache_actual) {
            return $this->cacheLoad();
        }

        while( $row = mysql_fetch_array($this->_adaptor_result) ) {
            $this->_result[] = $row;
        }

        mysql_free_result($this->_adaptor_result);

        if($this->_cache) {
            $this->cacheCreate($this->_result);
        }

        return $this->_result;
    }

    public function fetchRow($without_numeric = false)
    {
        if($this->_cache_actual) {
            return $this->cacheLoad();
        }

        $this->_result = ( $without_numeric ? mysql_fetch_assoc($this->_adaptor_result) : mysql_fetch_array($this->_adaptor_result) );
        mysql_free_result($this->_adaptor_result);

        if($this->_cache) {
            $this->cacheCreate($this->_result);
        }

        return $this->_result;
    }

    public function fetchObjects()
    {
        if($this->_cache_actual) {
            return $this->cacheLoad();
        }

        while( $row = mysql_fetch_object($this->_adaptor_result) ) {
            $this->_result[] = $row;
        }

        mysql_free_result($this->_adaptor_result);

        if($this->_cache) {
            $this->cacheCreate($this->_result);
        }

        return $this->_result;
    }

    public function fetchObject()
    {
        if($this->_cache_actual) {
            return $this->cacheLoad();
        }

        $this->_result = mysql_fetch_object($this->_adaptor_result);
        mysql_free_result($this->_adaptor_result);

        if($this->_cache) {
            $this->cacheCreate($this->_result);
        }

        return $this->_result;
    }
}
