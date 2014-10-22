<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Temp_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table1 = 't_swan_baidu_keyword';

    public function __Construct()
    {
        parent::__Construct();
    }

    public function get_user($db_index) {
        if (!is_numeric($db_index))
            return array();

        $swan_slaves = 'slaves'.$db_index;
        $conn = $this->databases->{self::$database}->$swan_slaves;
        $conn->select('user_id');
        $conn->from(self::$table1);
        $conn->where('bid_status','2');
        $conn->group_by('user_id');
        $conn->having('count(1) >','100');

        $res = $conn->get();
        if (!$res)
            return array();

        $result = $res->result_array();
        $this->load->helper('array_util_helper');
        return change_data_key($result, 'user_id'); 
    }

    public function get_keyword($db_index,$user_id)
    {
         if (!is_numeric($db_index) || empty($user_id))
            return FALSE;

        $swan_master = 'master'.$db_index;
        $conn = $this->databases->{self::$database}->$swan_master;

        $sql = 'select t_swan_baidu_keyword.keyword_id '
             . 'from t_swan_baidu_keyword '
             . "left join (select * from t_swan_baidu_keyword_stat where date='2014-05-13')A on t_swan_baidu_keyword.keyword_id = A.keyword_id "
             . "where user_id = $user_id and bid_status = 2 "
             . "order by cost desc "
             . "limit 0,100 ";

        $res = $conn->query($sql);
        if (!$res)
            return array();

        $result = $res->result_array();
        $this->load->helper('array_util_helper');
        return change_data_key($result, 'keyword_id'); 
    }

    public function update_status($db_index,array $keyword_ids,$user_id)
    {
        if (!is_numeric($db_index) || empty($keyword_ids) || empty($user_id))
            return FALSE;

        $swan_master = 'master'.$db_index;
        $conn = $this->databases->{self::$database}->$swan_master;
    
        $conn->where('user_id',$user_id);
        $conn->where('bid_status','2');
        $conn->where_not_in('keyword_id',$keyword_ids);
        $res = $conn->update(self::$table1,array('bid_status'=>'3'));

        return $res;
    }

    public function get_day_user()
    {
        $conn = $this->databases->phoenix->slaves;
        $sql = "select username,name from t_enterprise_user as A inner join t_enterprise_user_permission as B on A.userid=B.userid and B.product=2 where B.status_product=0 and A.last_login='0000-00-00 00:00:00' and A.ctime < '2014-05-20' ";
        $res = $conn->query($sql);

        return $res->result_array();
    }
}

