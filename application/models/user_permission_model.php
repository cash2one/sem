<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_permission_model extends CI_Model {

    protected static $database = 'swan_extra';
    protected static $table = 't_user_module_permission';

    public function __Construct()
    {
        parent::__Construct();
    }


    //获取用户权限
    public function get($condition=array(),$col="*")
    {
		$conn = $this->databases->{self::$database}->slaves;
		$conn->from(self::$table);
        foreach($condition as $key=>$value)
        {
            if(is_array($value))
                $conn->where_in($key,$value);
            else
                $conn->where($key,$value);
        }
		$table = $conn->get_compile_select();
		// 再分组
        $conn->select($col);
		$conn->from("({$table}) t");
        $conn->join('dic_module','dic_module.id=t.module_id','right');
		$query = $conn->get();

		$result = $query->result_array();
		return $result;
    }

    //修改用户权限,没有就插入
    public function update_batch($update_data)
    {
        if(empty($update_data))
            return FALSE;

        $conn = $this->databases->{self::$database}->master;
        $conn->on_duplicate_batch(self::$table,$update_data);
        return TRUE;
    }
}

