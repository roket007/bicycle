<?php

class searchModel extends Fw_Model_Base
{
    public function searchByQuery($query, $offset = 0, $limit = 10)
    {
        $lang = C::$lang;
        $this->sql->setQuery("
            SELECT *
            FROM projects_lang AS pl
            LEFT JOIN projects AS p ON p.p_id = pl.pl_p_id
            WHERE 
                MATCH (pl_title, pl_text) AGAINST ('{$query}')
                AND p.p_state = 1
            GROUP BY pl_p_id
            ORDER BY p.p_date_create DESC
            LIMIT {$offset}, {$limit}
        ");
        $this->sql->sendQuery(false);
        return $this->sql->fetchObjects();
    }
}
