<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Enterprise_user_model extends CI_Model {

	private $enterprise_user_table = 't_enterprise_user';
	private $database = 'phoenix';

	//根据用户名获取用户信息
	public function get_user_by_name($username) {
		if(empty($username))
			return array();

		$conn = $this->databases->{$this->database}->slaves;

        $sql = "select A.*,B.status_product,C.lock,C.expiration,C.questionnaire from $this->enterprise_user_table as A "
             . "left join "
             . "(select * from t_enterprise_user_permission where product=2)B "
             . "on A.userid = B.userid "
             . "left join t_enterprise_sem_user as C on C.userid=A.userid "
             . "where A.username = ? "
             . "limit 0,1 ";

        $sql_data[] = $username;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
	}

	//根据用户id获取用户信息
	public function get_user_by_id($user_id) {
		if(empty($user_id))
			return array();

		$conn = $this->databases->{$this->database}->slaves;

        $sql = "select A.*,B.status_product,C.lock,C.expiration,C.compete_expiration,C.owner as sem_owner,C.uninterruptible,C.tips,C.questionnaire from $this->enterprise_user_table as A "
             . "left join "
             . "(select * from t_enterprise_user_permission where product=2)B "
             . "on A.userid = B.userid "
             . "left join t_enterprise_sem_user as C on C.userid=A.userid "
             . "where A.userid = ? "
             . "limit 0,1 ";

        $sql_data[] = $user_id;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
	}
    
    //更新用户状态
    public function update_user_by_id($id,$data)
    {
        if(empty($id) || empty($data))
            return FALSE;
		$conn = $this->databases->{$this->database}->master;

        $conn->where('userid', $id);
        $res = $conn->update($this->enterprise_user_table, $data); 
        
        return $res;
    }

    //获取用户信息
    public function get_info_by_id($user_id,$col = "*")
    {
        if(empty($user_id))
            return array();

        $conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->enterprise_user_table);
        $conn->where('userid', $user_id);
        $res = $conn->get();
    
        if(!$res)
            return array();
        return $res->result_array();
    }

    //获取所属代理商或客服信息
    public function get_owner_info($user_id)
    {
        if(empty($user_id))
            return array();
            
        $conn = $this->databases->{$this->database}->slaves;
        $sql = "select B.* from t_enterprise_sem_user as A "
             . "inner join t_agent_user_info as B on A.owner=B.userid "
             . "where A.userid = ? "
             . "limit 0,1";

        $res = $conn->query($sql,array($user_id));
    
        if(!$res)
            return array();
        return $res->result_array();
    }

    //获取用户信息
    public function sem_user_status()
    {
        $conn = $this->databases->{$this->database}->slaves;

             //开户未登录
        $sql = "("
             . "    select A.userid,A.username,A.name,A.ctime,A.last_login,'1' as 'status','0' as 'baidu_id' from $this->enterprise_user_table as A "
             . "    inner join t_enterprise_user_permission as B on A.userid=B.userid and B.product=2 and B.status_product=0 where A.last_login='0000-00-00 00:00:00' "
             . ") "
             . "union "
             //登录未绑定
             . "("
             . "    select A.userid,A.username,A.name,A.ctime,A.last_login,'2' as 'status','0' as 'baidu_id' from $this->enterprise_user_table as A inner join t_enterprise_user_permission as C on A.userid=C.userid and C.status_product=0 and C.product=2 where A.last_login != '0000-00-00 00:00:00' and A.userid not in (select distinct user_id from t_account_bind) "
             . ")"
             . "union "
             //绑定未竞价
             . "("
             . "    select B.userid,B.username,B.name,B.ctime,B.last_login,'3' as 'status',A.baidu_id from t_account_bind as A inner join $this->enterprise_user_table as B on A.user_id=B.userid "
             . ")";


        $res = $conn->query($sql);
    
        if(!$res)
            return array();
        return $res->result_array();
    }

    //获取未登录客户信息
    public function get_user_not_login()
    {
        $col = 'username,mobile,email,ctime';
        $conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->enterprise_user_table);
        $conn->join('t_enterprise_sem_user',$this->enterprise_user_table.'.userid=t_enterprise_sem_user.userid');
        $conn->where('last_login',DEFAULT_LAST_LOGIN);
        $res = $conn->get();
    
        if(!$res)
            return array();
        return $res->result_array();
    }

}

// END Enterprise_user_model class

/* End of file enterprise_user_model.php */
/* Location: ./application/models/enterprise_user_model.php */

