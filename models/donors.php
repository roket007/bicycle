<?php

class donorsModel extends Fw_Model_Base
{
    public function getDonor($id)
    {
        $cache = true;
        
        if( Fw_Auth::isValid() ) {
            $cache = ( Fw_Auth::get('id') == $id ? false : true);
        }
        
        $lang = Fw_Request::get('language');
        $this->sql->setQuery("
            SELECT
                u.u_id,
                u.u_name,
                u.u_surname,
                u.u_date_reg,
                u.u_information,
                u.u_address,
                u.u_img,
                SUM(pp.pp_sum) AS money_sum,
                COUNT(DISTINCT pp.pp_p_id) AS project_support
            FROM users AS u
            LEFT JOIN projects_pays AS pp ON pp.pp_u_id = u.u_id
            WHERE u.u_id = {$id} AND u.u_activate = 1
            GROUP BY u.u_id
        ");

        $this->sql->sendQuery( $cache, 3600, array('donors', 'donor', $lang) );
        return $this->sql->fetchRow();
    }
    
    public function getProjectsByDonor($id)
    {
        $cache = true;
        
        if( Fw_Auth::isValid() ) {
            $cache = ( Fw_Auth::get('id') == $id ? false : true);
        }
        
        $lang = Fw_Request::get('language');
        $this->sql->setQuery("
            SELECT
                p.p_id,
                p.p_date_create,
                p.p_need,
                p.p_current,
                p.p_count_vote,
                pl.pl_description,
                pl.pl_text,
                pl.pl_title,
                pl.pl_alias,
                pl.pl_lang,
                GROUP_CONCAT(DISTINCT pph.pp_src ORDER BY pph.pp_ord ASC SEPARATOR ',') AS images
            FROM projects_pays AS pp
            LEFT JOIN projects_lang AS pl ON pl.pl_p_id = pp.pp_p_id AND pl.pl_lang = '{$lang}'
            LEFT JOIN projects AS p ON p.p_id = pl.pl_p_id
            LEFT JOIN projects_photos AS pph ON pph.pp_p_id = pl.pl_p_id
            WHERE pp.pp_u_id = {$id} AND p.p_state = 1
            GROUP BY pp.pp_p_id
            ORDER BY pp.pp_data DESC
        ");
            
        $this->sql->sendQuery( $cache, 3600, array('donors', 'donor', $lang) );
        return $this->sql->fetchObjects();
    }

    public function getOrderDonors($order_by = 'new', $offset = 0, $limit = 10)
    {
        if($order_by == 'new') {
            $order_query_part = 'date_reg';
        } elseif($order_by == 'project') {
            $order_query_part = 'count_pays';
        } elseif($order_by == 'count') {
            $order_query_part = 'money_sum';
        }

        $lang = Fw_Request::get('language', 'ua');
        $query = "
            SELECT
                u.u_id AS id,
                u.u_name AS name,
                u.u_surname AS surname,
                u.u_date_reg AS date_reg,
                u.u_img AS img,
                SUM(pp.pp_sum) AS money_sum,
                COUNT(DISTINCT pp.pp_p_id) AS count_pays,
                ( SELECT MAX(pp.pp_data) ) AS last_pay_date,
                GROUP_CONCAT(DISTINCT pp.pp_sum ORDER BY pp.pp_data DESC) AS last_pay_sum
            FROM users AS u
            LEFT JOIN projects_pays AS pp ON pp.pp_u_id = u.u_id
            WHERE 
                u.u_activate = 1
            GROUP BY u.u_id
            HAVING money_sum IS NOT NULL
            ORDER BY {$order_query_part} DESC
            LIMIT {$offset}, {$limit}
        ";

        $this->sql->setQuery($query);
        $this->sql->sendQuery(true, 600, array('donors', 'list'));
        return $this->sql->fetchObjects();
    }
}
