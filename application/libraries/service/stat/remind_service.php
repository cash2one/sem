<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Remind_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('account_bind_model');
	}

    public function get_user($dead_date)
    {
        if(empty($dead_date))
            return array();

        $col = array('user_id','baidu_id','last_login','mobile');
        return $this->CI->account_bind_model->get_login_deadline($dead_date,$col);
    }

    public function calc_work_date($f_date)
    {
        if(empty($f_date))
            return 0;

        $count = 0;
        $f_stamp = strtotime($f_date);
        $e_stamp = strtotime(date('Y-m-d',time()));

        while($f_stamp < $e_stamp)
        {
            $arr = getdate($f_stamp);
            //是否为周末
            if(!in_array($arr['weekday'],array('Saturday','Sunday')))
            {
                //是否为法定休休假日
                if(!$this->_is_holiday($f_stamp))
                {
                    ++$count;
                }
            }
            $f_stamp += 24*3600;
        }
    
        return $count;
    }

    public function _is_holiday($stamp)
    {
        if(empty($stamp))  
            return FALSE;

        $this->CI->load->config('holiday_conf');
        $holiday = $this->CI->config->item('holiday_day');
        
        return in_array(date('Y-m-d',$stamp),$holiday);
    }

    public function pause($pause_user,$pause_baidu_user)
    {
        if(empty($pause_user) || empty($pause_baidu_user))
            return FALSE;
    
        $this->CI->load->model('enterprise_sem_user_model');
        $sem_update = array('uninterruptible'=>'0');
        $this->CI->enterprise_sem_user_model->update($pause_user,$sem_update);

        $this->CI->load->model('keyword_model');
        $params = array('bid_status'=>'2','user_id'=>$pause_baidu_user);
        $update_data = array('bid_status'=>'3');
        for($i=0;$i<SWAN_DB_COUNT;++$i)
        {
            $this->CI->keyword_model->update($i,$params,$update_data);
        }
    }
}


