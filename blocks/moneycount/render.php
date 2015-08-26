<?php
    class moneycountBlock extends Fw_Blocks
    {        
        public function render()
        {
            $this->user_count = $this->getUserCount();
            $this->money_count = $this->getMoneyCount();
            $this->current = Fw_Request::get('controller', 'index');
            parent::render(__CLASS__);
        }
        
        public function getUserCount()
        {
            $model = Fw_Model::getInstance();
            $model->setQuery("SELECT COUNT(u.u_id) AS user_counts FROM users AS u");
            $model->sendQuery( true, 600, array('index', 'blocks', 'counter') );
            $result = $model->fetchObject();
            return $result->user_counts;
        }
        
        public function getMoneyCount()
        {
            $model = Fw_Model::getInstance();
            $model->setQuery("SELECT SUM(p.p_current) AS money_count FROM projects AS p");
            $model->sendQuery( true, 600, array('index', 'blocks', 'counter') );
            $result = $model->fetchObject();
            return $result->money_count;
        }
    }
