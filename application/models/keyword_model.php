<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Keyword_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table = 't_swan_baidu_keyword';
    protected static $stat_table = 't_swan_baidu_keyword_stat';
    protected static $refbid_table = 't_swan_baidu_keyword_refbid';
    private $table_tag = "t_swan_baidu_user_tag";

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

    public function get_keyword_list($params, $cols, $extra_params = array(), $join = FALSE, $join_table = '', $join_params = array()) {
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
                    $cols[$key] = self::$table . ".keyword_id";
                    break;
                default:
                    break;
                }
            }
            $connection->select($cols);
        }
        $connection->from(static::$table);
        if ($join === TRUE) {
            $join_query = $this->_join_query($join_table, $join_params);
            $connection->join("({$join_query}) join", "join.keyword_id = " . self::$table . ".keyword_id", 'left');
        }
         $connection->join($this->table_tag, $this->table_tag . ".id = " . self::$table . ".tag_id", 'left');

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

    public function get_keyword_with_stat_by_params($params, $stat_params, $cols = array(), $extra_params = array()) {
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
                case 'keyword_id':
                    $cols[$key] = self::$table . ".keyword_id";
                    break;
                default:
                    break;
                }
            }
            $connection->select($cols);
        }
        $connection->from(static::$table);
        $connection->join("({$stat_query_sql}) stat", "stat.keyword_id = " . self::$table . ".keyword_id", 'left');
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
        //echo $connection->last_query();die;
        if (!empty($extra_params['hash_key'])) {
            $this->load->helper('array_util');
            $result = change_data_key($result, $extra_params['hash_key']);
        }
        return $result;
    }

    private function _join_query($table, $params) {
        $connection = $this->databases->{self::$database}->{$this->swan_slaves};
        $connection->from($table);
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

    public function get_keyword_count($user_id,$keyword)
    {
        if(empty($user_id) || empty($keyword))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('count(1) as count');
        $conn->from(self::$table);
        $conn->where('user_id',$user_id);
        if(is_array($keyword))
            $conn->where_in('keyword_id',$keyword);
        else
            $conn->where('keyword_id',$keyword);
        $res = $conn->get();
        
		if(!$res)
			return array();
		return $res->result_array();
    }
    

    /* *
     * 获取客户生效关键词个数
     * */
    public function get_effective_keyword_count($baidu_id)
    {
        if(empty($baidu_id)) {return array();}

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('count(1) as count');
        $conn->from(self::$table);
        $conn->where('user_id', $baidu_id);
        $conn->where('pause', 0);
        $res = $conn->get();
        
		if( ! $res) {return array();}

		return $res->result_array();
    }


    public function get_keyword_by_params($params)
    {
        if(empty($user_id)) {return array();}

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('keyword_id');
        $conn->from(self::$table);
        $conn->where($params);
        $res = $conn->get();
        
        if( ! $res) {return array();}
        return $res->result_array();
    }


    public function get_refbid_info($keyword_ids = array()) {
        if (empty($keyword_ids))
            return array();
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('*');
        $conn->from(self::$refbid_table);
        $conn->where_in('keyword_id', $keyword_ids);

        $res = $conn->get();
        if (!$res)
            return array();

        $result = $res->result_array();
        $this->load->helper('array_util_helper');
        return change_data_key($result, 'keyword_id'); 
    }

    public function add_refbid_keyword($params) {
        if (empty($params)) {
            return array();
        }        

        $connection = $this->databases->{self::$database}->{$this->swan_master};
        $connection->on_duplicate_batch(self::$refbid_table, $params);
    }

    public function delete_refbid_keywords($keyword_ids) {
        
    }

    public function last_rank($last_time,$count)
    {
        if(empty($last_time) || !is_numeric($count)) 
            return array();
        
        $swan_slaves = 'slaves'.$count;
        $conn = $this->databases->{self::$database}->$swan_slaves;
        $sql = "select A.keyword_id,A.keyword,A.user_id,B.alarm_rank,C.rank "
             . "from ". self::$table ." as A "
             . "inner join t_swan_baidu_keyword_autobid as B "
             . "on A.keyword_id = B.keyword_id "
             . "inner join t_swan_baidu_keyword_monitor as C "
             . "on A.keyword_id = C.keyword_id "
             . "where A.bid_status = 2 and A.pause = 0 and C.moni_time=?";
        $res = $conn->query($sql,array($last_time));
        
		if(!$res)
			return array();
		return $res->result_array();
    }

    public function modify_tag($keyword_ids,$tag_id)
    {
        if(empty($keyword_ids))
            return FALSE;

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->where_in('keyword_id',$keyword_ids);
        $res = $conn->update(self::$table,array('tag_id'=>$tag_id));

        return $res;
    }

    public function delete_tag($user_id,$tag_id)
    {
        if(empty($user_id) || empty($tag_id))
            return FALSE;

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->trans_begin();

        $conn->update(self::$table,array('tag_id'=>NULL_KEYWORD_ID),array('tag_id'=>$tag_id,'user_id'=>$user_id));
        $conn->delete($this->table_tag,array('id'=>$tag_id));

        if ($conn->trans_status() === FALSE)
        {
            $conn->trans_rollback();
            return FALSE;
        }
        else
        {
            $conn->trans_commit();
            return TRUE;
        }
    }

    public function get_calc_count($user_id)
    {
        if(empty($user_id))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('count(1) as count');
        $conn->from(self::$table);
        $conn->where('user_id',$user_id);
        $conn->where('ref_status','1');
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    public function get_keyword_tag_count($user_id,$tag_id)
    {
        if(empty($user_id) || empty($tag_id))
            return array();
    
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('count(1) as count');
        $conn->from(self::$table);
        $conn->where('user_id',$user_id);
        $conn->where('tag_id',$tag_id);
        $res = $conn->get();

         if(!$res)
            return array();
        return $res->result_array();
    }
    
    public function keywords_filter($keyword_ids,$tag_id)
    {
        if(empty($keyword_ids))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('keyword_id');
        $conn->from(self::$table);
        $conn->where_in('keyword_id',$keyword_ids);
        $conn->where('tag_id != ',$tag_id);
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    //获取竞价相关数据
    public function bid_info($count)
    {
        if(!is_numeric($count))
            return array();

        $swan_slaves = "slaves".$count;
        $conn = $this->databases->{self::$database}->$swan_slaves;

        $sql = "select C.user_id as baidu_id,C.plan_count,C.unit_count,C.keyword_count,F.lock_count,G.pause_count from "
             . "(select A.user_id,count(distinct B.plan_id) as plan_count,count(distinct A.unit_id) as unit_count,count(A.keyword_id) as keyword_count from ".self::$table." as A inner join t_swan_baidu_unit as B on A.unit_id=B.unit_id where A.bid_status !=1 group by A.user_id)C "
             . "left join (select D.user_id,count(D.keyword_id) as lock_count from ".self::$table." as D inner join t_swan_baidu_keyword_autobid as E on D.keyword_id=E.keyword_id where E.snipe=1 and D.bid_status !=1 group by D.user_id )F on C.user_id=F.user_id "
             . "left join (select user_id,count(keyword_id) as pause_count from ".self::$table." where bid_status=3 group by user_id )G on C.user_id=G.user_id ";

        $res = $conn->query($sql);
        if(!$res)
            return array();
        return $res->result_array();
    }


    //获取关键词效果
    public function bid_keyword_stat($s_date,$e_date,$count)
    {
        if(empty($s_date) || empty($e_date) ||!is_numeric($count))
            return array();

        $swan_slaves = "slaves".$count;
        $conn = $this->databases->{self::$database}->$swan_slaves;

        $sql = "select A.baidu_id,A.impression,A.click,A.cost,D.bid_impression,D.bid_click,D.bid_cost from "
             . "(select baidu_id,sum(impression) as impression,sum(click) as click,sum(cost) as cost from t_swan_baidu_keyword_stat where date>=? and date<=? group by baidu_id)A "
             . "left join (select C.baidu_id,sum(C.impression) as bid_impression,sum(C.click) as bid_click,sum(C.cost) as bid_cost from ".self::$table." as B inner join t_swan_baidu_keyword_stat as C on B.keyword_id=C.keyword_id and B.bid_status !=1 where C.date>=? and C.date<=? group by C.baidu_id )D on A.baidu_id=D.baidu_id ";

        $res = $conn->query($sql,array($s_date,$e_date,$s_date,$e_date));
        if(!$res)
            return array();
        return $res->result_array();
    }

    private function _get_user($count, $sql, $params=array())
    {
        if( ! is_numeric($count) OR empty($sql)) {
            return array();
        }

        $swan_slaves = "slaves".$count;
        $conn = $this->databases->{self::$database}->$swan_slaves;

        $res = $conn->query($sql, $params);
        if( ! $res) {
            return array();
        }
        return $res->result_array();
    }



    /* *
     * 获取激活竞价用户信息
     *
     * */
    public function activate_bid_info($count)
    {
        $sql = "
            select A.user_id as baidu_id,min(B.moni_time) as activate_bid_time
            from t_swan_baidu_keyword as A
                inner join t_swan_baidu_keyword_monitor as B
                on A.keyword_id=B.keyword_id
            group by A.user_id;";
        return $this->_get_user($count, $sql);
    }


    // 获取达标用户（即核心用户）
    // 
    // 所谓的达标用户，即在$from_date和$to_date时间内，有$num_active_days天，
    // 每天有超过$num_active_keywords个竞价词在竞价的用户。
    // $from_date为开始统计日期，格式如「2014-05-20」
    public function core_user($num_active_days, $num_active_keywords,
        $end_date_r, $start_date_r, $count)
    {
        if( ! is_numeric($num_active_days)
         OR ! is_numeric($num_active_keywords)) {
            return array();
        }
        
        $sql = "select user_id as baidu_id from
            (
                select B.user_id,date(A.moni_time),count(distinct A.keyword_id) as num_keywords
                from t_swan_baidu_keyword_monitor as A
                inner join t_swan_baidu_keyword as B
                on A.keyword_id=B.keyword_id
                where date(A.moni_time)<='$start_date_r'
                and date(A.moni_time)>'$end_date_r'
                group by B.user_id,date(A.moni_time)
                having num_keywords>=$num_active_keywords
            )A
            group by A.user_id
            having count(1) >= $num_active_days"; 

        return $this->_get_user($count, $sql);
    }


    /* *
     * 获取当天活跃用户
     *
     * */
    public function active_user($date, $count)
    {
        $sql = "
            select distinct A.user_id as baidu_id
            from t_swan_baidu_keyword as A
                inner join t_swan_baidu_keyword_monitor as B
                on A.keyword_id=B.keyword_id
            where date(B.moni_time)=?;";

        return $this->_get_user($count, $sql, array($date));
    }


    /* *
     * 获取$to_date,$from_date时间段内的有过竞价记录的用户
     *
     * */
    public function bid_user($from_date, $to_date, $count)
    {
        $sql = "
            select distinct A.user_id as baidu_id
            from t_swan_baidu_keyword as A
                inner join t_swan_baidu_keyword_monitor as B
                on A.keyword_id=B.keyword_id
            where date(B.moni_time)> ?
            and date(B.moni_time)< ? ;";

        return $this->_get_user($count, $sql, array($from_date, $to_date));
    }


    /* *
     * 获取截至$date为止，所有有过出价记录的用户
     *
     * */
    public function all_bid_user($date, $count)
    {
        $sql = "
            select distinct A.user_id as baidu_id
            from t_swan_baidu_keyword as A
                inner join t_swan_baidu_keyword_monitor as B
                on A.keyword_id=B.keyword_id
            where date(B.moni_time)<= ? ;";
        
        return $this->_get_user($count, $sql, array($date));
    }


    //获取跟踪对手关键词列表
    public function track_keyword_list($params,$count=FALSE)
    {
        if(empty($params))
            return array();
    
        if($count)
            $col = "count(1) as count";
        else
            $col = "A.keyword_id,A.keyword,A.competitor_status,C.plan_name,B.unit_name,D.tag";
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $sql_data = array();
        $sql = "select $col from ".self::$table. " as A "
             . "inner join t_swan_baidu_unit as B "
             . "on A.unit_id = B.unit_id "
             . "inner join t_swan_baidu_plan as C "
             . "on B.plan_id = C.plan_id "
             . "left join $this->table_tag as D "
             . "on A.tag_id = D.id "
             . "left join(select keyword_id,max(ctime) as ctime from t_swan_baidu_keyword_competitor group by keyword_id)E on A.keyword_id = E.keyword_id "
             . "where A.user_id = ? ";
        $sql_data[] = $params['sem_id'];
        if(!empty($params['plan_id']))
        {
            $sql .= "and C.plan_id = ? ";
            $sql_data[] = $params['plan_id'];
        }
        if(!empty($params['unit_id']))
        {
            $sql .= "and B.unit_id = ? ";
            $sql_data[] = $params['unit_id'];
        }
        if(!empty($params['keyword']))
        {
            $sql .= "and A.keyword like '%".$params['keyword']."%' ";
        }
        if(!empty($params['tag_id']))
        {
            $sql .= "and A.tag_id = ? ";
            $sql_data[] = $params['tag_id'];
        }

        if(!$count)
            $sql .= "order by E.ctime desc,A.keyword_id asc limit {$params['index']},{$params['limit']}";

        $res = $conn->query($sql,$sql_data);

        if(!$res)
            return array();
        return $res->result_array();
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
    
    
    public function update_keyword($params) {
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


    public function insert_batch($insert_data)
    {
        if(empty($insert_data))
            return FALSE;
        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $res = $conn->insert_batch(self::$table, $insert_data); 
		return $res;
    }

    public function get_keyword($params,$cols = array())
    {
        if (empty($params)) {
            return array();
        }

        $conn = $this->databases->{static::$database}->{$this->swan_slaves};
        if (!empty($cols)) {
            // 联表查询转换相同的字段
            foreach ($cols as $key => $value) {
                switch($value) {
                case 'user_id':
                    $cols[$key] = self::$table . ".user_id";
                    break;
                case 'keyword_id':
                    $cols[$key] = self::$table . ".keyword_id";
                    break;
                default:
                    break;
                }
            }
            $conn->select($cols);
        }
        $conn->from(static::$table);
        $conn->join("t_swan_baidu_unit", "t_swan_baidu_unit.unit_id = " . self::$table . ".unit_id");
        $conn->join("t_swan_baidu_plan", "t_swan_baidu_unit.plan_id = t_swan_baidu_plan.plan_id");
        $conn->where(static::$table.".user_id",$params['user_id']);
        if(!empty($params['plan_id']))
            $conn->where('t_swan_baidu_plan.plan_id',$params['plan_id']);
        if(!empty($params['unit_id']))
        {
            if(is_array($params['unit_id']))
                $conn->where_in('t_swan_baidu_unit.unit_id',$params['unit_id']);
            else
                $conn->where('t_swan_baidu_unit.unit_id',$params['unit_id']);
        }
        if(!empty($params['keyword_id']))
        {
            if(is_array($params['keyword_id']))
                $conn->where_in(static::$table.".keyword_id",$params['keyword_id']);
            else
                $conn->where(static::$table.".keyword_id",$params['keyword_id']);
        }

        if(!empty($params['bid_status']))
            $conn->where(static::$table.".bid_status !=",'1');

        if(!empty($params['keyword']))
            $conn->like(static::$table.".keyword",$params['keyword']);
        if(isset($params['index']) && isset($params['limit']))
            $conn->limit($params['limit'],$params['index']);
        $conn->order_by(static::$table.".keyword_id",'desc');

        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    public function get_by_param($count,$params,$col="*")
    {
        if(empty($params) || !isset($count))
            return array();

        $swan_slaves = 'slaves'.$count;
        $conn = $this->databases->{self::$database}->$swan_slaves;
        $conn->select($col);
        $conn->from(self::$table);
        foreach($params as $key=>$value)
        {
            if(is_array($value))
                $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    public function update($count,$params,$update_data)
    {
        if(empty($params) || empty($update_data) || !isset($count))
            return array();

        $swan_master = 'master'.$count;
        $conn = $this->databases->{self::$database}->$swan_master;
        foreach($params as $key=>$value)
        {
            if(is_array($value))
                $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
        $res = $conn->update(self::$table,$update_data);

        return $res;
    }

    public function update_batch($update_data,$primary_key)
    {
        if(empty($primary_key) || empty($update_data))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $res = $conn->update_batch(self::$table,$update_data,$primary_key);

        return $res;
    }
}

