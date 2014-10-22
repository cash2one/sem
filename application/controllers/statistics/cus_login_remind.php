<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 客户未登录提醒
 */
class Cus_login_remind extends CI_Controller {

    private $msg = array(
        '0'=>'智投易云服务仍在为您24小时不间断竞价，如需变更请登录智投易设置24小时智能竞价。谢谢',
        '1'=>'您已长期未登录智投易，系统将在2个工作日后锁定您的智投易账户并暂停您的关键词竞价服务。如需变更请立即登录智投易。谢谢',
        '2'=>'您已长期未登录智投易，您的智投易账户已经锁定并已暂停关键词竞价服务。如需变更请拨打客服热线：4000639966。谢谢',
    );

	function __Construct()
	{
		parent::__Construct();
        //只允许CLI方式运行
        if(! $this->input->is_cli_request())
        {
            show_error('403 forbidden : no access to visit',403);
            exit ;
        }

        $this->load->library('service/stat/remind_service');
        $this->load->library('sms/sms_service');
    }

    public function index($is_first=0)
    {
        //获取开启24小时竞价并且至少12天未登录的客户
        $dead_date = date('Y-m-d',time()-12*24*3600);
        $user_info = $this->remind_service->get_user($dead_date);
        if(empty($user_info))
        {
            $this->ext_log_data = array('msg'=>'no user filter');
            return ;
        }
        
        $pause_user = array();
        $pause_baidu_user = array();
        $pause_mobile = array();

        foreach($user_info as $value)
        {
            //计算未登录的工作日时间
            $day_count = $this->remind_service->calc_work_date(date('Y-m-d',strtotime($value['last_login'])));
            //大于12天
            if($day_count >= 12)
            {
                echo $value['user_id']."=>".$value['mobile'].'=>'.$value['last_login'].'=>'.$day_count.': 12天'.PHP_EOL;
                array_push($pause_user,$value['user_id']);
                array_push($pause_baidu_user,$value['baidu_id']);
                array_push($pause_mobile,$value['mobile']);
            }
            //等于10天
            else if($day_count == 10)
            {
                $content = $this->msg[1].ZTY_MSG_SUFFIX;
                $mobile = $value['mobile'];
                $this->sms_service->mt($mobile,$content);
                echo $value['user_id']."=>".$value['mobile'].'=>'.$value['last_login'].'=>'.$day_count.': 10天'.PHP_EOL;
            }
            //如果是第一次，那么大于5天小于10天就发
            //如果不是第一次，那么只发送等于5天的记录
            else if($day_count >=5)
            {
                if($is_first || (!$is_first && $day_count == 5) )
                {
                    $content = $this->msg[0].ZTY_MSG_SUFFIX;
                    $mobile = $value['mobile'];
                    $this->sms_service->mt($mobile,$content);
                    echo $value['user_id']."=>".$value['mobile'].'=>'.$value['last_login'].'=>'.$day_count.': 5天'.PHP_EOL;
                }
            }
        }
        //处理超过12工作日未登录的智投易客户
        $this->remind_service->pause($pause_user,$pause_baidu_user);
        //如果是第一次，发送短信
        if($is_first)
        {
            foreach($pause_mobile as $mobile)
            {
                $content = $this->msg[2].ZTY_MSG_SUFFIX;
                $this->sms_service->mt($mobile,$content);
            }
        }
        $this->ext_log_data = array('pause_user'=>json_encode($pause_user));
        echo "Done!";
    }
}

/* End of file cus_login_remind.php */
/* Location: ./application/controllers/statistics/cus_login_remind.php */
