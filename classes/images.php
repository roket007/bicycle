<?php
    defined('ANIPORT') || die;
    require_once APPLICATION_PATH . '/moduls/img_cropper/ThumbLib.inc.php';
    
    class Classes_Images extends Fw_Controller
    {
        public $name = 'imgaes';
        public $actionName = 'index';
        public $img_sizes;
        private $_def_width;
        private $_def_height;

        /**
         * Имя картинки
         * @var string
         */
        private $_img_name;

        /**
         * Операцию которую нужно выполнить над картинкой,(res -изменить размер, crop - обрезать)
         * @var string
         */
        private $_operation;

        /**
         * Набор доступных операций
         * @var array
         */
        private $_operation_arr;

        private $_def_img_name;
        private $_def_part;
        private $_def_img_directory;

        /**
         * Новая картинка
         * @var string
         */
        private $_new_pic;

        /**
         * Путь к картинкам
         * @var string
         */
        private $_pic_path;

        /**
         * Новый путь к картинке
         * @var string
         */
        private $_new_pic_path;

        /**
         * Массив размеров для обработки
         * @var array
         */
        private $_size;

        /**
         * Часть Url в которой содержиться <operation>_<width>x<height>
         * @var string
         */
        private $_size_part;
        
        public function setAction() 
        {
            $param_0 = Fw_Request::get(0, null);
            $param_1 = Fw_Request::get(1, null);
            
            if( is_null($param_0) ) {
                return false;
            }
            
            if( is_null($param_0) ) {
                return false;
            }
            
            Fw_Request::setGet('size', $param_0);
            Fw_Request::setGet('name', $param_1);
            
            return true;
        }
        
        public function init()
        {
            $res = true;
            $this->img_conf = C::getConfig('img');

            $this->_def_width = 25;
            $this->_def_height = 25;

            $this->_def_img_name = 'default.jpg';
            $this->_def_img_directory = 'images';

            $this->_operation_arr = array('res', 'crop', 'cropr', 'cropg');
            $this->_pic_path = realpath(APPLICATION_PATH . '/../' . C::pub_dir . '/images');

            $params = array();
            $params['name'] = Fw_Request::get('name', '');
            $params['size'] = Fw_Request::get('size', '');

            if ( !empty($params['name']) ) {
                $this->_img_name = $params['name'];
            } else {
                $res = false;
            }

            if ( !empty($params['size']) ) {	
                $p_arr = explode('_', $params['size']);
                
                if ( !empty($p_arr[0]) && in_array($p_arr[0], $this->_operation_arr) ) {	
                    $this->_operation = $p_arr[0];
                    
                    if ( !empty($p_arr[1]) && in_array($p_arr[1], $this->img_conf['size']) ) {
                        $size_arr = explode('x', $p_arr[1]);
                        
                        if ( is_numeric($size_arr[0]) && is_numeric($size_arr[1]) ) {	
                            $this->_size[0] = (int)$size_arr[0];	
                            $this->_size[1] = (int)$size_arr[1];	
                        }
                    } else {
                        $res = false;
                    }
                } else {
                    $res = false;
                }
            } else {
                $res = false;
            }

            if (!$res) {
                header("Status: 404 Not Found");
                exit;
            }
        }
        
        public function indexAction()
        {
            $real_pic = implode('/', array($this->_pic_path, $this->_img_name));
		
            if ( !file_exists($real_pic) ) {
                header("Status: 404 Not Found");
                return '';
            }

            $this->image = PhpThumbFactory::create($real_pic); //new Zend_Image_Transform($real_pic, new Zend_Image_Driver_Gd);


            $this->_size_part = $this->_operation . '_' . implode('x', $this->_size);

            $this->_new_pic_path = implode( '/', array($this->_pic_path, $this->_size_part) );

            $this->_new_pic = implode( '/', array($this->_new_pic_path, $this->_img_name) );

            if ( !file_exists($this->_new_pic_path) ) {
                mkdir($this->_new_pic_path);
            }

            if ('res' == $this->_operation) {
                $this->resizeImage();
            } elseif ('crop' == $this->_operation) {
                $this->cropImage();
            } elseif ('cropr' == $this->_operation) {
                $this->cropResize();
            } elseif ('cropg' == $this->_operation) {
                $this->cropResizeUp();
            }
            
            $this->image->show();
        }
        
        /**
         * Обрезает картинку
         * 
         * @param unknown_type $params
         */
        public function cropImage()
        {
            $this->image->crop(0, 0, $this->_size[0], $this->_size[1]);
            $this->image->save($this->_new_pic);       
        }

        /**
         * Изменяет размер картинки
         * 
         * @param $params
         */
        public function resizeImage()
        {
            $this->image->resize($this->_size[0], $this->_size[1]);
            $this->image->save($this->_new_pic);
        }

        /**
         * Пропорционально обрезает и изменяет размер
         */
        public function cropResize()
        {
            $this->image->adaptiveResize($this->_size[0], $this->_size[1]);
            $this->image->save($this->_new_pic);		
        }
        /**
         * Пропорционально обрезает и изменяет размер
         */
        public function cropResizeUp()
        {
            $this->image->setOptions(array('resizeUp' => FALSE));
            $this->image->resize($this->_size[0], $this->_size[1]);

            $this->image->setOptions(array('resizeUp' => TRUE));
            $this->image->resize($this->_size[0], $this->_size[1]);

            $this->image->save($this->_new_pic);
        }
    }