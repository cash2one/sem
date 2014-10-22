<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_model extends CI_Model {

	protected static $database = 'swan';
	protected static $table = 't_swan_baidu_plan';
	private static $stat_table = 't_swan_baidu_plan_stat';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

	public function get_plan_join_stat_by_params($params, $stat_params, $cols = array(), $extra_params = array()) {
		if (empty($params) || empty($stat_params)) {
			return array();
		}
		$stat_query_sql = $this->_stat_query($stat_params);
		$default_extra_params = array(
			'from_master'	=> FALSE,
			'groupby'		=> FALSE,
			'having'		=> FALSE,
			'orderby'		=> FALSE,
			'offset'		=> FALSE,
			'limit'			=> FALSE,
			'hash_key'		=> FALSE,
		);
		$extra_params = array_merge($default_extra_params, $extra_params);
		if ($extra_params['from_master']) {
			$connection = $this->databases->{static::$database}->{$this->swan_master};
		} else {
			$connection = $this->databases->{static::$database}->{$this->swan_slaves};
		}
		if (!empty($cols)) {
			// 联表查询转换相同的字段
			foreach ($cols as $key => $value) {
				switch($value) {
					case 'plan_id':
						$cols[$key] = self::$table . ".plan_id";
					break;
					default:
					break;
				}
			}
			$connection->select($cols);
		}
		$connection->from(static::$table);
		$connection->join("({$stat_query_sql}) stat", "stat.plan_id = " . self::$table . ".plan_id", 'left');
		foreach ($params as $key => $value) {
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
		!empty($extra_params['groupby']) && $connection->group_by($extra_params['groupby']);
		!empty($extra_params['having']) && $connection->having($extra_params['having']);
		!empty($extra_params['orderby']) && $connection->order_by($extra_params['orderby']);
		!empty($extra_params['offset']) && $connection->offset($extra_params['offset']);
		!empty($extra_params['limit']) && $connection->limit($extra_params['limit']);
		$query = $connection->get();
		$result = $query->result_array();
		if (!empty($extra_params['hash_key'])) {
			$this->load->helper('array_util');
			$result = change_data_key($result, $extra_params['hash_key']);
		}
		return $result;
	}

	private function _stat_query($params) {
		$connection = $this->databases->{self::$database}->{$this->swan_slaves};
		$connection->from(self::$stat_table);
		foreach ($params as $key => $value) {
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
		return $connection->get_compile_select();
	}

    public function get_plan_by_user_ids(array $user_ids,$col="*",$type=0)
    {
        if(empty($user_ids))
            return array();

        $connection = $this->databases->{self::$database}->{$this->swan_slaves};
        $connection->select($col);
        $connection->from(self::$table.' as A');
        $connection->join('t_swan_baidu_unit as B','B.plan_id = A.plan_id');
        $connection->join('t_swan_baidu_keyword as C','B.unit_id=C.unit_id','left');
        $connection->where_in('A.user_id',$user_ids);
        !empty($type) && $connection->where('C.bid_status !=','1');
        $connection->group_by('A.plan_id');
        ($type == '1') && $connection->having('keyword_count >','0');
        $connection->order_by('A.plan_id','desc');
        $res = $connection->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    public function get(array $condition,$col='*',$hash_key='')
    {
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select($col);
        $conn->from(self::$table);
        foreach($condition as $key=>$value)
        {
            if(is_array($value))
                $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
        $query = $conn->get();

        if(!$query)
            return array();
        
        $res = $query->result_array();

		if (!empty($hash_key)) {
			$this->load->helper('array_util');
			$res = change_data_key($res, $hash_key);
		}
        return $res;
    }

}

