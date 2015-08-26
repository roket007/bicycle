<?php

class sliderBlock extends Fw_Blocks
{        
    public function render()
    {
        $model = Fw_Model::getInstance();
        $this->data = $model->getModel('index')->getData();
        parent::render(__CLASS__);
    }
}