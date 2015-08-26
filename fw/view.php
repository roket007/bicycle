<?php

class Fw_View
{
    public $_dir_layout;
    public $_dir_template;
    public $_dir_js;
    public $_web_js;
    public $_dir_css;
    public $_web_css;
    public $_template;
    public $_layout;
    public $_data = array();
    public $_custom_headers = array();
    public $_head = array();
    public $blocks;
    public $blocks_dir;

    public function __construct($dir, $view)
    {   
        $this->_dir_layout = APPLICATION_PATH . '/view/';
        $this->_dir_template = APPLICATION_PATH . '/tmp/';
        $this->_dir_js = PUBLIC_PATH . '/js/';
        $this->_web_js = '/js/';
        $this->_dir_css = PUBLIC_PATH . '/css/';
        $this->_web_css = '/css/';
        $this->_template;
        $this->_layout;
        $this->_data = array();
        $this->_custom_headers = array();
        $this->_head = array();
        $this->blocks;
        $this->blocks_dir = APPLICATION_PATH . '/blocks/';
        $this->setLayout($dir, $view);
        $this->setTemplate();
    }

    public function setBlocks($names = array())
    {
        foreach($names as $num => $value) {
            if( is_numeric($num) ) {
                $filename = $this->blocks_dir . $value;

                if( is_dir($filename) && is_file($filename . DIRECTORY_SEPARATOR . 'render.php') && is_file($filename . DIRECTORY_SEPARATOR . 'view.phtml') ) {
                    include_once $filename . DIRECTORY_SEPARATOR . 'render.php';
                    $block_name = $value . 'Block';
                    $this->blocks[$value] = new $block_name();
                } else {
                    throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Неполноценный блок ' . $filename, 500);
                }
            } else {
                $filename = $this->blocks_dir . $num;

                if( is_dir($filename) && is_file($filename . DIRECTORY_SEPARATOR . 'render.php') && is_file($filename . DIRECTORY_SEPARATOR . 'view.phtml') ) {
                    include_once $filename . DIRECTORY_SEPARATOR . 'render.php';
                    $block_name = $num . 'Block';
                    $this->blocks[$num] = new $block_name();

                    if( is_array($value) ) {
                        foreach($value as $var_name => $var_value) {
                            $this->blocks[$num]->$var_name = $var_value;
                        }
                    }
                } else {
                    throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Неполноценный блок ' . $filename, 500);
                }
            }
        }
    }

    public function setDefaultAssets()
    {
        $this->setJs('main.js');
        $this->setJs('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', true);
        $this->setCss('main.css');
    }

    public function setLayout($dir, $view = 'index')
    {
        $this->_layout = $this->_dir_layout . $dir . DIRECTORY_SEPARATOR . $view . '.phtml';

        unset($dir);
        unset($view);
    }

    public function addHeaders($headers) {
        if( !is_array($headers) ) {
            return false;
        }

        foreach($headers as $value) {
            $this->_custom_headers[] = $value;
        }
    }

    public function setTemplate($name = 'default')
    {
        $this->_template = $this->_dir_template . $name . '.php';

        unset($name);
    }

    public function __set($name, $value) 
    {
        $this->$name = $value;

        unset($name);
        unset($value);
    }

    public function __get($name) 
    {
        if ( isset($this->$name) ) {
            return $this->$name;
        }

        unset($name);

        return null;
    }

    public function setHead($name, $attr = array(), $html = '', $end_tag = true)
    {
        $_head_attr = '';

        foreach($attr as $name_attr => $value_attr) {
            $_head_attr .= $name_attr . '="' . $value_attr . '" ';
        }

        $_head_html = '<' . $name . ' ' . $_head_attr . ($end_tag ? '>' : '/>');
        $_head_html .= ($end_tag ? $html : '');
        $_head_html .= ($end_tag ? '</' . $name . '>' : '');
        $this->_head[] = $_head_html;
    }

    public function setJs($file, $no_local = false) {
        $local_file = $this->_dir_js . $file;
        $web_path = ( $no_local ? '' : $this->_web_js) . $file;

        if( is_file($local_file) || $no_local ) {
            $this->setHead( 'script', array('type' => 'text/javascript', 'src' => $web_path) );
        } else {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Отсутствует javascript файл! Template = ' . $local_file, 500);
        }
    }

    public function setCustom($string) {
        $this->_head[] = $string;
    }

    public function setCss($file) {
        $local_file = $this->_dir_css . $file;
        $web_path = $this->_web_css . $file;

        if( is_file($local_file) ) {
            $this->setHead( 'link', array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $web_path), '', false );
        } else {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Отсутствует javascript файл! Template = ' . $local_file, 500);
        }
    }

    protected function renderHead()
    {
        foreach ($this->_head as $per_head) {
            echo $per_head . "\n";
        }
    }

    public function renderContent() {
        require_once $this->_layout;
    }

    public function renderHeaders() {
        foreach($this->_custom_headers as $value) {
            header($value);
        }
    }

    public function render($need_render_tmp = true, $need_render_content = true) 
    {
        if( !is_file($this->_layout) && $need_render_content ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Отсутствует файл шаблона контента! Controller = ' . $this->_layout, 500);
        }

        if( !is_file($this->_template) && $need_render_tmp ) {
            throw new Fw_Exception('[f:"' . __FILE__ . '", l:"' . __LINE__ . '"] Отсутствует файл шаблона! Template = ' . $name, 500);
        }

        $this->renderHeaders();

        if($need_render_tmp && $need_render_content) {
            require_once $this->_template;
        } else if(!$need_render_tmp && $need_render_content) {
            $this->renderContent();
        } else {
            return;
        }
    }
}
