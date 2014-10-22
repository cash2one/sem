<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Quota_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('agent_token_model');
        $this->CI->load->model('agent_stat_model');
	}
    
    public function mcc_base_data($date)
    {
        if(empty($date))
            $date = date('Y-m-d',time());
        $col = "username,balance,user_amount,consume";
        $condition = array('date'=>$date);

        $data = $this->CI->agent_stat_model->get($col,$condition);
        $res = array();
        foreach($data as $value)
            $res[$value['username']] = $value;
        return $res;
    }

    public function mcc_week_data($s_date,$e_date)
    {
        if(empty($s_date) || empty($e_date))
            return array();

        $col = "username,avg(consume) as avg_consume,max(consume) as max_consume,min(consume) as min_consume,sum(consume) as sum_consume";
        $data = $this->CI->agent_stat_model->get_complex($col,$s_date,$e_date);
        $res = array();
        foreach($data as $value)
            $res[$value['username']] = $value;
        return $res;
    }

    public function mcc_month_data($s_date,$e_date)
    {
        if(empty($s_date) || empty($e_date))
            return array();

        $col = "username,avg(consume) as avg_consume";
        $data = $this->CI->agent_stat_model->get_complex($col,$s_date,$e_date);
        $res = array();
        foreach($data as $value)
            $res[$value['username']] = $value;
        return $res;
    }

    //mcc账户下的用户数
    public function user_calc()
    {
        $data = $this->CI->agent_token_model->get_user_count();
        return $data;
    }

    public function combian($base,$date,$week_data=array(),$month_data=array())
    {
        $res = array();
        if(empty($base) || empty($date))
            return $res;

        $count = 0;
        foreach($base as $value)
        {
            $name = $value['username'];
            $res[$count]['username'] = $value['username'];
            $res[$count]['user_amount'] = '';
            $res[$count]['consume'] = $value['consume'];
            $res[$count]['month_avg'] = (int)$month_data[$name]['avg_consume'];
            $res[$count]['week_avg'] = (int)$week_data[$name]['avg_consume'];
            $res[$count]['week_max'] = $week_data[$name]['max_consume'];
            $res[$count]['week_min'] = $week_data[$name]['min_consume'];
            $res[$count]['predict_user_amount'] = $this->_predict_user_calc($value['balance'],$date);
            $res[$count]['week_sum'] = $week_data[$name]['sum_consume'];
            $res[$count]['week_balance'] = $value['balance'];
            $res[$count]['status'] = $this->_status_calc($value['balance'],$week_data[$name],$date);
            ++$count;
        }
        return $res;
    }

    //计算预计可支持用户数
    private function _predict_user_calc($balance,$date)
    {
        if(empty($balance) || empty($date))
            return 0;

        $day = $this->_residue_calc($date);

        return (int)($balance/$day/40000);
    }

    //计算本周剩余天数
    private function _residue_calc($date)
    {
        if(empty($date))
            return 1;

        $next_monday = strtotime("next Monday");
        $day = (int)(($next_monday - strtotime($date))/(24*3600));
        
        return ($day == 0) ? 1 : $day;
    }
    //计算mcc额度使用状态
    private function _status_calc($balance,$week_data,$date)
    {
        if(!isset($balance) || empty($week_data) || empty($date))
            return "未知";

        $day = $this->_residue_calc($date);
        $compare = (int)($balance/$day);

        if($compare > $week_data['max_consume'])
            return "富余";
        else if($compare > $week_data['avg_consume'])
            return "正常";
        else if($compare > $week_data['min_consume'])
            return "紧张";
        else
            return "紧急";
        
    }

    public function send_email($body,$date)
    {
        if(empty($body))
            return FALSE;
        
        $this->CI->load->library('email');

        $config['protocol'] = 'smtp';
        $config['charset'] = 'utf-8';
        $config['mailtype'] = 'html';
        $config['smtp_host'] = 'mail.haizhi.com';
        $config['smtp_user'] = 'server';
        $config['smtp_pass'] = 'hzwj1234';
        $config['smtp_port'] = '25';
        $this->CI->email->initialize($config);

        $this->CI->email->from(EMAIL_FROM);
        $this->CI->email->to(EMAIL_TO); 
        $this->CI->email->cc(EMAIL_CC); 
        $this->CI->email->subject(EMAIL_SUBJECT.$date);
        $this->CI->email->message($body); 
        $this->CI->email->send();
        
        return TRUE;
    }
}


