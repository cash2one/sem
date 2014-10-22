<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_whitelist_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table = 't_swan_baidu_user_whitelist';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

    public function white_count($user_id)
    {
        if(empty($user_id))
            return array();
                
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('count(1) as count');
        $conn->from(self::$table);
        $conn->where('user_id',$user_id);
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }

    public function add($user_id,$white_list)
    {
        if(empty($user_id) || empty($white_list))
            return FALSE;

        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->trans_begin();
        
        foreach($white_list as $white_domain)
        {
            $conn->insert(self::$table,array('user_id'=>$user_id,'white_domain'=>$white_domain));
        }

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

    public function del($user_id,$white_list)
    {
        if(empty($user_id) || empty($white_list))
            return FALSE;
        
        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $conn->where('user_id',$user_id);
        $conn->where_in('white_domain',$white_list);
        $res = $conn->delete(self::$table);

        return $res;
    }

    public function get($user_id)
    {
        if(empty($user_id))
            return array();
                
        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select('white_domain');
        $conn->from(self::$table);
        $conn->where('user_id',$user_id);
        $res = $conn->get();

        if(!$res)
            return array();
        return $res->result_array();
    }
}

