<?php

class Fw_Controller
{
    public $view;
    public $model;
    
    public function setView($view)
    {
        $this->view = $view;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }
}