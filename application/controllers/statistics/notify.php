<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  短信邮件通知机制
 *  对于未激活的客户，根据注册时间发送不同的邮件或短信
*/
class Notify extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        //只允许CLI方式运行
        if(! $this->input->is_cli_request())
        {
            //show_error('403 forbidden : no access to visit',403);
            //exit ;
        }

        $this->load->model('enterprise_user_model');
        $this->load->library('service/stat/remind_service');
	}

    public function mails($first = FALSE)
    {
        $res = array('5'=>array(),'13'=>array(),'29'=>array());
        //获取未登陆过的客户
        $this->load->model('enterprise_user_model');
        $user_info = $this->enterprise_user_model->get_user_not_login();

        if(empty($user_info))
        {
            echo "no user select !".PHP_EOL;
            $this->ext_log_data = json_encode($res);
            exit;
        }
        $this->load->library('service/stat/common_service');
        $mail_conf = $this->config->item('mail_notify');
        foreach($user_info as $value)
        {
            $day_count = $this->remind_service->calc_work_date(date('Y-m-d',strtotime($value['ctime'])));
            if($first)
            {
                if($day_count > 5 && $day_count < 13)
                    $day_count = 5;
                else if ($day_count > 13 && $day_count < 29)
                    $day_count = 13;
                else if($day_count > 29)
                    $day_count = 29;
            }
            if(isset($mail_conf[$day_count]))
            {
                $data['mobile'] = $value['mobile'];
                $html = $this->load->view('edm/'.$mail_conf[$day_count]['page'],$data,TRUE);
                $conf = array(
                    'email_from'=>'zhitouyi@haizhi.com',
                    'email_to'=>$value['email'],
                    'email_cc'=>'',
                    'email_subject'=>$mail_conf[$day_count]['title'],
                );
                $this->common_service->send_email($html,$conf);
                array_push($res[$day_count],$value['mobile']);
            }
        }

        echo 'ok !'.PHP_EOL;
        $this->ext_log_data = json_encode($res);
        return ;
    }

    public function msg($first = FALSE)
    {
        $res = array('2'=>array(),'4'=>array(),'11'=>array());
        //获取未登陆过的客户
        $this->load->model('enterprise_user_model');
        $user_info = $this->enterprise_user_model->get_user_not_login();

        if(empty($user_info))
        {
            echo "no user select !".PHP_EOL;
            $this->ext_log_data = json_encode($res);
            exit;
        }
        $this->load->library('sms/sms_service');
        $msg_conf = $this->config->item('msg_notify');
        foreach($user_info as $value)
        {
            $day_count = $this->remind_service->calc_work_date(date('Y-m-d',strtotime($value['ctime'])));
            if($first)
            {
                if($day_count > 2 && $day_count < 4)
                    $day_count = 2;
                else if ($day_count > 4 && $day_count < 11)
                    $day_count = 4;
                else if($day_count > 11)
                    $day_count = 11;
            }
            if(isset($msg_conf[$day_count]))
            {
                $mobile = $value['mobile'];
                $content = str_replace('{username}',$mobile,$msg_conf[$day_count]).ZTY_MSG_SUFFIX;
                $this->sms_service->mt($mobile,$content);
                array_push($res[$day_count],$mobile);
            }
        }

        echo 'ok !'.PHP_EOL;
        $this->ext_log_data = json_encode($res);
        return ;
    }

}

/* End of file nofity.php */
/* Location: ./application/controllers/statistics/notify.php */
