<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account_bind_model extends CI_Model {

	private $table = 't_account_bind';
	private $database = 'phoenix';

	//获取默认绑定用户的信息
	public function get_bind_info($userid) {
		if(empty($userid))
			return array();

		$conn = $this->databases->{$this->database}->slaves;

        $sql = "select baidu_id from $this->table as A "
             . "where A.default = 1 and A.user_id = ? "
             . "limit 0,1 ";

        $sql_data[] = $userid;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
	}

    //获取绑定信息
    public function bind_info($hzuser_id,$user_id)
    {
        if(empty($hzuser_id) || empty($user_id))
            return array();

        $conn = $this->databases->{$this->database}->slaves;

        $sql = "select baidu_id from $this->table "
             . "where user_id = ? and baidu_id = ? "
             . "limit 0,1 ";

        $sql_data[0] = $hzuser_id;
        $sql_data[1] = $user_id;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
    }

    //获取hz用户下的所有user
    public function all_user($user_id)
    {
        if(empty($user_id))
            return array();

        $conn = $this->databases->{$this->database}->slaves;

        $sql = "select * from $this->table "
             . "where user_id = ? ";

        $sql_data[0] = $user_id;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
    }

    //根据用户名获取个数
    public function get_count_by_id($baidu_id)
    {
		if(empty($baidu_id))
			return array();

		$conn = $this->databases->{$this->database}->slaves;

        $sql = "select count(1) as count from $this->table "
             . "where baidu_id = ? ";

        $sql_data[] = $baidu_id;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
    }

    //获取hz用户绑定了几个账户
    public function get_count($user_id)
    {
		if(empty($user_id))
			return array();

		$conn = $this->databases->{$this->database}->slaves;

        $sql = "select count(1) as count from $this->table "
             . "where user_id = ? ";

        $sql_data[] = $user_id;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
    }

    public function insert_bind($insert_bind_data)
    {
        if(empty($insert_bind_data))
            return FALSE;

        $conn = $this->databases->{$this->database}->master;
        return $conn->insert($this->table,$insert_bind_data);

    }

    public function get_hzuser_info($ids)
    {
        if(empty($ids))
            return array();

        $conn = $this->databases->{$this->database}->slaves;
        $conn->select('A.baidu_id,B.mobile,C.uninterruptible');
        $conn->from($this->table.' as A');
        $conn->join('t_enterprise_user as B','A.user_id=B.userid');
        $conn->join('t_enterprise_sem_user as C','B.userid=C.userid');
        $conn->where_in('A.baidu_id',$ids);
		$res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

    public function hzuser_level($ids)
    {
        if(empty($ids))
            return array();

        $conn = $this->databases->{$this->database}->slaves;
        $conn->select('A.baidu_id,B.name,B.ctime,C.*');
        $conn->from($this->table.' as A');
        $conn->join('t_enterprise_user as B','A.user_id=B.userid');
        $conn->join('t_company_level_extend as C','B.userid=C.customer_id');
        $conn->where_in('A.baidu_id',$ids);
        $conn->where('customer_type','2');
		$res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

    public function get_by_baidu($baidu_id,$col="*")
    {
        if(empty($baidu_id))
            return array();

        $conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->table);
        $conn->where('baidu_id',$baidu_id);
		$res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

    public function get_login_deadline($date,$col="*")
    {
        if(empty($date))
            return array();

        $conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->table);
        $conn->join('t_enterprise_sem_user','t_enterprise_sem_user.userid='.$this->table.'.user_id');
        $conn->join('t_enterprise_user','t_enterprise_user.userid=t_enterprise_sem_user.userid');
        $conn->where('uninterruptible','1');
        $conn->where('last_login <=',$date);
		$res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }
}

// END Enterprise_user_model class

/* End of file enterprise_user_model.php */
/* Location: ./application/models/enterprise_user_model.php */

