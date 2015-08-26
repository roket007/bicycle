<?php

class Fw_Model_Base
{
    public $sql;

    public function __construct($model)
    {
        $this->sql = $model;
    }
}