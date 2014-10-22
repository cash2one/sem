<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Creative_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table = 't_swan_baidu_creative';
    protected static $stat_table = 't_swan_baidu_creative_stat';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

    public function get_creative_with_stat_by_params($params, $stat_params, $cols = array(), $extra_params = array()) {
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
                case 'creative_id':
                    $cols[$key] = self::$table . ".creative_id";
                    break;
                default:
                    break;
                }
            }
            $connection->select($cols);
        }
        $connection->from(static::$table);
        $connection->join("({$stat_query_sql}) stat", "stat.creative_id = " . self::$table . ".creative_id", 'left');
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

    //获取创意所属的单元
    public function get_unit_by_creative(array $creative_ids)
    {
        if(empty($creative_ids))
            return array();
        
        $connection = $this->databases->{self::$database}->{$this->swan_slaves};
        $connection->select("unit_id");
        $connection->from(self::$table);
        $connection->where_in('creative_id',$creative_ids);

        $query = $connection->get();
        if(!$query)
            return array();
        
        $result = $query->result_array();
        return $result;
    }

    //获取单元下的创意
    public function get_creative_count_by_unit($unit_id,$condition = array())
    {
        if(empty($unit_id))
            return 0;;
        
        $connection = $this->databases->{self::$database}->{$this->swan_slaves};
        $connection->select("count(1) as count");
        $connection->from(self::$table);
        $connection->where('unit_id',$unit_id);
        $connection->where($condition);

        $query = $connection->get();
        if(!$query)
            return array();
        
        $result = $query->result_array();
        return empty($result[0]['count']) ? 0 : $result[0]['count'];
    }
}

