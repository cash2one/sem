<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_tag_model extends CI_Model {

    protected static $database = 'swan';
    protected static $table = 't_swan_baidu_user_tag';

    public function __Construct()
    {
        parent::__Construct();

        if(!empty(Auth_filter::current_sem_id())) 
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
    }

    public function add_tag($user_id,$tag)
    {
        if(empty($tag) || empty($user_id))
            return FALSE;
                
        $conn = $this->databases->{self::$database}->{$this->swan_master};
        $data = array('tag'=>$tag,'belong_user_id'=>$user_id);
        
        $res = $conn->insert(self::$table,$data);

        if($res)
            return $conn->insert_id();
        else
            return FALSE;
    }

    public function get($id,$col="*")
    {
        if(empty($id))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $conn->select($col);
        $conn->from(self::$table);
        $conn->where('id',$id);
        $res = $conn->get();

         if(!$res)
            return array();
        return $res->result_array();
    }

    public function get_tag($user_id)
    {
        if(empty($user_id))
            return array();

        $conn = $this->databases->{self::$database}->{$this->swan_slaves};
        $sql = "select id,tag "
             . "from ".self::$table." "
             . "where belong_user_id=? "
             . "order by id desc";
        $res = $conn->query($sql,array($user_id));

		if(!$res)
			return array();
		return $res->result_array();
    }

}

