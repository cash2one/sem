<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Autobid_model extends CI_Model {

	protected static $database = 'swan';
	protected static $table = 't_swan_baidu_keyword_autobid';
	private static $keyword_table = 't_swan_baidu_keyword';
	private static $tag_table = 't_swan_baidu_user_tag';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

	public function insert_batch($params) {
		if (empty($params)) {
			return FALSE;
		}
		$connection = $this->databases->{self::$database}->{$this->swan_master};
		return $connection->on_duplicate_batch(static::$table, $params);
//		return $connection->insert_batch(static::$table, $params);
	}

	public function update_autobid_by_keyword_id($keyword_id, $params) {
		if (empty($keyword_id)) return FALSE;
		$fields = "(`keyword_id`,";
		$values = "('{$keyword_id}',";
		$update = '';
		foreach ($params as $field => $value) {
			$fields .= "`{$field}`,";
			$values .= "'{$value}',";
			$update .= "`{$field}` = VALUES(`{$field}`),";
		}
		$fields = rtrim($fields, ',') . ')';
		$values = rtrim($values, ',') . ')';
		$update = rtrim($update, ',');
		$sqlComm = "INSERT INTO " . self::$table . " {$fields} VALUES {$values} ON DUPLICATE KEY UPDATE {$update}";
		$connection = $this->databases->{self::$database}->{$this->swan_master};
		$connection->query($sqlComm);
		return ($connection->affected_rows() > 0) ? TRUE : FALSE;
	}

	public function update_autobid_by_keyword_ids($update_params) {
        if ( ! is_array($update_params) 
            OR empty($update_params)) {
                return FALSE;
        }
		$connection = $this->databases->{self::$database}->{$this->swan_master};
		return $connection->on_duplicate_batch(self::$table, $update_params);
	}	


    public function update_autobid($params) {
        if ( ! is_array($params) 
            OR empty($params)) {
                return FALSE;
        }
		$conn = $this->databases->{self::$database}->{$this->swan_master};
        foreach($params as $param) {
            $conn->where('keyword_id', $param['keyword_id']);
            $conn->update(self::$table, $param);
        }
    }

	public function get_autobid_join_keyword_by_params($params, $cols = array(), $extra_params = array()) {
		if (empty($params)) {
			return array();
		}
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
					case 'keyword_id':
						$cols[$key] = self::$keyword_table . ".keyword_id";
					break;
					default:
					break;
				}
			}
			$connection->select($cols);
		}
		$connection->from(static::$table);
		$connection->join(self::$keyword_table, self::$keyword_table . ".keyword_id = " . self::$table . ".keyword_id");
		foreach ($params as $key => $value) {
			switch($key) {
				case 'user_id':
					$key = self::$keyword_table . "." . $key;
				break;
				default:
				break;
			}
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
        $connection->join(self::$tag_table, self::$tag_table . ".id = " . self::$keyword_table . ".tag_id", 'left');
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

    public function update($data,$condition = NULL)
    {
        if(empty($data))
            return FALSE;

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        if(!is_null($condition))
            $conn->where($condition);

        $res = $conn->update(self::$table,$data);
        return $res;
    }

    public function update_status($update_data,$condition)
    {
        if(empty($update_data) || empty($condition))
            return FALSE;
        
        $conn = $this->databases->{self::$database}->{$this->swan_master};
        foreach($condition as $key=>$value)
        {
            if(is_array($value))
                $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
        $res = $conn->update(self::$table,$update_data);
        return $res;
    }

    public function get_by_id($id,$col="*")
    {
        if(empty($id))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select($col);
        $conn->from(self::$table);
        $conn->where('keyword_id',$id);
        $res = $conn->get();
        
		if(!$res)
			return array();
		return $res->result_array();
    }

    public function get_by_plan($plan_ids,$col="*")
    {
        if(empty($plan_ids))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select($col);
        $conn->from(self::$table.' as A ');
        $conn->join('t_swan_baidu_keyword','t_swan_baidu_keyword.keyword_id=A.keyword_id');
        if(is_array($plan_ids))
            $conn->where_in('A.plan_id',$plan_ids);
        else    
            $conn->where('A.plan_id',$plan_ids);
        $conn->where('t_swan_baidu_keyword.bid_status','2');
        $res = $conn->get();
        
		if(!$res)
			return array();
		return $res->result_array();
    }

    //多表,拿到关键词，计划，账户的地域
    public function get_all_area($keyword_ids)
    {
        if(empty($keyword_ids))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('A.bid_area,B.region,C.region_target');
        $conn->from(self::$table.' as A ');
        $conn->join('t_swan_baidu_plan as B ','A.plan_id=B.plan_id');
        $conn->join('t_swan_baidu_user_info as C ','B.user_id=C.user_id');

        if(is_array($keyword_ids))
            $conn->where_in('A.keyword_id',$keyword_ids);
        else    
            $conn->where('A.keyword_id',$keyword_ids);

        $res = $conn->get();
        
		if(!$res)
			return array();
		return $res->result_array();
    }

    /**
     * 删除智能竞价关键词，并将关键词状态至为「未开启」
     *     t_swan_baidu_keyword_autobid
     *     t_swan_baidu_keyword
     *
     * return TRUE/FALSE
     *
     * */
    public function delete_autobid_keywords(array $params)
    {
        $t_autobid = self::$table;
        $t_keyword = self::$keyword_table;

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        
        $conn->trans_start();
        
        $conn->where_in('keyword_id', $params);
        $conn->delete($t_autobid);
        $conn->where_in('keyword_id', $params);
        $conn->update($t_keyword, array('bid_status' => 1));

        $conn->trans_complete();
        return $conn->trans_status();
    }


    public function get_all_autobid_keywords($userid)
    {
        $t_keyword = self::$keyword_table;
        $unopened_bid_status = 1;
        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->select('keyword_id');
        $conn->where('user_id', $userid);
        $conn->where('bid_status !=', $unopened_bid_status);
        $query = $conn->get($t_keyword);
        
        $keyword_ids = array();
        foreach ($query->result_array() as $row) {
            $keyword_ids[] = $row['keyword_id'];
        }
        return $keyword_ids;
    }


    public function count_autobid_keywords($userid)
    {
        $t_keyword = self::$keyword_table;
        $unopened_bid_status = 1;
        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->where('user_id', $userid);
        $conn->where('bid_status !=', $unopened_bid_status);
        return $conn->count_all_results($t_keyword);
    }

    public function get_curr_keyword_info($params,array $cols,$hash_key = '')
    {
        if(empty($params))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
		if (!empty($cols)) {
			// 联表查询转换相同的字段
			foreach ($cols as $key => $value) {
				switch($value) {
					case 'keyword_id':
						$cols[$key] = self::$keyword_table . ".keyword_id";
					break;
					default:
					break;
				}
			}
			$conn->select($cols);
		}
        $conn->from(self::$table);
        $conn->join(self::$keyword_table,self::$table.'.keyword_id='.self::$keyword_table.'.keyword_id');
        foreach($params as $key=>$value)
        {
            if($key == 'keyword_id')
                $key = self::$table.'.keyword_id';
            if(is_array($value))
                $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
        $res = $conn->get();
        
		if(!$res)
			return array();
		$result = $res->result_array();

        if (!empty($hash_key)) {
            $this->load->helper('array_util');
            $result = change_data_key($result, $hash_key);
        }
        return $result;
    }


    public function autobid_keyword_amount($baidu_id)
    {
        if (empty($baidu_id)) {return array();}
        $swan_slaves = 'slaves'.$baidu_id%SWAN_DB_COUNT;
		$conn = $this->databases->{self::$database}->{$swan_slaves};
        $sql = "
            select count(1) as count
            from t_swan_baidu_keyword as A
                inner join t_swan_baidu_keyword_autobid as B
                on A.keyword_id=B.keyword_id
            where A.user_id = ?
            group by A.user_id;";
        
        $res = $conn->query($sql, array($baidu_id)); 
		if( ! $res) {return array();}
		return $res->result_array();
    }
}

