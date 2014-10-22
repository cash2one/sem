<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Keyword_competitor_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table = 't_swan_baidu_keyword_competitor';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }


    //获取跟踪对手关键词列表
    public function competitor_data($ids)
    {
        if(empty($ids))
            return array();
    
        $col = "keyword_id,competitor_id";
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        
        $conn->select($col);
        $conn->from(self::$table);
        $conn->where_in('keyword_id',$ids);
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    public function insert_batch($insert_data,$del_data)
    {
        if(empty($insert_data) || empty($del_data))
            return FALSE;

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->trans_start();
        //删除数据
        $conn->where_in('keyword_id',$del_data);
        $conn->delete(self::$table);
        //插入数据
        $conn->insert_batch(self::$table, $insert_data); 
        //修改状态
        $conn->where_in('keyword_id',$del_data);
        $conn->update('t_swan_baidu_keyword',array('competitor_status'=>'2'));

        $conn->trans_complete();
		return $conn->trans_status();
    }
}

