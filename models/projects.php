<?php

class projectsModel extends Fw_Model_Base
{
    public function getProject($id, $only_ended = false)
    {
        $lang = Fw_Request::get('language');
        $sql_select = $sql_left_join = '';
        $cache = true;
        
        if( Fw_Auth::isValid() ) {
            $sql_select = ",  pv.pv_id";
            $sql_left_join = " LEFT JOIN projects_votes AS pv ON pv.pv_p_id = p.p_id AND pv.pv_u = " . Fw_Auth::get('id');
            $cache = false;
        }
        
        $this->sql->setQuery("             
            SELECT
                p.*,
                pl.*,
                (SELECT pl2.pl_alias FROM projects_lang AS pl2 WHERE pl2.pl_p_id = {$id} AND pl2.pl_lang = 'ua') AS ua_alias,
                (SELECT pl2.pl_alias FROM projects_lang AS pl2 WHERE pl2.pl_p_id = {$id} AND pl2.pl_lang = 'ru') AS ru_alias,
                GROUP_CONCAT(DISTINCT pp.pp_src ORDER BY pp.pp_ord SEPARATOR ',') AS images
                " . $sql_select . "
            FROM projects AS p
            LEFT JOIN projects_lang AS pl ON p.p_id = pl.pl_p_id AND pl.pl_lang = '{$lang}'
            LEFT JOIN projects_photos AS pp ON pp.pp_p_id = p.p_id
            " . $sql_left_join . "
            WHERE 
                p.p_state = 1 
                AND p.p_id = {$id}
            LIMIT 1
        ");

        $this->sql->sendQuery($cache, 1200, array('project', $lang) );
        return $this->sql->fetchRow();
    }
    
    public function updateVoteByProject($id)
    {
        $this->sql->setQuery("
            UPDATE projects AS p 
            SET
                p.p_count_vote = (SELECT COUNT(pv1.pv_id) FROM projects_votes AS pv1 WHERE pv1.pv_p_id = {$id}), 
                p.p_avg_vote = (SELECT AVG(pv.pv_vote) FROM projects_votes AS pv WHERE pv.pv_p_id = {$id}) 
            WHERE p.p_id = {$id}
        ");
        $this->sql->sendQuery();
    }
    
    public function voteByProject($id, $user_id)
    {
        $this->sql->setQuery("INSERT INTO projects_votes (pv_p_id, pv_u) VALUES ({$id}, {$user_id})");
        return $this->sql->sendQuery();
    }

    public function getOrderProjects($order_by = 'new', $offset = 0, $limit = 10, $only_ended = false)
    {
        if($order_by == 'new') {
            $order_query_part = 'p.p_date_create';
        } elseif($order_by == 'vote') {
            $order_query_part = 'p.p_count_vote';
        } elseif($order_by == 'need') {
            $order_query_part = '( (p.p_current / p.p_need) * 100 )';
        }

        $lang = Fw_Request::get('language', 'ua');
        $query = "
            SELECT 
                p.p_id AS id,
                p.p_date_create AS date_start,
                p.p_need AS money_need,
                p.p_current AS money_now,
                p.p_count_vote AS count_vote,
                p.p_avg_vote AS avg_vote,
                pl.pl_title AS title,
                pl.pl_text AS text,
                pl.pl_description AS description,
                pl.pl_alias AS alias,
                GROUP_CONCAT(DISTINCT pp.pp_src ORDER BY pp.pp_ord SEPARATOR ',') AS images
            FROM projects AS p
            LEFT JOIN projects_lang AS pl ON pl.pl_p_id = p.p_id AND pl.pl_lang = '{$lang}'
            LEFT JOIN projects_photos AS pp ON pp.pp_p_id = p.p_id
            WHERE 
                p.p_state = 1
                AND p.p_current " . ($only_ended ? " >= " : " < ") . " p.p_need
            GROUP BY p.p_id
            ORDER BY {$order_query_part} DESC
            LIMIT {$offset}, {$limit}
        ";

        $this->sql->setQuery($query);
        $this->sql->sendQuery(true, 1200, array('done', 'list', $lang));
        return $this->sql->fetchObjects();
    }
    
    public function getDonorsByProject($id, $offset = 0, $limit = 10)
    {
        $this->sql->setQuery("
            SELECT 
                pp.*,
                u.u_name,
                u.u_surname,
                u.u_img
            FROM projects_pays AS pp 
            LEFT JOIN users AS u ON u.u_id = pp.pp_u_id
            WHERE pp.pp_p_id = {$id}
            ORDER BY pp.pp_data DESC
            LIMIT {$offset}, {$limit}
        ");
        $this->sql->sendQuery( true, 1200, array('project', 'donors') );
        return $this->sql->fetchObjects();
    }
}
