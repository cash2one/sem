<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Competitor_stat_model extends CI_Model {

    protected static $database = 'swan_extra';
    protected static $table = 't_swan_baidu_competitor_stat';

    public function __Construct()
    {
        parent::__Construct();
    }


    //获取跟踪对手关键词列表
    public function rank_data($ids)
    {
		if (empty($ids)) {
			return array();
		}
		// 先排序
		$conn = $this->databases->{static::$database}->slaves;
		$conn->from(static::$table);
        $conn->where_in('track_id',$ids);
        $conn->where('type','1');
		$conn->order_by('time desc');
		$table = $conn->get_compile_select();
		// 再分组
        $conn->select('t.track_id,t.rank');
		$conn->from("({$table}) t");
		$conn->group_by('track_id');
		$query = $conn->get();

		$result = $query->result_array();
		$this->load->helper('array_util');
		$result = change_data_key($result, 'track_id');
		return $result;
    }

    public function get_rank($ids,$condition=array())
    {
        if(empty($ids))
            return array();

        $conn = $this->databases->{static::$database}->slaves;
        $conn->select('track_id as id,time,rank');
        $conn->from(self::$table);
        $conn->where_in('track_id',$ids);
        if(!empty($condition))
            $conn->where($condition);
        $conn->order_by('track_id asc,time asc');
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }
}

