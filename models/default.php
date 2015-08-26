<?php

class defaultModel extends Fw_Model_Base
{
    public function getMetaData($controller = null, $action = null)
    {
        $lang = Fw_Request::get('language', 'ua');
        $this->sql->setQuery("
            SELECT 
                md.md_type AS m_type,
                md.md_data AS m_data
            FROM meta_data AS md
            LEFT JOIN meta_data_cotrollers AS mdc ON mdc.mdc_id = md.md_mdc_id 
            LEFT JOIN meta_data_action AS mda ON mda.mda_id = md.md_mda_id AND mda.mda_mdc_id = mdc.mdc_id 
            WHERE
                md.md_lang = '{$lang}'
                AND mdc.mdc_controller = '{$controller}'
                AND mda.mda_action = '{$action}'
        ");
        $this->sql->sendQuery(true, (24 * 3600), array('default', 'metadata'));
        return $this->sql->fetchObjects();
    }
    
    public function loadStaticPage($controller = null, $action = null) {
        $lang = Fw_Request::get('language', 'ua');
        $this->sql->setQuery("
            SELECT 
                sp.sp_body
            FROM static_page AS sp
            LEFT JOIN meta_data_cotrollers AS mdc ON mdc.mdc_id = sp.sp_mdc_id 
            LEFT JOIN meta_data_action AS mda ON mda.mda_id = sp.sp_mda_id AND mda.mda_mdc_id = mdc.mdc_id 
            WHERE
                sp.sp_lang = '{$lang}'
                AND mdc.mdc_controller = '{$controller}'
                AND mda.mda_action = '{$action}'
            LIMIT 1
        ");
        $this->sql->sendQuery(true, (24 * 3600), array('default', 'custom_page'));
        $result = $this->sql->fetchObjects();
        return ( isset($result[0]) ? $result[0]->sp_body : NULL );
    }
}
