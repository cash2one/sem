<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Package_model extends CI_Model
{

    protected static $database = 'phoenix';

    public function __Construct()
    {
        parent::__Construct();
    }


    public function all_package()
    {
        $sql = "
            select * 
            from t_enterprise_sem_user_package
            where id != 0 ";
        return $this->_query($sql);
    }


    public function max_package()
    {
        $sql = "
            select *
            from t_enterprise_sem_user_package
            order by bid_keyword_num desc
            limit 1;";
        return $this->_query($sql);
    }


    public function insert_package_apply($data)
    {
        if(empty($data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->insert('t_enterprise_sem_user_purchase', $data); 
		return $res;
    }


    private function _query($sql, $params=array())
    {
        $conn = $this->databases->{self::$database}->slaves;
        $res = $conn->query($sql, $params);
        if (! $res) {return array();}

        return $res->result_array();
    }
}


/* End of file */
