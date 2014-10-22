<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_info_model extends CI_Model {

	private $table = 't_swan_baidu_user_info';
	private $database = 'swan';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

	//获取用户信息
	public function user_info($user_id,$col="*") {
		if(empty($user_id))
			return array();

        $swan_slaves = 'slaves'.$user_id%SWAN_DB_COUNT;
        $conn = $this->databases->{$this->database}->$swan_slaves;
        $conn->select($col);
        $conn->from($this->table);
        $conn->where('user_id', $user_id);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
	}

	//根据用户名获取用户信息
	public function get_by_name($username,$count,$col="*") {
		if(empty($username) || !is_numeric($count))
			return array();

        $swan_slaves = 'slaves'.$count%SWAN_DB_COUNT;
        $conn = $this->databases->{$this->database}->$swan_slaves;
        $conn->select($col);
        $conn->from($this->table);
        $conn->where('username', $username);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
	}

	//批量获取用户信息
	public function get_by_ids(array $user_ids,$col="*") {
		if(empty($user_ids))
			return array();

        $conn = $this->databases->{$this->database}->{$this->swan_slaves};
        $conn->select($col);
        $conn->from($this->table);
        $conn->where_in('user_id', $user_ids);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
	}
    //修改用户信息
    public function modify($data,$where = NULL)
    {
        if(empty($data))
            return FALSE;

        $conn = $this->databases->{$this->database}->{$this->swan_master};
        if(!is_null($where))
            $conn->where($where);
        $res = $conn->update($this->table,$data);

        return $res;
    }

    //插入一个用户
    public function add($data)
    {
        if(empty($data))
            return FALSE;
        
        $swan_master = 'master'.$data['user_id']%SWAN_DB_COUNT;
        $conn = $this->databases->{$this->database}->$swan_master;
        $res = $conn->insert($this->table,$data);

        return $res;
    }

    public function user_count($date,$count)
    {
        if(empty($date) || !is_numeric($count))
            return array();

        $swan_slaves = "slaves".$count;
        $conn = $this->databases->{$this->database}->$swan_slaves;
        $sql = "select A.user_id as baidu_id,A.cost,B.plan_count,C.unit_count,D.keyword_count,E.active_user_keyword_count from "
             . "(select E.user_id,F.cost from ".$this->table." as E left join t_swan_baidu_user_stat as F on E.user_id=F.baidu_id and F.date=? group by E.user_id)A "
             . "left join (select user_id,count(1) as plan_count from t_swan_baidu_plan group by user_id)B "
             . "on A.user_id=B.user_id "
             . "left join (select user_id,count(1) as unit_count from t_swan_baidu_unit group by user_id)C "
             . "on B.user_id=C.user_id "
             // 获取所有绑定用户的关键词数目
             . "left join (select user_id,count(1) as keyword_count from t_swan_baidu_keyword group by user_id)D "
             . "on C.user_id=D.user_id "
             // 获取当日活跃用户的关键次数目
             . "left join (
                    select user_id,count(1) as active_user_keyword_count from t_swan_baidu_keyword as E
                    inner join
                    (SELECT DISTINCT `user_id` as baidu_id FROM `t_swan_baidu_keyword` AS A,`t_swan_baidu_keyword_autobid` AS B
                        WHERE A.`keyword_id` = B.`keyword_id` AND DATE(B.`complete_time`)=?
                    )F
                    on F.baidu_id=E.user_id
                    group by user_id
                )E "
             . "on C.user_id=E.user_id ";

        $res = $conn->query($sql,array($date, $date));
        if(!$res)
            return array();
        return $res->result_array();
    }
}

// END user_info_model class

/* End of file user_info_model.php */
/* Location: ./application/models/user_info_model.php */

