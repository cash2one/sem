<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_stat_model extends CI_Model {

	private $database = 'swan';
	private $table = 't_swan_baidu_user_stat';


    public function __Construct()
    {
        parent::__Construct();
        $id = Auth_filter::current_sem_id();
        if(!empty($id)) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

	//获取时间段内的消费均值
	public function avg_consume($user_id,$s_date,$e_date) {
		if(empty($user_id) || empty($s_date) || empty($e_date))
			return array();

        $where = array(
                "baidu_id"=>$user_id,
                "date >= "=>$s_date,
                "date <= "=>$e_date
            );
        $conn = $this->databases->{$this->database}->{$this->swan_slaves};
        $conn->select_avg('cost');
        $conn->from($this->table);
        $conn->where($where);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
	}

    //用户总体信息
    public function summary($user_id,$s_date,$e_date)
    {
        if(empty($user_id) || empty($s_date) || empty($e_date))
            return array();
        
        $where = array(
                "baidu_id"=>$user_id,
                "date >= "=>$s_date,
                "date <= "=>$e_date
            );
        $conn = $this->databases->{$this->database}->{$this->swan_slaves};
        $conn->select("sum(impression) as impression,sum(click) as click,sum(cost) as cost");
        $conn->from($this->table);
        $conn->where($where);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

    //用户详细信息
    public function detail($user_id,$s_date,$e_date)
    {
        if(empty($user_id) || empty($s_date) || empty($e_date))
            return array();
        
        $where = array(
                "baidu_id"=>$user_id,
                "date >= "=>$s_date,
                "date <= "=>$e_date
            );
        $conn = $this->databases->{$this->database}->{$this->swan_slaves};
        $conn->select("date,impression,click,cost");
        $conn->from($this->table);
        $conn->where($where);
        $conn->group_by('date');
        $conn->order_by('date','asc');
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

    //获取用户日平均消费数据
    public function get_consume($s_date,$e_date,$threshold,$count)
    {
        if(empty($s_date) || empty($e_date) || empty($threshold) || !is_numeric($count))
            return array();
        
        $swan_slaves = 'slaves'.$count;
        $conn = $this->databases->{$this->database}->$swan_slaves;
        $conn->select("baidu_id,avg(cost) as avg_consume");
        $conn->from($this->table);
        $conn->where('date >=',$s_date);
        $conn->where('date <=',$e_date);
        $conn->group_by('baidu_id');
        $conn->having('avg_consume < ',$threshold);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

    //获取竞价的信息，韩刘的临时需求
    public function bid_info($count)
    {
        if(!is_numeric($count))
            return array();

        $swan_slaves = "slaves".$count;
        $conn = $this->databases->{$this->database}->$swan_slaves;

               //用户日均消费
        $sql = "select A.baidu_id,A.user_avg_cost,B.biding_count,C.bided_count,D.biding_avg_cost,E.bided_avg_cost from "
             . "("
             . "    select A.user_id as baidu_id,avg(B.cost) as user_avg_cost from t_swan_baidu_user_info as A left join ".$this->table." as B on A.user_id=B.baidu_id group by A.user_id "
             . ")A "
               //竞价中关键词数
             . "left join "
             . "("
             . "    select user_id as baidu_id,count(keyword_id) as biding_count from t_swan_baidu_keyword where bid_status = 2 group by user_id "
             . ")B on A.baidu_id=B.baidu_id "
               //竞价暂停关键词数
             . "left join "
             . "("
             . "    select user_id as baidu_id,count(keyword_id) as bided_count from t_swan_baidu_keyword where bid_status = 3 group by user_id "
             . ")C on A.baidu_id=C.baidu_id "
             //竞价中关键词日均消费
             . "left join "
             . "("
             . "    select baidu_id,avg(bid_cost) as biding_avg_cost from (select B.baidu_id,B.date,sum(cost) as bid_cost from t_swan_baidu_keyword as A inner join t_swan_baidu_keyword_stat as B on A.keyword_id=B.keyword_id where A.bid_status =2 group by B.baidu_id,B.date)A group by baidu_id "
             . ")D on A.baidu_id=D.baidu_id "
             //竞价中关键词日均消费
             . "left join "
             . "("
             . "    select baidu_id,avg(bid_cost) as bided_avg_cost from (select B.baidu_id,B.date,sum(cost) as bid_cost from t_swan_baidu_keyword as A inner join t_swan_baidu_keyword_stat as B on A.keyword_id=B.keyword_id where A.bid_status =3 group by B.baidu_id,B.date)A group by baidu_id "
             . ")E on A.baidu_id=E.baidu_id ";

        $res = $conn->query($sql);
        if(!$res)
            return array();
        return $res->result_array();
    }
}

