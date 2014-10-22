<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class monitor_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table = 't_swan_baidu_keyword_monitor';


    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

    public function rank_data($id,$s_time,$e_time)
    {
        if(empty($id) || empty($s_time) || empty($e_time))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('keyword_id as id,bid,rank,compete_rank,moni_time');
        $conn->from(self::$table);
        $conn->where('keyword_id',$id);
        $conn->where('moni_time <=',$e_time);
        $conn->where('moni_time >=',$s_time);
        $conn->order_by('moni_time','asc');
        $res = $conn->get();
        
		if(!$res)
			return array();
		return $res->result_array();
    }

    public function get_last_time()
    {
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('max(moni_time) as last_time');
        $conn->from(self::$table);
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

	public function get_recently($params) {
		if (empty($params)) {
			return array();
		}
		// 先排序
		$connection = $this->databases->{static::$database}->{$this->swan_slaves};
		$connection->from(static::$table);
		foreach ($params as $key => $value) {
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
		$connection->order_by('moni_time desc');
		$table = $connection->get_compile_select();
		// 再分组
		$connection->from("({$table}) t");
		$connection->group_by('keyword_id');
		$query = $connection->get();
		$result = $query->result_array();
		$this->load->helper('array_util');
		$result = change_data_key($result, 'keyword_id');
		return $result;
	}
}

