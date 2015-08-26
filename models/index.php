<?php

class indexModel extends Fw_Model_Base
{
    public function getData()
    {
        $lang = C::$lang;
        $this->sql->setQuery("
            SELECT 
                p.*,
                pl.*,
                GROUP_CONCAT(DISTINCT pp.pp_src ORDER BY pp.pp_ord SEPARATOR ',') images
            FROM projects AS p
            LEFT JOIN projects_lang AS pl ON p.p_id = pl.pl_p_id
            LEFT JOIN projects_photos AS pp ON pp.pp_p_id = p.p_id
            WHERE 
                pl.pl_lang = '{$lang}'
                AND p.p_current < p.p_need
                AND p.p_state = 1
            GROUP BY p.p_id
            ORDER BY p.p_date_create DESC
            LIMIT 4
        ");
        $this->sql->sendQuery( true, (3600 * 24), array('index', 'content') );
        return $this->sql->fetchObjects();
    }
}
