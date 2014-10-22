<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Status_report_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('user_stat_model');
        $this->CI->load->model('enterprise_user_model');
	}

    //获取用户状态
    public function get_cus_status()
    {
        $res = $this->CI->enterprise_user_model->sem_user_status();

        return empty($res) ? array() : $res;
    }

    //用户竞价相关数据
    public function get_bid_info()
    {
        $res = array();
        $this->CI->load->model('user_stat_model');
        $data = array();
        for($i=0;$i<SWAN_DB_COUNT;++$i)
        {
            $data_part = $this->CI->user_stat_model->bid_info($i);
            $data = array_merge($data,$data_part);
        }
        foreach($data as $value)
        {
            $res[$value['baidu_id']] = $value;
        }
        unset($data);
        return $res;
    }

    //聚合
    public function aggregate($cus_status,$user_info,$bid_info)
    {
        if(empty($cus_status))
            return array();

        $res = array();
        $count = 0;
        //echo json_encode($cus_status);exit;
        //echo json_encode($user_info);exit;
        //echo json_encode($bid_info);exit;
        foreach($cus_status as $value)
        {
            $res[$count]['username'] = $value['username'];
            $res[$count]['name'] = $value['name'];
            $res[$count]['ctime'] = $value['ctime'];
            $res[$count]['last_login'] = $value['last_login'];
            if($value['status'] != '3')
            {
                $res[$count]['status'] = $this->_status($value['status']);
                $res[$count]['plan_count'] = '-';
                $res[$count]['unit_count'] = '-';
                $res[$count]['keyword_count'] = '-';
                $res[$count]['user_avg_cost'] = '-';
                $res[$count]['biding_count'] = '-';
                $res[$count]['bided_count'] = '-';
                $res[$count]['biding_cost_rat'] = '-';
                $res[$count]['bided_cost_rat'] = '-';
            }
            else
            {
                $baidu_id = $value['baidu_id'];
                if(empty($bid_info[$baidu_id]['biding_count']) && empty($bid_info[$baidu_id]['bided_count']))
                    $res[$count]['status'] = $this->_status('4');
                else
                    $res[$count]['status'] = $this->_status('5');

                $res[$count]['plan_count'] = $user_info[$baidu_id]['plan_count'];
                $res[$count]['unit_count'] = $user_info[$baidu_id]['unit_count'];
                $res[$count]['keyword_count'] = $user_info[$baidu_id]['keyword_count'];
                $res[$count]['user_avg_cost'] = $this->_convert_float($bid_info[$baidu_id]['user_avg_cost']);
                $res[$count]['biding_count'] = $this->_convert_null($bid_info[$baidu_id]['biding_count']);
                $res[$count]['bided_count'] = $this->_convert_null($bid_info[$baidu_id]['bided_count']);
                $res[$count]['biding_cost_rat'] = $this->_divide($bid_info[$baidu_id]['biding_avg_cost'],$bid_info[$baidu_id]['user_avg_cost']);
                $res[$count]['bided_cost_rat'] = $this->_divide($bid_info[$baidu_id]['bided_avg_cost'],$bid_info[$baidu_id]['user_avg_cost']);
                
            }
            ++$count;
        }
        
        return $res;
    }

    private function _status($code)
    {
        if(empty($code))
            return '未知';

        switch($code)
        {
            case 1:
                return '开户未登录';
                break;
            case 2:
                return '已登录未绑定';
                break;
            case 4:
                return '已绑定未竞价';
                break;
            case 5:
                return '激活竞价';
                break;
            default :
                return '未知';
        }
    }

    private function _convert_null($data)
    {
        if(empty($data))
            return '0';

        return $data;
    }

    private function _convert_float($data)
    {
        $this->CI->load->helper('number_format');

        $data = empty($data) ? 0 : $data;

        return float_format2($data);
    }

    private function _divide($num1,$num2,$tag='%')
    {
        $this->CI->load->helper('number_format');
        $num1 = empty($num1) ? 0 : $num1;
        $num2 = empty($num2) ? 0 : $num2;
        if(empty($num2))
            return '-';
        if($tag == "%")
            return float_format2(($num1/$num2)*100).$tag;
        else
            return float_format2($num1/$num2).$tag;
    }
}


