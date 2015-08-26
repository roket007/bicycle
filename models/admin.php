<?php

class adminModel extends Fw_Model_Base
{
    public function getStaticPages()
    {
        $this->sql->setQuery("
            SELECT *
            FROM static_page AS sp
            LEFT JOIN meta_data_cotrollers AS mdc ON mdc.mdc_id = sp.sp_mdc_id
            LEFT JOIN meta_data_action AS mda ON mda.mda_id = sp.sp_mda_id AND mdc.mdc_id = mda.mda_mdc_id
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getProjectUsers()
    {
        $this->sql->setQuery("SELECT u_id, u_login, u_name, u_surname FROM users ORDER BY u_id DESC");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function addPay($data)
    {
        $sql_name_field = array();
        $sql_value_field = array();

        foreach($data as $name_field => $value_field) {
            $sql_name_field[] = "`" . $name_field . "`";
            $sql_value_field[] = "'" . $value_field . "'";
        }
        
        $this->sql->setQuery("INSERT INTO `projects_pays` (" . implode(",", $sql_name_field) . ") VALUES (" . implode(",", $sql_value_field) . ")");
        $result = $this->sql->sendQuery();
        $this->sql->setQuery("UPDATE projects AS p SET p.p_current = (p.p_current + " . $data['pp_sum'] . ") WHERE p.p_id = " . $data['pp_p_id']);
        $result = $this->sql->sendQuery();
        $this->sql->setQuery("SELECT MAX(pp_id) AS pp_id FROM projects_pays");
        $this->sql->sendQuery();
        $result = $this->sql->fetchRow(true);
        return ( !empty($result) ? $result['pp_id'] : false );
    }
    
    public function dropPay($id)
    {
        $this->sql->setQuery("SELECT * FROM projects_pays WHERE pp_id = " . $id);
        $this->sql->sendQuery();
        $result = $this->sql->fetchRow(true);
        $this->sql->setQuery("DELETE FROM projects_pays WHERE pp_id = " . $id);
        $this->sql->sendQuery();
        $this->sql->setQuery("UPDATE projects AS p SET p.p_current = (p.p_current - " . $result['pp_sum'] . ") WHERE p.p_id = " . $result['pp_p_id']);
        $result = $this->sql->sendQuery();
        return $result;
    }

    public function updateMainProjectData($id, $data)
    {
        $sql_update = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_update[] = " `" . $name_field . "` = '" . $value_field . "' ";
        }
        
        $this->sql->setQuery("UPDATE projects SET " . implode(",", $sql_update) . " WHERE p_id = " . $id);
        return $this->sql->sendQuery();
    }
    
    public function updateProjectLangData($id, $lang, $data)
    {
        $sql_update = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_update[] = " `" . $name_field . "` = '" . $value_field . "' ";
        }
        
        $this->sql->setQuery("UPDATE projects_lang SET " . implode(",", $sql_update) . " WHERE pl_p_id = {$id} AND pl_lang = '{$lang}'");
        return $this->sql->sendQuery();
    }
    
    public function saveProjectLangData($data)
    {
        $sql_name_field = array();
        $sql_value_field = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_name_field[] = "`" . $name_field . "`";
            $sql_value_field[] = "'" . $value_field . "'";
        }
        
        $this->sql->setQuery("INSERT INTO projects_lang (" . implode(",", $sql_name_field) . ") VALUES (" . implode(",", $sql_value_field) . ")");
        return $this->sql->sendQuery();
    }
    
    public function switchProjectPhoto($data)
    {
        $result = false;
        
        foreach($data as $value) {
            $this->sql->setQuery("UPDATE projects_photos SET pp_ord = " . $value['pp_ord'] . " WHERE pp_id = " . $value['pp_id']);
            $result = $this->sql->sendQuery();
        }
        
        return $result;
    }
    
    public function addProjectPhoto($id, $src)
    {
        $this->sql->setQuery("
            INSERT INTO projects_photos (pp_p_id, pp_ord, pp_src) 
            VALUES(
                {$id}, 
                IFNULL( (SELECT (MAX(pp.pp_ord) + 1) AS max_ord FROM projects_photos AS pp WHERE pp.pp_p_id = {$id}), 1), 
                '{$src}'
            )
        ");
        return $this->sql->sendQuery();
    }
    
    public function dropProjectPhoto($id)
    {
        $this->sql->setQuery("DELETE FROM projects_photos WHERE pp_id = {$id}");
        return $this->sql->sendQuery();
    }
    
    public function getProjectPhoto($id)
    {
        $this->sql->setQuery("SELECT * FROM projects_photos WHERE pp_p_id = {$id} ORDER BY pp_ord ASC");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getProjects()
    {
        $this->sql->setQuery("
            SELECT 
                p.*,
                pl.pl_title AS ru_title,
                pl1.pl_title AS ua_title,
                pl.pl_alias AS ru_alias,
                pl1.pl_alias AS ua_alias,
                pl.pl_keywords AS ru_keywords,
                pl1.pl_keywords AS ua_keywords,
                pl.pl_description AS ru_description,
                pl1.pl_description AS ua_description,
                pl.pl_text AS ru_text,
                pl1.pl_text AS ua_text,
                GROUP_CONCAT(DISTINCT CONCAT_WS(':', pp.pp_ord, pp.pp_src) ORDER BY pp.pp_ord SEPARATOR ',') AS images
            FROM projects AS p
            LEFT JOIN projects_lang AS pl ON pl.pl_p_id = p.p_id  AND pl.pl_lang = 'ru'
            LEFT JOIN projects_lang AS pl1 ON pl1.pl_p_id = p.p_id  AND pl1.pl_lang = 'ua'
            LEFT JOIN projects_photos AS pp ON pp.pp_p_id = p.p_id
            GROUP BY p.p_id
            ORDER BY p.p_date_create DESC
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getUserPay($id)
    {
        $this->sql->setQuery("SELECT * FROM projects_pays WHERE pp_u_id = " . $id);
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getProjectPays($id)
    {
        $this->sql->setQuery("SELECT * FROM projects_pays LEFT JOIN users ON pp_u_id = u_id WHERE pp_p_id = " . $id);
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function saveMainProjectData($data)
    {
        $sql_name_field = array();
        $sql_value_field = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_name_field[] = "`" . $name_field . "`";
            $sql_value_field[] = "'" . $value_field . "'";
        }
        
        $this->sql->setQuery("INSERT INTO projects (" . implode(",", $sql_name_field) . ") VALUES (" . implode(",", $sql_value_field) . ")");
        
        if( $this->sql->sendQuery() ) {
            $this->sql->setQuery("SELECT max(p.p_id) AS p_id FROM projects AS p");
            $this->sql->sendQuery();
            $result = $this->sql->fetchRow();
            return $result['p_id'];
        } else {
            return false;
        }
    }
    
    public function getUsers()
    {
        $this->sql->setQuery("
            SELECT *
            FROM users AS u
            LEFT JOIN admin_users AS au ON au.au_u_id = u.u_id
            ORDER BY u_id DESC
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function makeAdmin($id, $admin)
    {
        if($admin) {
            $this->sql->setQuery("INSERT INTO admin_users (`au_u_id`) VALUES ({$id})");
        } else {
            $this->sql->setQuery("DELETE FROM admin_users WHERE au_u_id = {$id}");
        }
        
        return $this->sql->sendQuery();
    }
    
    public function updateUser($id, $data)
    {
        $sql_update = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_update[] = " `" . $name_field . "` = '" . $value_field . "' ";
        }
        
        $this->sql->setQuery("UPDATE users SET " . implode(",", $sql_update) . " WHERE u_id = " . $id);
//        echo $this->sql->_query;
//        die;
        return $this->sql->sendQuery();
    }
    
    public function deleteUser($id)
    {
        $this->sql->setQuery("DELETE FROM users WHERE u_id = " . $id);
        return $this->sql->sendQuery();
    }
    
    public function testUser($id)
    {
        $this->sql->setQuery("SELECT pp_id FROM projects_pays WHERE pp_u_id = " . $id);
        $this->sql->sendQuery();
        $data = $this->sql->fetchRow(true);
        return ( isset($data['pp_id']) && is_numeric($data['pp_id']) ? true : false );
    }
    
    public function getSystemControllers()
    {
        $this->sql->setQuery("SELECT * FROM meta_data_cotrollers");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getControllerActions($id)
    {
        $this->sql->setQuery("SELECT * FROM meta_data_action WHERE mda_mdc_id = {$id}");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function saveStaticPage($data)
    {
        $sql_name_field = array();
        $sql_value_field = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_name_field[] = "`" . $name_field . "`";
            $sql_value_field[] = "'" . $value_field . "'";
        }
        
        $this->sql->setQuery("INSERT INTO static_page (" . implode(",", $sql_name_field) . ") VALUES (" . implode(",", $sql_value_field) . ")");
        
        if( $this->sql->sendQuery() ) {
            $this->sql->setQuery("SELECT max(sp.sp_id) AS sp_id FROM static_page AS sp");
            $this->sql->sendQuery();
            $result = $this->sql->fetchRow();
            return $result['sp_id'];
        } else {
            return false;
        }
    }
    
    public function updateStaticPage($id, $data)
    {
        $sql_update = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_update[] = " `" . $name_field . "` = '" . $value_field . "' ";
        }
        
        $this->sql->setQuery("UPDATE static_page SET " . implode(",", $sql_update) . " WHERE sp_id = " . $id);
        return $this->sql->sendQuery();
    }
    
    public function updateMetaData($id, $data)
    {
        $sql_update = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_update[] = " `" . $name_field . "` = '" . $value_field . "' ";
        }
        
        $this->sql->setQuery("UPDATE meta_data SET " . implode(",", $sql_update) . " WHERE md_id = " . $id);
        return $this->sql->sendQuery();
    }
    
    public function saveMetaData($data)
    {
        $sql_name_field = array();
        $sql_value_field = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_name_field[] = "`" . $name_field . "`";
            $sql_value_field[] = "'" . $value_field . "'";
        }
        
        $this->sql->setQuery("INSERT INTO meta_data (" . implode(",", $sql_name_field) . ") VALUES (" . implode(",", $sql_value_field) . ")");
        
        if( $this->sql->sendQuery() ) {
            $this->sql->setQuery("SELECT max(mb.mb_id) AS mb_id FROM meta_data AS mb");
            $this->sql->sendQuery();
            $result = $this->sql->fetchRow();
            return $result['mb_id'];
        } else {
            return false;
        }
    }
    
    public function loadMenuData()
    {
        $this->sql->setQuery("SELECT * FROM menu ORDER BY m_id ASC");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function saveMenuData($data, $without_id = true)
    {
        $result;
        
        foreach($data as $id => $data) {
            $sql_parts_field = array();
            $sql_parts_values = array();
            
            if($without_id) {
                $sql_parts_field[] = '`m_id`';
                $sql_parts_values[] = $id;
            }
            
            foreach($data as $field_name => $field_value) {
                $sql_parts_field[] = "`{$field_name}`";
                $sql_parts_values[] = "'{$field_value}'";
            }
            
            $this->sql->setQuery("REPLACE INTO `menu` (" . implode(",", $sql_parts_field) . ") VALUES (" . implode(",", $sql_parts_values) . ")");
            $result = $this->sql->sendQuery();
            
            if(!$result) {
                return false;
            }
        }
        
        return true;
    }
    
    public function switchMenuPoint($from, $to)
    {
        $this->sql->setQuery('SELECT * FROM menu WHERE m_id = ' . $from);
        $this->sql->sendQuery();
        $from = $this->sql->fetchRow(true);
        $this->sql->setQuery('SELECT * FROM menu WHERE m_id = ' . $to);
        $this->sql->sendQuery();
        $to = $this->sql->fetchRow(true);
        $from_id = $from['m_id'];
        $from['m_id'] = $to['m_id'];
        $to['m_id'] = $from_id;
        return $this->saveMenuData( array($from, $to), false );
    }
    
    public function deleteMenuPoint($id)
    {
        $this->sql->setQuery("DELETE FROM `menu` WHERE `m_id` = {$id}");
        return $this->sql->sendQuery();
    }
    
    public function saveBreadCrumb($data)
    {
        $sql_name_field = array();
        $sql_value_field = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_name_field[] = "`" . $name_field . "`";
            $sql_value_field[] = "'" . $value_field . "'";
        }
        
        $this->sql->setQuery("INSERT INTO meta_breadcrumbs (" . implode(",", $sql_name_field) . ") VALUES (" . implode(",", $sql_value_field) . ")");
        
        if( $this->sql->sendQuery() ) {
            $this->sql->setQuery("SELECT max(mb.mb_id) AS mb_id FROM meta_breadcrumbs AS mb");
            $this->sql->sendQuery();
            $result = $this->sql->fetchRow();
            return $result['mb_id'];
        } else {
            return false;
        }
    }
    
    public function updateBreadCrumb($id, $data)
    {
        $sql_update = array();
        
        foreach($data as $name_field => $value_field) {
            $sql_update[] = " `" . $name_field . "` = '" . $value_field . "' ";
        }
        
        $this->sql->setQuery("UPDATE meta_breadcrumbs SET " . implode(",", $sql_update) . " WHERE mb_id = " . $id);
        return $this->sql->sendQuery();
    }
    
    public function getAllSystemPaths()
    {
        $this->sql->setQuery("
            SELECT
                mdc.*,
                GROUP_CONCAT( DISTINCT CONCAT_WS(':', mda.mda_id, mda.mda_action) ) AS mdc_actions
            FROM meta_data_cotrollers AS mdc
            LEFT JOIN meta_data_action AS mda ON mda.mda_mdc_id = mdc.mdc_id
            GROUP BY mdc.mdc_id
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getMetaDataTypes()
    {
        $this->sql->setQuery("
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_NAME = 'meta_data'
                AND TABLE_SCHEMA = '" . C::db_name . "'
                AND COLUMN_NAME = 'md_type'
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObject();
    }
    
    public function getMetaData()
    {
        $this->sql->setQuery("
            SELECT * FROM meta_data AS md
            LEFT JOIN meta_data_action AS mda ON mda.mda_id = md.md_mda_id
            LEFT JOIN meta_data_cotrollers AS mdc ON mdc.mdc_id = md.md_mdc_id AND mda.mda_mdc_id = mdc.mdc_id
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function getBreadCrumbs()
    {
        $this->sql->setQuery("
            SELECT
                *
            FROM meta_breadcrumbs AS mb
            LEFT JOIN meta_data_action AS mda ON mda.mda_id = mb.mb_mda_id
            LEFT JOIN meta_data_cotrollers AS mdc ON mdc.mdc_id = mb.mb_mdc_id AND mda.mda_mdc_id = mdc.mdc_id
        ");
        $this->sql->sendQuery();
        return $this->sql->fetchObjects();
    }
    
    public function saveNewAction($id, $path, $name)
    {
        $this->sql->setQuery("INSERT INTO meta_data_action (mda_action, mda_mdc_id, mda_name) VALUES ('{$path}', {$id}, '{$name}')");
        
        if( $this->sql->sendQuery() ) {
            $this->sql->setQuery("SELECT max(mda.mda_id) AS mda_id FROM meta_data_action AS mda");
            $this->sql->sendQuery();
            $result = $this->sql->fetchRow();
            return $result['mda_id'];
        } else {
            return false;
        }
    }
    
    public function saveNewController($path)
    {
        $this->sql->setQuery("INSERT INTO meta_data_cotrollers (mdc_controller) VALUES ('{$path}')");
        
        if( $this->sql->sendQuery() ) {
            $this->sql->setQuery("SELECT max(mdc.mdc_id) AS mdc_id FROM meta_data_cotrollers AS mdc");
            $this->sql->sendQuery();
            $result = $this->sql->fetchRow();
            return $result['mdc_id'];
        } else {
            return false;
        }
    }
    
    public function updateAction($action_id, $path, $name)
    {
        $this->sql->setQuery("UPDATE meta_data_action SET mda_action = '{$path}', mda_name = '{$name}' WHERE mda_id = {$action_id}");
        return $this->sql->sendQuery();
    }
    
    public function updateController($controller_id, $path)
    {
        $this->sql->setQuery("UPDATE meta_data_cotrollers SET mdc_controller = '{$path}' WHERE mdc_id = {$controller_id}");
        return $this->sql->sendQuery();
    }
}
