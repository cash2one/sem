<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Enterprise_sem_user_model extends CI_Model {

	private $enterprise_user_table = 't_enterprise_sem_user';
	private $database = 'phoenix';

    
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

    //获取代理商-客户统计信息
    public function get_agency_info($date)
    {
        if(empty($date))
            return array();

        $conn = $this->databases->{$this->database}->slaves;

        $sql = "select C.userid as user_id,C.company_name,C.areas,E.baidu_id,F.customer_count,H.new_add_user,C.add_admin_id,I.today_login_count,J.uninterruptible_amount from $this->enterprise_user_table as A "
             . "inner join t_company_level_extend as B "
             . "on B.customer_id=A.userid and B.customer_type=2 "
             . "inner join t_agent_user_info as C "
             . "on C.userid=B.agency_id "
             . "inner join t_enterprise_user_permission as D "
             . "on A.userid=D.userid and D.product=2 and D.status_product = 0 "
             . "inner join t_account_bind as E "
             . "on E.user_id=A.userid "
             // 总注册用户数
             . "left join "
             . "("
             . "    select agency_id,count(customer_id) as customer_count from t_company_level_extend "
             . "    where customer_type=2 "
             . "    group by agency_id "
             . ")F on F.agency_id=C.userid "
             // 24小时竞价客户数
             . "left join"
             . "("
             . "select A.agency_id, count(A.customer_id) as uninterruptible_amount
             from t_company_level_extend as A
             inner join t_enterprise_sem_user as B
             on A.customer_id=B.userid
             where A.customer_type=2
             and B.uninterruptible=1
             group by A.agency_id"
             . ")J on J.agency_id=C.userid "
             // 当日新增客户数
             . "left join "
             . "("
             . "   select B.agency_id,count(A.userid) as new_add_user
             from t_enterprise_sem_user as A, t_company_level_extend as B, t_enterprise_user as C
             where B.`customer_type`=2
             and C.status=0
             and date(C.ctime)='$date'
             and B.customer_id=A.userid
             and A.userid=C.userid
             group by B.agency_id"
             . ")H on C.userid=H.agency_id "
             // 当日登录客户数
             . " left join "
             . "("
             . "    select B.agency_id,count(A.userid) as today_login_count
             from t_enterprise_sem_user as A, t_company_level_extend as B, t_enterprise_user as C
             where B.`customer_type`=2
             and C.status=0
             and date(C.last_login)='$date'
             and B.customer_id=A.userid
             and A.userid=C.userid
             group by B.agency_id"
             . ")I on C.userid=I.agency_id";

        $res = $conn->query($sql);
        if(!$res)
            return array();

        return $res->result_array();
    }
    
    //获取代理商下客服的客户统计信息
    public function get_agent_info($date, $agency_id)
    {
        if(empty($date))
            return array();

        $conn = $this->databases->{$this->database}->slaves;

        $sql = "select C.userid as user_id,C.contact,C.username,C.areas,E.baidu_id,F.customer_count,H.new_add_user,C.add_admin_id,I.today_login_count,J.uninterruptible_amount from $this->enterprise_user_table as A "
             . "inner join t_company_level_extend as B "
             . "on B.customer_id=A.userid and B.customer_type=2 and B.agency_id='$agency_id' "
             . "inner join t_agent_user_info as C "
             . "on C.userid=B.agent_id "
             . "inner join t_enterprise_user_permission as D "
             . "on A.userid=D.userid and D.product=2 and D.status_product = 0 "
             . "inner join t_account_bind as E "
             . "on E.user_id=A.userid "
             // 总注册用户数
             . "left join "
             . "("
             . "    select agent_id,count(customer_id) as customer_count from t_company_level_extend "
             . "    where customer_type=2 and agency_id='$agency_id' "
             . "    group by agent_id "
             . ")F on F.agent_id=C.userid "
             // 开启24小时竞价数
             . "left join "
             . "("
             . "select A.agent_id, count(A.customer_id) as uninterruptible_amount
             from t_company_level_extend as A
             inner join t_enterprise_sem_user as B
             on A.customer_id=B.userid
             where A.customer_type=2
             and B.uninterruptible=1
             and A.agency_id='$agency_id'
             group by A.agent_id "
             . ")J on J.agent_id=C.userid "
             // 当日新增客户数
             . "left join "
             . "("
             . "   select B.agent_id,count(A.userid) as new_add_user
             from t_enterprise_sem_user as A, t_company_level_extend as B, t_enterprise_user as C
             where B.`customer_type`=2 and B.`agency_id`='$agency_id' 
             and C.status=0
             and date(C.ctime)='$date'
             and B.customer_id=A.userid
             and A.userid=C.userid
             group by B.agent_id"
             . ")H on C.userid=H.agent_id "
             // 当日登录客户数
             . " left join "
             . "("
             . "    select B.agent_id,count(A.userid) as today_login_count
             from t_enterprise_sem_user as A, t_company_level_extend as B, t_enterprise_user as C
             where B.`customer_type`=2 and B.`agency_id`='$agency_id' 
             and C.status=0
             and date(C.last_login)='$date'
             and B.customer_id=A.userid
             and A.userid=C.userid
             group by B.agent_id"
             . ")I on C.userid=I.agent_id";

        $res = $conn->query($sql);
        if(!$res)
            return array();

        return $res->result_array();
    }

    public function agency_ids()
    {
        $conn = $this->databases->{$this->database}->slaves;
        $sql = "select userid as agency_id
            from t_agent_user_info
            where usertype='2' ";
        $res = $conn->query($sql);
        if(!$res)
            return array();

        return $res->result_array();
    }

    //更新用户状态
    public function update($ids,$data)
    {
        if(empty($ids) || empty($data))
            return FALSE;
		$conn = $this->databases->{$this->database}->master;

        if(is_array($ids))
            $conn->where_in('userid',$ids);
        else
            $conn->where('userid',$ids);
        $res = $conn->update($this->enterprise_user_table, $data); 
        
        return $res;
    }

    public function customer_info()
    {
        $conn = $this->databases->{$this->database}->slaves;
        $sql = "select D.add_admin_id,B.contact,A.agency_id,A.agent_id,E.company_name,C.baidu_id,B.username,B.userid,B.name,B.mobile,B.email,B.ctime,B.last_login,F.status_product,G.uninterruptible
            from t_enterprise_user as B
            inner join t_company_level_extend A
            on  A.customer_id=B.userid
            inner join t_agent_user_info as E
            on A.agent_id=E.userid
            inner join t_enterprise_user_permission as F
            on F.userid=B.userid and F.product=2 and F.status_product = 0 
            inner join t_agent_user_info as D
            on D.userid=A.agency_id
            inner join t_enterprise_sem_user as G
            on G.userid=B.userid
            left join t_account_bind as C
            on C.user_id=B.userid;";

        $res = $conn->query($sql);
        if(!$res) {
            return array();
        }
        return $res->result_array();
    }
    
   /* 
    public function branch_id_and_level()
    {
        $conn = $this->databases->{$this->database}->slaves;
        $sql = "select userid,branch_level
            from t_agent_user_info
            where usertype=3;";

        $res = $conn->query($sql);
        if(!$res) {return array();}
        return $res->result_array();
    }
    */


    public function branch_info($date, $branch_level)
    {
        $branch_map = array();
        $branch_map[1] = 'branch_one';
        $branch_map[2] = 'branch_two';
        $branch_map[3] = 'branch_three';
        $branch_map[4] = 'branch_four';
        $branch_map[5] = 'branch_five';

        $branch_field = $branch_map[intval($branch_level)];
        
        if(empty($date) OR empty($branch_field)) {return array();}

        $conn = $this->databases->{$this->database}->slaves;

        $sql = "select C.userid as user_id,C.company_name,C.areas,E.baidu_id,F.customer_count,H.new_add_user,C.add_admin_id,I.today_login_count,J.uninterruptible_amount from $this->enterprise_user_table as A "
             . "inner join t_company_level_extend as B "
             . "on B.customer_id=A.userid and B.customer_type=2 "
             . "inner join t_agent_user_info as C "
             . "on C.userid=B.$branch_field "
             . "inner join t_enterprise_user_permission as D "
             . "on A.userid=D.userid and D.product=2 and D.status_product = 0 "
             . "inner join t_account_bind as E "
             . "on E.user_id=A.userid "
             // 总注册用户数
             . "left join "
             . "("
             . "    select $branch_field,count(customer_id) as customer_count from t_company_level_extend "
             . "    where customer_type=2 "
             . "    group by $branch_field "
             . ")F on F.$branch_field=C.userid "
             // 24小时竞价数
             . "left join "
             . "("
             . " select A.$branch_field, count(A.customer_id) as uninterruptible_amount
             from t_company_level_extend as A
             inner join t_enterprise_sem_user as B
             on A.customer_id=B.userid
             where A.customer_type=2
             and B.uninterruptible=1
             group by A.$branch_field "
             . ")J on J.$branch_field=C.userid "
             // 当日新增客户数
             . "left join "
             . "("
             . "   select B.$branch_field,count(A.userid) as new_add_user
             from t_enterprise_sem_user as A, t_company_level_extend as B, t_enterprise_user as C
             where B.`customer_type`=2  
             and C.status=0
             and date(C.ctime)='$date'
             and B.customer_id=A.userid
             and A.userid=C.userid
             group by B.$branch_field"
             . ")H on C.userid=H.$branch_field "
             // 当日登录客户数
             . " left join "
             . "("
             . "    select B.$branch_field,count(A.userid) as today_login_count
             from t_enterprise_sem_user as A, t_company_level_extend as B, t_enterprise_user as C
             where B.`customer_type`=2 
             and C.status=0
             and date(C.last_login)='$date'
             and B.customer_id=A.userid
             and A.userid=C.userid
             group by B.$branch_field"
             . ")I on C.userid=I.$branch_field";

        $res = $conn->query($sql);
        if( ! $res) {return array();}

        return $res->result_array();
    }


    public function admin_info()
    {
        $sql = "select B.userid as admin_id,B.company_name as admin_name,A.userid as agency_id
            from t_agent_user_info as A
            inner join t_agent_user_info as B
            on A.add_admin_id=B.userid
            where A.usertype=2
            and B.usertype!=6 ";

        return $this->_query($sql);
    }
    
    
    /* *
     * 客户套餐申请信息
     * */
    public function applying_package_info($user_id)
    {
        if(empty($user_id)) {return array();}
        
        $sql = "
            select A.userid,B.apply_type,B.apply_package,B.apply_time,C.id,C.bid_keyword_num,C.money
            from t_enterprise_sem_user as A
                inner join t_enterprise_sem_user_purchase as B
                on B.userid=A.userid
                inner join t_enterprise_sem_user_package as C
                on B.apply_package=C.id
            where A.userid= ? 
            and B.buy_status = 0
            order by B.apply_time desc
            limit 0,1;";

        return $this->_query($sql, array($user_id));
    }
    
    
    /* *
     * 客户已开通套餐信息
     * */
    public function opened_package_info($user_id, $date)
    {
        if(empty($user_id)) {return array();}
        
        $sql = "
            select A.userid,A.bidword_package_id as id,B.bid_keyword_num,B.money,A.bidword_money,A.bidword_effective_date,A.bidword_expiration as expiration_date
            from t_enterprise_sem_user as A
                inner join t_enterprise_sem_user_package as B
                on A.bidword_package_id=B.id
            where A.userid= ? 
            and A.bidword_package_id != 0
            and A.bidword_effective_date<= ?
            and A.bidword_expiration >= ? 
            limit 0,1;";

        return $this->_query($sql, array($user_id, $date, $date));
    }
    

    public function auditor_info($user_id)
    {
        $sql = "
            select B.*
            from t_company_level_extend as A
                inner join t_agent_user_info as B
                on A.agency_id=B.add_admin_id
            where A.customer_id = ? 
            and A.customer_type='2'
            and B.usertype = 10
            limit 0,1;";

        return $this->_query($sql, array($user_id));
    }


    public function user_info($user_id)
    {
        $sql = "
            select userid,username,name
            from t_enterprise_user
            where userid=?
            limit 0,1;";
        return $this->_query($sql, array($user_id));
    }

    
    private function _query($sql, $params=array())
    {
        $conn = $this->databases->{$this->database}->slaves;
        $res = $conn->query($sql, $params);
        if( ! $res) {return array();}

        return $res->result_array();
    }
}

/* End of file enterprise_sem_user_model.php */
/* Location: ./application/models/enterprise_sem_user_model.php */

