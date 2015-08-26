<?php
    class Log
    {        
        public static function add($massage, $code = false)
        {
            $path = APPLICATION_PATH . '/logs/';
            $file_handle = fopen($path . 'error' . ($code === false ? '' : $code) . '.log', 'a');
            fwrite($file_handle, date( "Y.m.d H:i:s", time() ) . ' ' . $massage . "\n");
            fclose($file_handle);
        }
    }
?>
