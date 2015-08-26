<?php

class menuBlock extends Fw_Blocks
{        
    public function render()
    {
        $this->data = $this->getMenuItems();
        $this->current = Fw_Request::get('controller', 'index');
        parent::render(__CLASS__);
    }

    public function getMenuItems()
    {
        $model = Fw_Model::getInstance();
        $model->setQuery("
            SELECT 
                m_id, 
                m_name_" . Fw_Request::get('language') . " AS m_name,
                m_url
            FROM menu 
            WHERE m_state = 1");
        $model->sendQuery( true, (3600 * 24), array('blocks', 'menu') );
        return $model->fetchObjects();
    }
}
