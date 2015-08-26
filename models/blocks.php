<?php

class blocksModel extends Fw_Model_Base
{
    public function getBreadCrumbs($controller, $action)
    {
        $lang = C::$lang;
        $this->sql->setQuery("
            SELECT 
                mb.mb_data
            FROM meta_breadcrumbs AS mb
            LEFT JOIN meta_data_cotrollers AS mdc ON mdc.mdc_id = mb.mb_mdc_id 
            LEFT JOIN meta_data_action AS mda ON mda.mda_id = mb.mb_mda_id AND mda.mda_mdc_id = mdc.mdc_id 
            WHERE 
                mb.mb_lang = '{$lang}' 
                AND mdc.mdc_controller = '{$controller}' 
                AND mda.mda_action = '{$action}'
        ");
        $this->sql->sendQuery( true, (3600 * 24), array('blocks', 'breadcrubms') );
        $result = $this->sql->fetchObjects();
        return ( isset($result[0])? $result[0]->mb_data : '' ) ;
    }
}
