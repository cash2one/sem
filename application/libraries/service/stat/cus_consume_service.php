<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cus_consume_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('user_stat_model');
        $this->CI->load->model('account_bind_model');
        $this->CI->load->model('agent_user_info_model');
	}

    //获取用户消费
    public function get_consume($s_date,$e_date,$threshold)
    {
        if(empty($s_date) || empty($e_date) || empty($threshold))
            return array();

        $consume = array();
        //取六个库的数据
        for($count = 0;$count < SWAN_DB_COUNT;++$count)
        {
            $branch_data = $this->CI->user_stat_model->get_consume($s_date,$e_date,$threshold,$count); 
            $consume = array_merge($consume,$branch_data);
        }
        
        $res = array();
        $whitelist = $this->CI->config->item('stat_cus_whitelist');
        foreach($consume as $value)
        {
            if(!in_array($value['baidu_id'],$whitelist))
                $res[$value['baidu_id']] = $value;
        }

        return $res;
    }

    //获取海智用户信息
    public function get_info(array $ids)
    {
        if(empty($ids))
            return array();

        $info = $this->CI->account_bind_model->hzuser_level($ids);
        if(empty($info))
            return array();
        //筛选数据
        $res = array();
        $agent_ids = array();
        $count = 0;
        foreach($info as $value)
        {
            $res[$count]['baidu_id'] = $value['baidu_id'];
            $res[$count]['name'] = $value['name'];
            $res[$count]['ctime'] = $value['ctime'];
            list($belong_agency,$belong_branch,$belong) = $this->_get_super($value);
            $res[$count]['agency'] = $belong_agency;
            $res[$count]['branch'] = $belong_branch;
            $res[$count]['agent'] = $belong;
            $agent_ids[] = $belong_agency;
            $agent_ids[] = $belong;
            $agent_ids[] = $belong_branch;
            ++$count;
        }
        unset($info);
        //获取代理，分公司的名称
        $agent = $this->CI->agent_user_info_model->get(array_unique($agent_ids),'userid,company_name');
        $this->CI->load->helper('array_util');
        $agent_name = change_data_key($agent,'userid');
        
        foreach($res as $key=>$value)
        {
            $res[$key]['agency'] = $agent_name[$value['agency']]['company_name'];
            $res[$key]['branch'] = $agent_name[$value['branch']]['company_name'];
            $res[$key]['agent'] = $agent_name[$value['agent']]['company_name'];
        }
        return $res;
    }

    private function _get_super($data)
    {
        if(empty($data))
            return array();
        if($data['agent_id'] == 0)
        {
            if($data['branch_five'] != 0)
            {
                $belong = $data['branch_five'];
                $belong_branch = $data['branch_five'];
            }
            else if($data['branch_four'] != 0)
            {
                $belong = $data['branch_four'];
                $belong_branch = $data['branch_four'];
            }
            else if($data['branch_three'] != 0)
            {
                $belong = $data['branch_three'];
                $belong_branch = $data['branch_three'];
            }
            else if($data['branch_two'] != 0)
            {
                $belong = $data['branch_two'];
                $belong_branch = $data['branch_two'];
            }
            else if($data['branch_one'] != 0)
            {
                $belong = $data['branch_one'];
                $belong_branch = $data['branch_one'];
            }
            else
            {
                $belong = $data['agency_id'];
                $belong_branch = $data['agency_id'];
            }
            $belong_agency = $data['agency_id'];
        }
        else
        {
            $belong = $data['agent_id'];
            $belong_agency = $data['agency_id'];
            if($data['branch_five'] != 0)
            {
                $belong_branch = $data['branch_five'];
            }
            else if($data['branch_four'] != 0)
            {
                $belong_branch = $data['branch_four'];
            }
            else if($data['branch_three'] != 0)
            {
                $belong_branch = $data['branch_three'];
            }
            else if($data['branch_two'] != 0)
            {
                $belong_branch = $data['branch_two'];
            }
            else if($data['branch_one'] != 0)
            {
                $belong_branch = $data['branch_one'];
            }
            else
            {
                $belong_branch = $data['agency_id'];
            }
        }
        return array($belong_agency,$belong_branch,$belong);
    }

    public function combian($consume,$level_info)
    {
        if(empty($consume) || empty($level_info))
            return array();

        foreach($level_info as $key=>$value)
        {
            $level_info[$key]['consume'] = intval($consume[$value['baidu_id']]['avg_consume']);
        }
        return $level_info;
    }
}


