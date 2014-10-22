<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Day_report_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('user_stat_model');
        $this->CI->load->model('account_bind_model');
        $this->CI->load->model('agent_user_info_model');
        $this->CI->load->model('enterprise_sem_user_model');
	}


    public function get_agency_info($date, $req_agency_id)
    {
        if(empty($date))
            return array();
        $res = array();
        $info = array();

        if ($req_agency_id === 0) {
            $info = $this->CI->enterprise_sem_user_model->get_agency_info($date);
        } else {
            $info = $this->CI->enterprise_sem_user_model->get_agent_info($date,$req_agency_id);
        }

        if(empty($info)) {return array();}

        //处理返回数据
        foreach($info as $value)
        {
            $id = $value['user_id'];
            if(array_key_exists($id,$res))
            {
                $res[$id]['baidu_id'][] = $value['baidu_id'];
            }
            else
            {
                $res[$id]['user_id'] = $id;
                // 代理商
                if ($req_agency_id === 0) {
                    $res[$id]['name'] = $value['company_name'];
                // 客服
                } else {
                    $res[$id]['name'] = empty($value['contact'])?$value['username']:$value['contact'];
                }
                $res[$id]['areas'] = $value['areas'];
                $res[$id]['add_admin_id'] = $value['add_admin_id'];
                $res[$id]['customer_amount'] = $value['customer_count'];
                $res[$id]['new_add_user'] = empty($value['new_add_user']) ? '0' : $value['new_add_user'];
                $res[$id]['today_login_count'] = empty($value['today_login_count']) ? '0' : $value['today_login_count'];
                $res[$id]['uninterruptible_amount'] = empty($value['uninterruptible_amount']) ? '0' : $value['uninterruptible_amount'];
                $res[$id]['baidu_id'][] = $value['baidu_id'];
            }
        }
        unset($info);
        return $res;
    }


    public function get_agency_ids()
    {
        $agency_ids = array();
        $info = $this->CI->enterprise_sem_user_model->agency_ids();
        foreach($info as $item) {
            $agency_ids[] = $item['agency_id'];
        }
        return $agency_ids;
    }


    public function get_branch_info($date, $branch_level)
    {
        if(empty($date)) {return array();}
        $res = array();
        $info = array();
        
        $info = $this->CI->enterprise_sem_user_model->branch_info(
            $date,$branch_level);

        if(empty($info)) {return array();}

        foreach($info as $value) {
            $id = $value['user_id'];
            if(array_key_exists($id,$res)) {
                $res[$id]['baidu_id'][] = $value['baidu_id'];
            } else {
                $res[$id]['user_id'] = $id;
                $res[$id]['name'] = $value['company_name'];
                $res[$id]['areas'] = $value['areas'];
                $res[$id]['add_admin_id'] = $value['add_admin_id'];
                $res[$id]['customer_amount'] = $value['customer_count'];
                $res[$id]['new_add_user'] = empty($value['new_add_user']) ? '0' : $value['new_add_user'];
                $res[$id]['today_login_count'] = empty($value['today_login_count']) ? '0' : $value['today_login_count'];
                $res[$id]['uninterruptible_amount'] = empty($value['uninterruptible_amount']) ? '0' : $value['uninterruptible_amount'];
                $res[$id]['baidu_id'][] = $value['baidu_id'];
            }
        }
        unset($info);
        return $res;
    }


    public function aggregate4admin($admin_info, $agency_data, $date)
    {
        if(empty($admin_info) OR empty($agency_data)) {return array();}
        $res = array();
        
        $agency = array();
        $admin = array();
        foreach($agency_data as $each) {
            $agency[$each['agency_id']] = $each;
        }
        
        foreach($admin_info as $each) {
            $admin[$each['admin_id']]['admin_id'] = $each['admin_id'];
            $admin[$each['admin_id']]['admin_name'] = $each['admin_name'];
            $admin[$each['admin_id']]['agency_ids'][] = $each['agency_id'];
        }

        $index = 0;
        $whitelist = $this->CI->config->item('stat_daily_whitelist');
        foreach($admin as $each) {
            // 过滤测试帐号
            if (in_array($each['admin_id'], $whitelist)) {
                continue;
            }
            $res[$index]['stat_date'] = $date;
            $res[$index]['admin_id'] = $each['admin_id'];
            $res[$index]['name'] = $each['admin_name'];
            // 开启24小时竞价客户数
            $res[$index]['uninterruptible_amount'] = $this->_sum(
                $each['agency_ids'], $agency, 'uninterruptible_amount');
            // 总注册客户数
            $res[$index]['customer_amount'] = $this->_sum(
                $each['agency_ids'], $agency, 'customer_amount');
            // 登录绑定客户数
            $res[$index]['bind_customer_amount'] = $this->_sum(
                $each['agency_ids'], $agency, 'bind_customer_amount');
            // 激活竞价客户数
            $res[$index]['activate_bid_amount'] = $this->_sum(
                $each['agency_ids'], $agency, 'activate_bid_amount');
            // 当日新增客户数
            $res[$index]['new_add_user'] = $this->_sum(
                $each['agency_ids'], $agency, 'new_add_user');
            // 当日活跃用户数
            $res[$index]['today_active_amount'] = $this->_sum(
                $each['agency_ids'], $agency, 'today_active_amount');
            // 当日登录客户数
            $res[$index]['today_login_count'] = $this->_sum(
                $each['agency_ids'],$agency,'today_login_count');
            // 核心用户数
            $res[$index]['core_user_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'core_user_amount');
            // 核心用户比例
            $res[$index]['core_user_rate'] = $this->_divide(
                $res[$index]['core_user_amount'], 
                $res[$index]['customer_amount'],'%');
            // 失效客户数
            $res[$index]['lost_user_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'lost_user_amount');
            // 流失客户比例
            $res[$index]['lost_user_rate'] = $this->_divide(
                $res[$index]['lost_user_amount'],
                $res[$index]['activate_bid_amount'],'%');
            // 推广关键词数
            //$res[$index]['keyword_amount'] = $this->_sum(
            //    $each['agency_ids'],$agency,'keyword_amount');
            // 当日活跃用户的推广关键词数
            $res[$index]['keyword_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'keyword_amount');
            // 当日推广消费
            $res[$index]['cost'] = $this->_sum(
                $each['agency_ids'],$agency,'cost');
            // 启用关键词数
            $res[$index]['bid_keyword_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_keyword_amount');
            // 锁定对手的关键词数
            $res[$index]['bid_lock_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_lock_amount');
            // 竞价暂停的关键词数
            $res[$index]['bid_pause_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_pause_amount');
            // 展现次数
            $res[$index]['bid_impression_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_impression_amount');
            $res[$index]['impression_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'impression_amount');
            // 展现占比
            $res[$index]['bid_impression_ratio'] = $this->_divide(
                $res[$index]['bid_impression_amount'],
                $res[$index]['impression_amount'],'%');
            // 点击次数
            $res[$index]['bid_click_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_click_amount');
            $res[$index]['click_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'click_amount');
            // 点击占比
            $res[$index]['bid_click_ratio'] = $this->_divide(
                $res[$index]['bid_click_amount'],
                $res[$index]['click_amount'],'%');
            // 消费情况
            $res[$index]['bid_cost_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_cost_amount');
            $res[$index]['cost_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'cost_amount');
            // 消费占比
            $res[$index]['bid_cost_ratio'] = $this->_divide(
                $res[$index]['bid_cost_amount'],
                $res[$index]['cost_amount'],'%');
            // 点击率
            $res[$index]['bid_ctr'] = $this->_divide(
                $res[$index]['bid_click_amount'],
                $res[$index]['bid_impression_amount'],'%');
            // 整体点击率
            $res[$index]['ctr'] = $this->_divide(
                $res[$index]['click_amount'],
                $res[$index]['impression_amount'],'%');
            // 平均点击价格
            $res[$index]['bid_acp'] = $this->_divide(
                $res[$index]['bid_cost_amount'],
                $res[$index]['bid_click_amount']);
            // 整体平均点击价格
            $res[$index]['acp'] = $this->_divide(
                $res[$index]['cost_amount'],
                $res[$index]['click_amount']);
            ++$index;
        }
        return $res;
    }
    
    
    public function aggregate_bid_4admin($admin_info, $agency_data, $date)
    {
        if(empty($admin_info) OR empty($agency_data)) {return array();}
        
        $agency = array();
        $admin = array();
        foreach($agency_data as $each) {
            $agency[$each['agency_id']] = $each;
        }
        
        foreach($admin_info as $each) {
            $admin[$each['admin_id']]['admin_id'] = $each['admin_id'];
            $admin[$each['admin_id']]['admin_name'] = $each['admin_name'];
            $admin[$each['admin_id']]['agency_ids'][] = $each['agency_id'];
        }

        $res = array(); 
        $index = 0;
        $whitelist = $this->CI->config->item('stat_daily_whitelist');
        foreach($admin as $each) {
            // 过滤测试帐号
            if (in_array($each['admin_id'], $whitelist)) {
                continue;
            }
            $res[$index]['stat_date'] = $date;
            $res[$index]['admin_id'] = $each['admin_id'];
            // 推广关键词数
            //$res[$index]['keyword_amount'] = $this->_sum(
            //    $each['agency_ids'],$agency,'keyword_amount');
            // 当日推广消费
            $res[$index]['cost'] = $this->_sum(
                $each['agency_ids'],$agency,'cost');
            // 展现次数
            $res[$index]['bid_impression_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_impression_amount');
            $res[$index]['impression_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'impression_amount');
            // 展现占比
            $res[$index]['bid_impression_ratio'] = $this->_divide(
                $res[$index]['bid_impression_amount'],
                $res[$index]['impression_amount'],'%');
            // 点击次数
            $res[$index]['bid_click_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_click_amount');
            $res[$index]['click_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'click_amount');
            // 点击占比
            $res[$index]['bid_click_ratio'] = $this->_divide(
                $res[$index]['bid_click_amount'],
                $res[$index]['click_amount'],'%');
            // 消费情况
            $res[$index]['bid_cost_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'bid_cost_amount');
            $res[$index]['cost_amount'] = $this->_sum(
                $each['agency_ids'],$agency,'cost_amount');
            // 消费占比
            $res[$index]['bid_cost_ratio'] = $this->_divide(
                $res[$index]['bid_cost_amount'],
                $res[$index]['cost_amount'],'%');
            // 点击率
            $res[$index]['bid_ctr'] = $this->_divide(
                $res[$index]['bid_click_amount'],
                $res[$index]['bid_impression_amount'],'%');
            // 整体点击率
            $res[$index]['ctr'] = $this->_divide(
                $res[$index]['click_amount'],
                $res[$index]['impression_amount'],'%');
            // 平均点击价格
            $res[$index]['bid_acp'] = $this->_divide(
                $res[$index]['bid_cost_amount'],
                $res[$index]['bid_click_amount']);
            // 整体平均点击价格
            $res[$index]['acp'] = $this->_divide(
                $res[$index]['cost_amount'],
                $res[$index]['click_amount']);
            ++$index;
        }
        return $res;
    }
    
    public function aggregate4sadmin($admin_info, $agency_data, $date)
    {
        if(empty($admin_info) OR empty($agency_data)) {return array();}
        $res = array();
        
        $agency = array();
        $sadmin = array();
        foreach($agency_data as $each) {
            $agency[$each['agency_id']] = $each;
        }

        foreach($admin_info as $each) {
            $sadmin['agency_ids'][] = $each['agency_id'];
        }

        $res[0]['stat_date'] = $date;
        // 开启24小时竞价客户数
        $res[0]['uninterruptible_amount'] = $this->_sum(
            $sadmin['agency_ids'], $agency, 'uninterruptible_amount');
        // 总注册客户数
        $res[0]['customer_amount'] = $this->_sum(
            $sadmin['agency_ids'], $agency, 'customer_amount');
        // 登录绑定客户数
        $res[0]['bind_customer_amount'] = $this->_sum(
            $sadmin['agency_ids'], $agency, 'bind_customer_amount');
        // 激活竞价客户数
        $res[0]['activate_bid_amount'] = $this->_sum(
            $sadmin['agency_ids'], $agency, 'activate_bid_amount');
        // 当日新增客户数
        $res[0]['new_add_user'] = $this->_sum(
            $sadmin['agency_ids'], $agency, 'new_add_user');
        // 当日活跃用户数
        $res[0]['today_active_amount'] = $this->_sum(
            $sadmin['agency_ids'], $agency, 'today_active_amount');
        // 当日登录客户数
        $res[0]['today_login_count'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'today_login_count');
        // 核心用户数
        $res[0]['core_user_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'core_user_amount');
        // 核心用户比例
        $res[0]['core_user_rate'] = $this->_divide(
            $res[0]['core_user_amount'], 
            $res[0]['customer_amount'],'%');
        // 失效客户数
        $res[0]['lost_user_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'lost_user_amount');
        // 流失客户比例
        $res[0]['lost_user_rate'] = $this->_divide(
            $res[0]['lost_user_amount'],
            $res[0]['activate_bid_amount'],'%');
        // 推广关键词数
        //$res[0]['keyword_amount'] = $this->_sum(
        //    $sadmin['agency_ids'],$agency,'keyword_amount');
        // 当日活跃用户的推广关键词数
        $res[0]['keyword_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'keyword_amount');
        // 当日推广消费
        $res[0]['cost'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'cost');
        // 启用关键词数
        $res[0]['bid_keyword_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_keyword_amount');
        // 锁定对手的关键词数
        $res[0]['bid_lock_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_lock_amount');
        // 竞价暂停的关键词数
        $res[0]['bid_pause_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_pause_amount');
        // 展现次数
        $res[0]['bid_impression_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_impression_amount');
        $res[0]['impression_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'impression_amount');
        // 展现占比
        $res[0]['bid_impression_ratio'] = $this->_divide(
            $res[0]['bid_impression_amount'],
            $res[0]['impression_amount'],'%');
        // 点击次数
        $res[0]['bid_click_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_click_amount');
        $res[0]['click_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'click_amount');
        // 点击占比
        $res[0]['bid_click_ratio'] = $this->_divide(
            $res[0]['bid_click_amount'],
            $res[0]['click_amount'],'%');
        // 消费情况
        $res[0]['bid_cost_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_cost_amount');
        $res[0]['cost_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'cost_amount');
        // 消费占比
        $res[0]['bid_cost_ratio'] = $this->_divide(
            $res[0]['bid_cost_amount'],
            $res[0]['cost_amount'],'%');
        // 点击率
        $res[0]['bid_ctr'] = $this->_divide(
            $res[0]['bid_click_amount'],
            $res[0]['bid_impression_amount'],'%');
        // 整体点击率
        $res[0]['ctr'] = $this->_divide(
            $res[0]['click_amount'],
            $res[0]['impression_amount'],'%');
        // 平均点击价格
        $res[0]['bid_acp'] = $this->_divide(
            $res[0]['bid_cost_amount'],
            $res[0]['bid_click_amount']);
        // 整体平均点击价格
        $res[0]['acp'] = $this->_divide(
            $res[0]['cost_amount'],
            $res[0]['click_amount']);
        return $res;
    }
    
    
    public function aggregate_bid_4sadmin($admin_info, $agency_data, $date)
    {
        if(empty($admin_info) OR empty($agency_data)) {return array();}
        
        $agency = array();
        $sadmin = array();
        foreach($agency_data as $each) {
            $agency[$each['agency_id']] = $each;
        }

        foreach($admin_info as $each) {
            $sadmin['agency_ids'][] = $each['agency_id'];
        }

        $res = array();
        $res[0]['stat_date'] = $date;
        // 推广关键词数
        //$res[0]['keyword_amount'] = $this->_sum(
        //    $sadmin['agency_ids'],$agency,'keyword_amount');
        // 当日推广消费
        $res[0]['cost'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'cost');
        // 展现次数
        $res[0]['bid_impression_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_impression_amount');
        $res[0]['impression_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'impression_amount');
        // 展现占比
        $res[0]['bid_impression_ratio'] = $this->_divide(
            $res[0]['bid_impression_amount'],
            $res[0]['impression_amount'],'%');
        // 点击次数
        $res[0]['bid_click_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_click_amount');
        $res[0]['click_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'click_amount');
        // 点击占比
        $res[0]['bid_click_ratio'] = $this->_divide(
            $res[0]['bid_click_amount'],
            $res[0]['click_amount'],'%');
        // 消费情况
        $res[0]['bid_cost_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'bid_cost_amount');
        $res[0]['cost_amount'] = $this->_sum(
            $sadmin['agency_ids'],$agency,'cost_amount');
        // 消费占比
        $res[0]['bid_cost_ratio'] = $this->_divide(
            $res[0]['bid_cost_amount'],
            $res[0]['cost_amount'],'%');
        // 点击率
        $res[0]['bid_ctr'] = $this->_divide(
            $res[0]['bid_click_amount'],
            $res[0]['bid_impression_amount'],'%');
        // 整体点击率
        $res[0]['ctr'] = $this->_divide(
            $res[0]['click_amount'],
            $res[0]['impression_amount'],'%');
        // 平均点击价格
        $res[0]['bid_acp'] = $this->_divide(
            $res[0]['bid_cost_amount'],
            $res[0]['bid_click_amount']);
        // 整体平均点击价格
        $res[0]['acp'] = $this->_divide(
            $res[0]['cost_amount'],
            $res[0]['click_amount']);
        return $res;
    }


    //推广数据
    public function get_user_info($date)
    {
        if(empty($date))
            return array();

        $res = array();
        $this->CI->load->model('user_info_model');
        $data = array();
        for($i=0;$i<SWAN_DB_COUNT;++$i)
        {
            $data_part = $this->CI->user_info_model->user_count($date,$i);
            $data = array_merge($data,$data_part);
        }
        foreach($data as $value)
        {
            $res[$value['baidu_id']] = $value;
        }
        unset($data);
        return $res;
    }


    //竞价相关数据
    public function get_bid_info()
    {
        $res = array();
        $this->CI->load->model('keyword_model');
        $data = array();
        for($i=0;$i<SWAN_DB_COUNT;++$i)
        {
            $data_part = $this->CI->keyword_model->bid_info($i);
            $data = array_merge($data,$data_part);
        }
        foreach($data as $value)
        {
            $res[$value['baidu_id']] = $value;
        }
        unset($data);
        return $res;
    }


    //竞价关键词效果
    public function get_bid_keyword_stat($s_date,$e_date)
    {
        if(empty($s_date) || empty($e_date))
            return array();

        $res = array();
        $this->CI->load->model('keyword_model');
        $data = array();
        for($i=0;$i<SWAN_DB_COUNT;++$i) {
            $data_part = $this->CI->keyword_model->bid_keyword_stat($s_date,$e_date,$i);
            $data = array_merge($data,$data_part);
        }

        foreach($data as $value) {
            $res[$value['baidu_id']] = $value;
        }
        unset($data);
        return $res;
    }


    /* *
     * 获取激活竞价用户
     *
     * */
    public function get_activate_bid_user()
    {
        $this->CI->load->model('keyword_model');
        $this->CI->load->model('stat_model');

        $activate_user = array();

        $history_activate_user = $this->CI->stat_model->history_activate_user();
        foreach($history_activate_user as $value) {
            $activate_user[$value['baidu_id']] = $value;
        }

        $today_activate_user = array();
        for($i=0; $i<SWAN_DB_COUNT; ++$i) {
            $data_part = $this->CI->keyword_model->activate_bid_info($i);
            $today_activate_user = array_merge($today_activate_user, $data_part);
        }

        foreach($today_activate_user as $value) {
            if (array_key_exists($value['baidu_id'], $activate_user)) {continue;}
            $activate_user[$value['baidu_id']] = $value;
        }
        return $activate_user;
    }


    /* *
     * 获取当天活跃用户
     *
     * */
    public function get_active_user($date)
    {
        if(empty($date)) { return array();}

        $this->CI->load->model('keyword_model');
        $data = array();
        for($i=0; $i<SWAN_DB_COUNT; ++$i) {
            $data_part = $this->CI->keyword_model->active_user($date, $i);
            $data = array_merge($data,$data_part);
        }

        $res = array();
        foreach($data as $value) {
            $res[$value['baidu_id']] = $value;
        }
        return $res;
    }


    /* *
     * 获取非活跃用户
     *      即当日非活跃用户，但3天内有过竞价记录
     *
     */
    public function get_inactive_user($date)
    {
        if(empty($date)) { return array();}

        $this->CI->load->model('keyword_model');
        $active_user_between_days = array();
        $to_date = $date;
        $from_date = date('Y-m-d',strtotime('-3 Day',strtotime($date)));
        for($i=0; $i<SWAN_DB_COUNT; ++$i) {
            $data_part = $this->CI->keyword_model->bid_user($from_date, $to_date, $i);
            $active_user_between_days = array_merge($active_user_between_days,$data_part);
        }

        $today_active_user = $this->get_active_user($date);
        $res = array();
        foreach($active_user_between_days as $value) {
            // 去除当日活跃用户
            if (array_key_exists($value['baidu_id'], $today_active_user)) {continue;}
            $res[$value['baidu_id']] = $value;
        }

        return $res;
    }


    /* *
     * 获取核心用户
     *
     * */
    public function get_core_user(
        $num_active_days, $num_active_keywords, $end_date_r, $start_date_r)
    {
        $this->CI->load->model('keyword_model');
        $data = array();
        for($i=0; $i<SWAN_DB_COUNT; ++$i) {
            $data_part = $this->CI->keyword_model->core_user(
                $num_active_days,$num_active_keywords, $end_date_r,
                $start_date_r, $i);
            $data = array_merge($data,$data_part);
        }

        $today_core_users = array();
        foreach($data as $value) {
            $today_core_users[$value['baidu_id']] = $value;
        }
        
        $history_core_users = $this->_get_history_core_user();
        
        // 合并$history_core_users with $today_core_users。
        // 因为两个array的键都是int值，所以不能使用array_merge。
        $core_users = $today_core_users; 
        foreach ($history_core_users as $key=>$value) {
            if (array_key_exists($key, $core_users)) {continue;}
            $core_users[$key] = $value;
        }

        return $core_users;
    }


    private function _get_history_core_user()
    {
        $this->CI->load->model('stat_model');
        $users = $this->CI->stat_model->history_core_user();
        $history_core_users = array();
        foreach($users as $value) {
            $history_core_users[$value['baidu_id']] = $value;
        }
        return $history_core_users;
    }


    /* *
     * 获取流失的客户
     *      流失客户定义：有过出价记录，但3天内没有竞价记录
     *
     * */
    public function get_lost_user($date)
    {
        $from_date = date('Y-m-d',strtotime('-3 Day',strtotime($date)));
        $to_date = date('Y-m-d',strtotime('+1 Day',strtotime($date)));
        $this->CI->load->model('keyword_model');

        $all_bid_users = array();
        $bid_users_between_date = array();
        for($i=0; $i<SWAN_DB_COUNT; ++$i) {
            $all_bid_user = $this->CI->keyword_model->all_bid_user($date, $i);
            $all_bid_users = array_merge($all_bid_users, $all_bid_user);
            $bid_user_between_date = $this->CI->keyword_model->bid_user($from_date, $to_date, $i);
            $bid_users_between_date = array_merge($bid_users_between_date, $bid_user_between_date);
        }

        $_bid_users_between_date = array();
        foreach($bid_users_between_date as $value) {
            $_bid_users_between_date[$value['baidu_id']] = $value;
        }

        $lost_user = array();
        foreach($all_bid_users as $value) {
            // 过滤3天内有过出价记录的用户
            if (array_key_exists($value['baidu_id'], $_bid_users_between_date)) {
                continue;
            }
            $lost_user[$value['baidu_id']] = $value;
        }

        return  $lost_user;
    }


    //整合数据成报表
    public function aggregate(
        $agency_info,$user_info,$activate_bid_user,$today_active_user,
        $core_user,$lost_user,$bid_info,$bid_keyword_stat,$date,$req_agency_id)
    {
        if(empty($agency_info) || empty($date)) {
            return array();
        }
        
        $res = array();
        $count = 0;
        $whitelist = $this->CI->config->item('stat_daily_whitelist');
        $this->CI->load->helper('number_format');
        foreach($agency_info as $value) {
            // 过滤测试帐号
            if (in_array($value['user_id'],$whitelist)) {
                continue;
            }
            if ($req_agency_id !== 0) {
                $res[$count]['belong_id'] = $req_agency_id;
            } else {
                $res[$count]['belong_id'] = $value['add_admin_id'];;
            }
            $res[$count]['stat_date'] = $date;
            $res[$count]['agency_id'] = $value['user_id'];
            $res[$count]['name'] = $value['name'];
            $res[$count]['areas'] = $value['areas'];
            // 开启24小时竞价客户数
            $res[$count]['uninterruptible_amount'] = 
                $value['uninterruptible_amount'];
            // 总注册客户数
            $res[$count]['customer_amount'] = $value['customer_amount'];
            // 登录绑定客户数
            $res[$count]['bind_customer_amount'] = is_array($value['baidu_id']) ? count($value['baidu_id']) : 0;
            // 激活竞价客户数
            $res[$count]['activate_bid_amount'] = $this->_intersect($value['baidu_id'],$activate_bid_user);
            // 当日新增客户数
            $res[$count]['new_add_user'] = $value['new_add_user'];
            // 当日活跃客户数
            $res[$count]['today_active_amount'] = $this->_intersect($value['baidu_id'],$today_active_user);
            // 当日登录客户数
            $res[$count]['today_login_count'] = $value['today_login_count'];
            // 核心用户数，即达标用户数
            $res[$count]['core_user_amount'] = $this->_intersect($value['baidu_id'],$core_user);
            // 计算核心用户比例，即达标率
            $res[$count]['core_user_rate'] = $this->_divide($res[$count]['core_user_amount'], $res[$count]['customer_amount'],'%');
            
            // 流失客户，即失效客户数
            $res[$count]['lost_user_amount'] = $this->_intersect($value['baidu_id'],$lost_user);
            // 流失客户比例，即失效率
            $res[$count]['lost_user_rate'] = $this->_divide($res[$count]['lost_user_amount'],$res[$count]['activate_bid_amount'],'%');
            
            /*推广数据*/
            //$res[$count]['plan_amount'] = $this->_sum($value['baidu_id'],$user_info,'plan_count');
            //$res[$count]['unit_amount'] = $this->_sum($value['baidu_id'],$user_info,'unit_count');
            // 推广关键词数
            // $res[$count]['keyword_amount'] = $this->_sum($value['baidu_id'],$user_info,'keyword_count');
            // 当日活跃用户的推广关键词数
            $res[$count]['keyword_amount'] = $this->_sum($value['baidu_id'],$user_info,'active_user_keyword_count');
            // 当日推广消费
            $res[$count]['cost'] = float_format2($this->_sum($value['baidu_id'],$user_info,'cost'));
            
            /*竞价数据*/
            //$res[$count]['bid_plan_amount'] = $this->_sum($value['baidu_id'],$bid_info,'plan_count');
            //$res[$count]['bid_unit_amount'] = $this->_sum($value['baidu_id'],$bid_info,'unit_count');
            // 启用关键词数
            $res[$count]['bid_keyword_amount'] = $this->_sum($value['baidu_id'],$bid_info,'keyword_count');
            // 锁定对手的关键词数
            $res[$count]['bid_lock_amount'] = $this->_sum($value['baidu_id'],$bid_info,'lock_count');
            // 竞价暂停的关键词数目
            $res[$count]['bid_pause_amount'] = $this->_sum($value['baidu_id'],$bid_info,'pause_count');

            /*竞价关键词效果情况*/
            ////基础数据
            $bid_impression_amount = $this->_sum($value['baidu_id'],$bid_keyword_stat,'bid_impression');
            $bid_click_amount = $this->_sum($value['baidu_id'],$bid_keyword_stat,'bid_click');
            $bid_cost_amount = $this->_sum($value['baidu_id'],$bid_keyword_stat,'bid_cost');
            $impression_amount = $this->_sum($value['baidu_id'],$bid_keyword_stat,'impression');
            $click_amount = $this->_sum($value['baidu_id'],$bid_keyword_stat,'click');
            $cost_amount = $this->_sum($value['baidu_id'],$bid_keyword_stat,'cost');
            ////结果
            // 展现次数
            $res[$count]['bid_impression_amount'] = $bid_impression_amount;
            // 展现占比
            $res[$count]['bid_impression_ratio'] = $this->_divide($bid_impression_amount,$impression_amount,'%');
            // 点击次数
            $res[$count]['bid_click_amount'] = $bid_click_amount;
            // 点击占比
            $res[$count]['bid_click_ratio'] = $this->_divide($bid_click_amount,$click_amount,'%');
            // 消费情况
            $res[$count]['bid_cost_amount'] = float_format2($bid_cost_amount);
            // 消费占比
            $res[$count]['bid_cost_ratio'] = $this->_divide($bid_cost_amount,$cost_amount,'%');
            // 点击率
            $res[$count]['bid_ctr'] = $this->_divide($bid_click_amount,$bid_impression_amount,'%');
            // 整体点击率
            $res[$count]['ctr'] = $this->_divide($click_amount,$impression_amount,'%');
            // 平均点击价格
            $res[$count]['bid_acp'] = $this->_divide($bid_cost_amount,$bid_click_amount);
            // 整体平均点击价格
            $res[$count]['acp'] = $this->_divide($cost_amount,$click_amount);
            ++$count;
        }
        return $res;
    }


    // 聚合用户数据
    public function aggregate_customer_info(
        $date, $customer_basic_info, $today_active_user,$today_inactive_user,
        $lost_user, $bid_info,$promote_info,$bid_keyword_stat,
        $activate_bid_info,$core_user)
    {
        $res = array();
        $index = 0;
        $this->CI->load->helper('number_format');
        foreach($customer_basic_info as $value)
        {
            $res[$index]['stat_date'] = $date;
            $res[$index]['admin_id'] = $value['add_admin_id']; 
            $res[$index]['agency_id'] = $value['agency_id'];
            $res[$index]['agent_id'] = $value['agent_id'];
            $res[$index]['baidu_id'] = $value['baidu_id'];
            $res[$index]['is_bind'] = empty($value['baidu_id'])?0:1;
            $res[$index]['contact'] = $value['contact'];
            $res[$index]['username'] = $value['username'];
            $res[$index]['name'] = $value['name'];
            $res[$index]['mobile'] = $value['mobile'];
            $res[$index]['email'] = $value['email'];
            $res[$index]['agent_name'] = $value['company_name'];
            $res[$index]['ctime'] = $value['ctime'];
            $res[$index]['last_login'] = $value['last_login']; 
            $res[$index]['status'] = $value['status_product'];
            $res[$index]['active_status'] =$this->_get_active_status(
                $today_active_user,$today_inactive_user,$lost_user,
                $value['baidu_id']);
            // 是否属于达标客户
            $res[$index]['is_core_user'] = array_key_exists($value['baidu_id'], $core_user) ? 1 : 0;
            // 是否开启24小时竞价
            $res[$index]['uninterruptible'] = $value['uninterruptible'];
            // 激活竞价时间信息
            $res[$index]['activate_bid_time'] = $this->_get(
                $activate_bid_info,$value['baidu_id'],'activate_bid_time'); 
            // 推广关键词数
            $res[$index]['keyword_amount'] = $this->_get(
                $promote_info,$value['baidu_id'],'keyword_count');
            // 当日推广消费
            $res[$index]['cost'] = $this->_get(
                $promote_info,$value['baidu_id'],'cost'); 
            // 启用关键词数
            $res[$index]['bid_keyword_amount'] = $this->_get(
                $bid_info,$value['baidu_id'],'keyword_count'); 
            // 锁定对手的关键词数
            $res[$index]['bid_lock_amount'] = $this->_get(
                $bid_info,$value['baidu_id'],'lock_count');
            // 竞价暂停的关键词数目
            $res[$index]['bid_pause_amount'] = $this->_get(
                $bid_info,$value['baidu_id'],'pause_count');

            /*竞价关键词效果统计*/
            $bid_impression_amount = $this->_get(
                $bid_keyword_stat,$value['baidu_id'],'bid_impression');
            $impression_amount = $this->_get(
                $bid_keyword_stat,$value['baidu_id'],'impression');

            $bid_click_amount = $this->_get(
                $bid_keyword_stat,$value['baidu_id'],'bid_click'); 
            $click_amount = $this->_get(
                $bid_keyword_stat,$value['baidu_id'],'click');

            $bid_cost_amount = $this->_get(
                $bid_keyword_stat,$value['baidu_id'],'bid_cost');
            $cost_amount = $this->_get(
                $bid_keyword_stat,$value['baidu_id'],'cost');

            // 展现次数
            $res[$index]['bid_impression_amount'] = $bid_impression_amount;
            $res[$index]['impression_amount'] = $impression_amount;
            // 展现占比
            $res[$index]['bid_impression_ratio'] = $this->_divide(
                $bid_impression_amount,$impression_amount,'%');
            // 点击次数
            $res[$index]['bid_click_amount'] = $bid_click_amount;
            $res[$index]['click_amount'] = $click_amount;
            // 点击占比
            $res[$index]['bid_click_ratio'] = $this->_divide(
                $bid_click_amount,$click_amount,'%');
            // 消费情况
            $res[$index]['bid_cost_amount'] = float_format2($bid_cost_amount);
            $res[$index]['cost_amount'] = float_format2($cost_amount);
            // 消费占比
            $res[$index]['bid_cost_ratio'] = $this->_divide(
                $bid_cost_amount,$cost_amount,'%');
            // 点击率
            $res[$index]['bid_ctr'] = $this->_divide(
                $bid_click_amount,$bid_impression_amount,'%');
            // 整体点击率
            $res[$index]['ctr'] = $this->_divide(
                $click_amount,$impression_amount,'%');
            // 平均点击价格
            $res[$index]['bid_acp'] = $this->_divide(
                $bid_cost_amount,$bid_click_amount);
            // 整体平均点击价格
            $res[$index]['acp'] = $this->_divide($cost_amount,$click_amount);
            ++$index;
        }
        return $res;
    }


    private function _intersect($arr1,$arr2)
    {
        if(!is_array($arr1) || empty($arr1))
            return 0;
        if(!is_array($arr2) || empty($arr2))
            return 0;

        $res = array_intersect($arr1,array_keys($arr2));

        return empty($res) ? 0 : count($res);
    }


    private function _sum($arr1,$arr2,$key)
    {
        if( ! is_array($arr1) OR empty($arr1)) {return -1;}
        if( ! is_array($arr2) OR empty($arr2)) {return -1;}

        $res = 0;
        foreach($arr1 as $value) {
            $tmp = empty($arr2[$value][$key]) ? 0 : $arr2[$value][$key];
            $tmp = $tmp < 0 ? 0 : $tmp;
            $res += $tmp;
        }
        return $res;
    }


    private function _add($num1, $num2)
    {
        $num1 = (empty($num1) OR $num1 < 0) ? 0 : $num1;
        $num2 = (empty($num2) OR $num2 < 0) ? 0 : $num2;
        return $num1 + $num2;
    }


    private function _divide($num1,$num2,$tag='')
    {
        $this->CI->load->helper('number_format');
        $num1 = (empty($num1) OR $num1 < 0) ? 0 : $num1;
        $num2 = (empty($num2) OR $num2 < 0) ? 0 : $num2;
        if(empty($num2))
            return -1;
        if($tag == "%")
            return float_format2(($num1/$num2)*100);
        else
            return float_format2($num1/$num2);
    }


    private function _get($value, $baiduid, $key)
    {
        if (empty($baiduid)) {
            return -1;
        }

        if (empty($value[$baiduid])) {
            return -1;
        }

        return $value[$baiduid][$key];
    }


    private function _get_active_status($today_active_user,
        $today_inactive_user, $lost_user,$baiduid)
    {
        if (empty($baiduid)) {
            return -1;
        }

        // 活跃用户
        if (array_key_exists($baiduid, $today_active_user)) {
            return 1;
        }

        // 非活跃用户
        if (array_key_exists($baiduid, $today_inactive_user)) {
            return 2;
        }

        // 失效客户
        if (array_key_exists($baiduid, $lost_user)) {
            return 3;
        }
        return -1;
    }


    public function aggregate_bid_keyword_stat(
        $agency_info, $bid_keyword_stat,$user_info, $date)
    {
        if(empty($agency_info) || empty($date)) {return array();}
        
        $res = array();
        $count = 0;
        $whitelist = $this->CI->config->item('stat_daily_whitelist');
        $this->CI->load->helper('number_format');
        foreach($agency_info as $value) {
            // 过滤测试帐号
            if (in_array($value['user_id'], $whitelist)) {
                continue;
            }
            $res[$count]['stat_date'] = $date;
            $res[$count]['agency_id'] = $value['user_id'];
            
            // 推广关键词数
            //$res[$count]['keyword_amount'] = $this->_sum(
             //   $value['baidu_id'],$user_info,'keyword_count');
            // 当日推广消费
            $res[$count]['cost'] = float_format2(
                $this->_sum($value['baidu_id'],$user_info,'cost'));
            
            /*竞价关键词效果情况*/
            $bid_impression_amount = $this->_sum(
                $value['baidu_id'],$bid_keyword_stat,'bid_impression');
            $bid_click_amount = $this->_sum(
                $value['baidu_id'],$bid_keyword_stat,'bid_click');
            $bid_cost_amount = $this->_sum(
                $value['baidu_id'],$bid_keyword_stat,'bid_cost');
            $impression_amount = $this->_sum(
                $value['baidu_id'],$bid_keyword_stat,'impression');
            $click_amount = $this->_sum(
                $value['baidu_id'],$bid_keyword_stat,'click');
            $cost_amount = $this->_sum(
                $value['baidu_id'],$bid_keyword_stat,'cost');
            // 展现次数
            $res[$count]['bid_impression_amount'] = $bid_impression_amount;
            $res[$count]['impression_amount'] = $impression_amount;
            // 展现占比
            $res[$count]['bid_impression_ratio'] = $this->_divide(
                $bid_impression_amount,$impression_amount,'%');
            // 点击次数
            $res[$count]['bid_click_amount'] = $bid_click_amount;
            $res[$count]['click_amount'] = $click_amount;
            // 点击占比
            $res[$count]['bid_click_ratio'] = $this->_divide(
                $bid_click_amount,$click_amount,'%');
            // 消费情况
            $res[$count]['bid_cost_amount'] = float_format2($bid_cost_amount);
            $res[$count]['cost_amount'] = float_format2($cost_amount);
            // 消费占比
            $res[$count]['bid_cost_ratio'] = $this->_divide(
                $bid_cost_amount,$cost_amount,'%');
            // 点击率
            $res[$count]['bid_ctr'] = $this->_divide(
                $bid_click_amount,$bid_impression_amount,'%');
            // 整体点击率
            $res[$count]['ctr'] = $this->_divide(
                $click_amount,$impression_amount,'%');
            // 平均点击价格
            $res[$count]['bid_acp'] = $this->_divide(
                $bid_cost_amount,$bid_click_amount);
            // 整体平均点击价格
            $res[$count]['acp'] = $this->_divide($cost_amount,$click_amount);
            ++$count;
        }
        return $res;
    }


    public function aggregate_customer_bid_info(
        $date, $customer_basic_info, $bid_keyword_stat, $promote_info)
    {
        $res = array();
        $index = 0;
        $this->CI->load->helper('number_format');
        foreach($customer_basic_info as $value) {
            $res[$index]['stat_date'] = $date;
            $res[$index]['username'] = $value['username'];

            // 推广关键词数
            //$res[$index]['keyword_amount'] = $this->_get($promote_info,$value['baidu_id'],'keyword_count');
            // 当日推广消费
            $res[$index]['cost'] = $this->_get($promote_info,$value['baidu_id'],'cost'); 
            
            /*竞价关键词效果统计*/
            $bid_impression_amount = $this->_get($bid_keyword_stat,$value['baidu_id'],'bid_impression');
            $impression_amount = $this->_get($bid_keyword_stat,$value['baidu_id'],'impression');

            $bid_click_amount = $this->_get($bid_keyword_stat,$value['baidu_id'],'bid_click'); 
            $click_amount = $this->_get($bid_keyword_stat,$value['baidu_id'],'click');

            $bid_cost_amount = $this->_get($bid_keyword_stat,$value['baidu_id'],'bid_cost');
            $cost_amount = $this->_get($bid_keyword_stat,$value['baidu_id'],'cost');

            // 展现次数
            $res[$index]['bid_impression_amount'] = $bid_impression_amount;
            $res[$index]['impression_amount'] = $impression_amount;
            // 展现占比
            $res[$index]['bid_impression_ratio'] = $this->_divide($bid_impression_amount,$impression_amount,'%');
            // 点击次数
            $res[$index]['bid_click_amount'] = $bid_click_amount;
            $res[$index]['click_amount'] = $click_amount;
            // 点击占比
            $res[$index]['bid_click_ratio'] = $this->_divide($bid_click_amount,$click_amount,'%');
            // 消费情况
            $res[$index]['bid_cost_amount'] = float_format2($bid_cost_amount);
            $res[$index]['cost_amount'] = float_format2($cost_amount);
            // 消费占比
            $res[$index]['bid_cost_ratio'] = $this->_divide($bid_cost_amount,$cost_amount,'%');
            // 点击率
            $res[$index]['bid_ctr'] = $this->_divide($bid_click_amount,$bid_impression_amount,'%');
            // 整体点击率
            $res[$index]['ctr'] = $this->_divide($click_amount,$impression_amount,'%');
            // 平均点击价格
            $res[$index]['bid_acp'] = $this->_divide($bid_cost_amount,$bid_click_amount);
            // 整体平均点击价格
            $res[$index]['acp'] = $this->_divide($cost_amount,$click_amount);
            ++$index;
        }
        return $res;
    }
}

/* End of file. */
