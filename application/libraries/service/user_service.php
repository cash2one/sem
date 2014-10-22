<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('user_info_model');
        $this->CI->load->model('account_bind_model');
        $this->CI->load->model('user_stat_model');
        $this->CI->load->model('user_whitelist_model');
        $this->CI->load->model('plan_model');
        $this->CI->load->model('unit_model');
	}

    public function user_info($user_id)
    {
        if(empty($user_id))
            return array();
        $res = $this->CI->user_info_model->user_info($user_id);       
        return empty($res[0]) ? array() : $res[0];
    }
    
    public function bind_info($hzuser_id,$user_id)
    {
        if(empty($hzuser_id) || empty($user_id))
            return array();
        
        $bind_user = $this->CI->account_bind_model->bind_info($hzuser_id,$user_id);
        $id = empty($bind_user[0]['baidu_id']) ? NULL : $bind_user[0]['baidu_id'];
        $res = $this->CI->user_info_model->user_info($id,'user_id,init_flag');

        return empty($res[0]) ? array() : $res[0];
    }

    public function get_sem_user($user_id)
    {
        if(empty($user_id))
            return array();

        $bind_user = $this->CI->account_bind_model->all_user($user_id);
        $ids = array();
        foreach($bind_user as $value)
        {
            $ids[] = $value['baidu_id'];
        }
        return $ids;
    }

    public function get_user($sem_user)
    {
        if(empty($sem_user))
            return array();

        $res = $this->CI->user_info_model->get_by_ids($sem_user);

        return $res;
    }

    public function get_plan($sem_user,$type=0)
    {
        if(empty($sem_user))
            return array();
        
        $col = 'A.user_id,count(C.keyword_id) as keyword_count,A.plan_id,A.plan_name,A.pause,A.region';
        $res = $this->CI->plan_model->get_plan_by_user_ids($sem_user,$col,$type);
        //获取每个计划已竞价的关键词数，包括竞价中和竞价暂停
        $col = 'A.plan_id,count(C.keyword_id) as bid_keyword_count';
        $plan_bid = $this->CI->plan_model->get_plan_by_user_ids($sem_user,$col,'2');
        $this->CI->load->helper('array_util');
        $plan_bid = change_data_key($plan_bid,'plan_id');

        foreach($res as &$value)
        {
            $value['bid_keyword_count'] = empty($plan_bid[$value['plan_id']]) ? 0 : $plan_bid[$value['plan_id']]['bid_keyword_count'];
        }
        return $res;
    }

    public function get_unit($sem_user,$type=0)
    { 
        if(empty($sem_user))
            return array();

        $col = 'A.plan_id,count(B.keyword_id) as keyword_count,A.unit_id,A.unit_name,A.pause';
        $res = $this->CI->unit_model->get_unit_by_user_ids($sem_user,$col,$type);
        //获取每个计划已竞价的关键词数，包括竞价中和竞价暂停
        $col = 'A.unit_id,count(B.keyword_id) as bid_keyword_count';
        $unit_bid = $this->CI->unit_model->get_unit_by_user_ids($sem_user,$col,'2');
        $this->CI->load->helper('array_util');
        $unit_bid = change_data_key($unit_bid,'unit_id');

        foreach($res as &$value)
        {
            $value['bid_keyword_count'] = empty($unit_bid[$value['unit_id']]) ? 0 : $unit_bid[$value['unit_id']]['bid_keyword_count'];
        }
        return $res;
    }

    //整合数据
    /*public function combian($user,$plan,$unit)
    {
        if(empty($user))
            return array();
        
        $res = array();
        $this->CI->load->helper('string');
        foreach($user as $v_user)
        {
            $c_user_id = $v_user['user_id'];
            //$res[$c_user_id]['name'] = s_mb_substr($v_user['username'],0,6);
            $res[$c_user_id]['name'] = $v_user['username'];
            $res[$c_user_id]['id'] = $v_user['user_id'];
            $res[$c_user_id]['plan'] = array();
            foreach($plan as $v_plan)
            {
                $c_plan_id = $v_plan['plan_id'];
                if($v_plan['user_id'] == $v_user['user_id'])
                {
                    //$res[$c_user_id]['plan'][$c_plan_id]['name'] = s_mb_substr($v_plan['plan_name'],0,6);
                    $res[$c_user_id]['plan'][$c_plan_id]['name'] = $v_plan['plan_name'];
                    $res[$c_user_id]['plan'][$c_plan_id]['id'] = $v_plan['plan_id'];
                    $res[$c_user_id]['plan'][$c_plan_id]['pause'] = $v_plan['pause'];
                    foreach($unit as $v_unit)
                    {
                        $c_unit_id = $v_unit['unit_id'];
                        if($v_unit['plan_id'] == $v_plan['plan_id'])
                        {
                            //$res[$c_user_id]['plan'][$c_plan_id]['unit'][$c_unit_id]['name'] = s_mb_substr($v_unit['unit_name'],0,6);
                            $res[$c_user_id]['plan'][$c_plan_id]['unit'][$c_unit_id]['name'] = $v_unit['unit_name'];
                            $res[$c_user_id]['plan'][$c_plan_id]['unit'][$c_unit_id]['id'] = $v_unit['unit_id'];
                            $res[$c_user_id]['plan'][$c_plan_id]['unit'][$c_unit_id]['pause'] = $v_unit['pause'];
                        }
                    }
                }
            }
        }

        return $res;
    }*/

    //整合数据
    public function combian($user,$plan,$unit)
    {
        if(empty($user))
            return array();
        
        $res = array();
        $this->CI->load->helper('string');
        $count = 0;
        foreach($user as $v_user)
        {
            $res[$count]['name'] = $v_user['username'];
            $res[$count]['id'] = $v_user['user_id'];
            $res[$count]['plan'] = array();
            $p_count = 0;
            foreach($plan as $v_plan)
            {
                if($v_plan['user_id'] == $v_user['user_id'])
                {
                    $res[$count]['plan'][$p_count]['name'] = $v_plan['plan_name'];
                    $res[$count]['plan'][$p_count]['id'] = $v_plan['plan_id'];
                    $res[$count]['plan'][$p_count]['pause'] = $v_plan['pause'];
                    $res[$count]['plan'][$p_count]['keyword_count'] = $v_plan['keyword_count'];
                    $res[$count]['plan'][$p_count]['region'] = $v_plan['region'];
                    $res[$count]['plan'][$p_count]['bid_keyword_count'] = $v_plan['bid_keyword_count'];
                    $u_count = 0;
                    foreach($unit as $v_unit)
                    {
                        if($v_unit['plan_id'] == $v_plan['plan_id'])
                        {
                            $res[$count]['plan'][$p_count]['unit'][$u_count]['name'] = $v_unit['unit_name'];
                            $res[$count]['plan'][$p_count]['unit'][$u_count]['id'] = $v_unit['unit_id'];
                            $res[$count]['plan'][$p_count]['unit'][$u_count]['pause'] = $v_unit['pause'];
                            $res[$count]['plan'][$p_count]['unit'][$u_count]['keyword_count'] = $v_unit['keyword_count'];
                            $res[$count]['plan'][$p_count]['unit'][$u_count]['bid_keyword_count'] = $v_unit['bid_keyword_count'];
                            ++$u_count;
                        }
                    }
                    ++$p_count;
                }
            }
            ++$count;
        }

        return $res;
    }

    //计算剩余消耗天数
    public function get_consume_days($balance,$user_id)
    {
        if(empty($user_id))
            return '';
        if(empty($balance)) 
            return '0';

        //获取7日消费均值,昨日到前7日
        $yes_day = date('Y-m-d',strtotime("-1 days"));
        $last_seven_day = date('Y-m-d',strtotime("-7 days"));
        $query = $this->CI->user_stat_model->avg_consume($user_id,$last_seven_day,$yes_day);
        if(empty($query[0]['cost']))
            return '';

        return ceil($balance/$query[0]['cost']);
    }
    
    //计算ip个数
    public function get_ip_count($exclude_ip)
    {
        if(empty($exclude_ip))
            return 0;

        return count(explode(',',$exclude_ip));
    }

    //ip列表
    public function get_ip($exclude_ip)
    {
        if(empty($exclude_ip))
            return array();

        return explode(',',trim($exclude_ip));
    }
    
    //获取用户时间范围内的总体数据
    public function user_stat_summary($user_id,$s_date,$e_date)
    {
        $this->CI->load->helper('number_format');
        $res = array('impression'=>'0','click'=>'0','cost'=>'0.00','click_rate'=>'0.00','average_click'=>'0.00');
        if(empty($user_id) || empty($s_date) || empty($e_date))
            return $res;

        $query = $this->CI->user_stat_model->summary($user_id,$s_date,$e_date);
        $summary = $query[0];
        
        $res['impression'] = empty($summary['impression']) ? '0' : num_format($summary['impression']);
        $res['click'] = empty($summary['click']) ? '0' : num_format($summary['click']);
        $res['cost'] = empty($summary['cost']) ? '0.00' : float_format($summary['cost']);
        $res['click_rate'] = empty($summary['impression']) ? '0.00' : float_format($summary['click']/$summary['impression']*100);
        $res['average_click'] = empty($summary['click']) ? '0.00' : float_format($summary['cost']/$summary['click']);

        return $res;
    }

    //获取用户时间范围内的详细数据
    public function user_stat_detail($user_id,$s_date,$e_date)
    {
        $this->CI->load->helper('number_format');
        $res = array();
        if(empty($user_id) || empty($s_date) || empty($e_date))
            return $res;

        $query = $this->CI->user_stat_model->detail($user_id,$s_date,$e_date);
        $stat = empty($query) ? array() : $query;

        foreach($stat as $value)
        {
            $res[$value['date']]['impression'] = empty($value['impression']) ? '0' : num_format($value['impression']);
            $res[$value['date']]['click'] = empty($value['click']) ? '0' : num_format($value['click']);
            $res[$value['date']]['cost'] = empty($value['cost']) ? '0.00' : float_format($value['cost']);
            $res[$value['date']]['click_rate'] = empty($value['impression']) ? '0.00' : float_format($value['click']/$value['impression']*100);
            $res[$value['date']]['average_click'] = empty($value['click']) ? '0.00' : float_format($value['cost']/$value['click']);
        }

        return $res;
    }

    //修改用户信息
    public function modify($user_id,array $data)
    {
        if(empty($user_id) || empty($data))
            return FALSE;
        
        $condition = array('user_id'=>$user_id);
        return $this->CI->user_info_model->modify($data,$condition);
    }

    //sem api 修改用户信息
    public function sem_modify(array $data)
    {
        if(empty($data))
            return array(FALSE,'11','');
        
        $this->CI->load->library('baidu_service');
        $sem_res = $this->CI->baidu_service->baidu_user_modify($data);
        $arr = json_decode($sem_res,TRUE);
        if(empty($arr))
            return array(FALSE,'10','service return error');
        if($arr['header']['error_code'] == '0')
            return array(TRUE,'','');

        return array(FALSE,$arr['header']['error_code'],$arr['header']['error_msg']);

    }

    //校验ip
    public function check_ip_list(array $ip_list)
    {
        if(empty($ip_list))
            return TRUE;
        
        if(count($ip_list) > 33)
            return FALSE;

        $advance_ip = 0;
        $general_ip = 0;
        foreach($ip_list as $ip)
        {
            $arr = explode('.',$ip);
            if(count($arr) != 4)
                return FALSE;
            if(!is_numeric($arr[0]) || $arr[0] > 255 || $arr[0] < 1)
                return FALSE;
            if(!is_numeric($arr[1]) || $arr[1] > 255 || $arr[1] < 1)
                return FALSE;
            //ip第三位
            if($arr[2] == "*")
            {
                if($arr[3] != "*")
                    return FALSE;
                ++$advance_ip;
            }
            else if(!is_numeric($arr[2]) || $arr[2] > 255 || $arr[2] < 1)
                return FALSE;
            //ip第四位
            if($arr[3] == "*")
            {
                if($arr[2] != "*")
                    ++$general_ip;
            }
            else if(!is_numeric($arr[3]) || $arr[3] > 255 || $arr[3] < 1)
                return FALSE;

            if($advance_ip > 3 || $general_ip > 30)
                return FALSE;

            return TRUE;
        }
            
    }

    //sem api 获取用户信息,包括初始化和更新账户
    public function sem_sync(array $data)
    {
        if(empty($data))
            return array(FALSE,'11','');
        
        $this->CI->load->library('baidu_service');
        $sem_res = $this->CI->baidu_service->baidu_user_sync($data);
        $arr = json_decode($sem_res,TRUE);
        if(empty($arr))
            return array(FALSE,'10','service return error');
        if($arr['header']['error_code'] == '0')
            return array(TRUE,'','');

        return array(FALSE,$arr['header']['error_code'],$arr['header']['error_msg']);

    }

    //获取用户白名单数量
    public function white_count($user_id)
    {
        if(empty($user_id))
            return 0;

        $res = $this->CI->user_whitelist_model->white_count($user_id);

        return empty($res[0]['count']) ? 0 : $res[0]['count'];
    }

    //添加白名单
    public function add_whitelist($user_id,$white_list)
    {
        if(empty($user_id) || empty($white_list))
            return FALSE;

        return $this->CI->user_whitelist_model->add($user_id,$white_list);
    }

    //删除白名单
    public function del_whitelist($user_id,$white_list)
    {
        if(empty($user_id) || empty($white_list))
            return FALSE;

        return $this->CI->user_whitelist_model->del($user_id,$white_list);
    }

    //获取用户白名单
    public function get_whitelist($user_id)
    {
        $res = array();
        if(empty($user_id))
            return $res;

        $list = $this->CI->user_whitelist_model->get($user_id);
        if(empty($list))
            return $res;

        foreach($list as $value)
            $res[] = $value['white_domain'];

        return $res;
    }

    public function plan_area_belong_user($user_id)
    {
        if(empty($user_id))
            return array();

        $col = "plan_id,region";
        $condition = array('user_id'=>$user_id,'region'=>'');

        return $this->CI->plan_model->get($condition,$col,'plan_id');

    }
}


