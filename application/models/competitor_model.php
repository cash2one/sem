<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Competitor_model extends CI_Model {

    protected static $database = 'swan_extra';
    protected static $table = 't_swan_baidu_competitor';

    public function __Construct()
    {
        parent::__Construct();
    }


    //获取跟踪对手关键词列表
    public function get($ids)
    {
		if (empty($ids)) {
			return array();
		}
        $conn = $this->databases->{self::$database}->slaves;
        $conn->select('id,domain,track_area');
		$conn->from(self::$table);
		$conn->where_in('id',$ids);
		$query = $conn->get();

		$result = $query->result_array();
		$this->load->helper('array_util');
		$result = change_data_key($result, 'id');
		return $result;
    }

    public function get_by_param($col,$params,$hash_key)
    {
        $conn = $this->databases->{self::$database}->slaves;
        $conn->select($col);
		$conn->from(self::$table);
        foreach($params as $key=>$value)
        {
            if(is_array($value))
		        $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
		$query = $conn->get();

		$res = $query->result_array();
		$this->load->helper('array_util');
		$res = change_data_key($res,$hash_key);
		return $res;
    }

    public function insert_batch($insert_data)
    {
        if(empty($insert_data))
            return FALSE;
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->insert_batch(self::$table, $insert_data); 
		return $res;
    }
}

