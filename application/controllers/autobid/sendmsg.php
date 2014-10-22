<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  短信发送
 *   
*/
class sendmsg extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
		$this->load->library('sms/sms_service');
		$this->load->library('service/common_service');
        $this->load->library('redis/ExpireHelperRedis');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //获取最近的时间
        $last_time = $this->autobid_service->get_deadline();
        
        //获取关键词排名数据
        $keyword_info = $this->autobid_service->last_rank($last_time);
        $hzuser_info = $this->autobid_service->get_hzuser_info($keyword_info);

        $res['status'] = 'success';
        if(empty($keyword_info))
            $this->output->set_output(json_encode($res));

        foreach($keyword_info as $value)
        {
            //如果当前排名低于阈值，发送短信
            if(!empty($value['alarm_rank']) && ($value['rank'] > $value['alarm_rank'] || $value['rank'] == '-1'))
            {
                if(! ExpireHelperRedis::get_monitor_date($value['keyword_id']))
                {   
                    $direct = floor($value['rank']/10) == 1 ? '左侧' : '右侧';
                    $rank = $value['rank']%10;
                    if($value['rank'] == "-1")
                        $content = "智投易排名监控提醒您: 您的关键词-{$value['keyword']} 出价未竞得左侧排名，请您关注！".ZTY_MSG_SUFFIX;
                    else
                        $content = "智投易排名监控提醒您: 您的关键词-{$value['keyword']} 出价排名下降至{$direct}第{$rank}位，请您关注！".ZTY_MSG_SUFFIX;

                    $mobile = empty($hzuser_info[$value['user_id']]) ? NULL : $hzuser_info[$value['user_id']];
                    $suc = $this->sms_service->mt($mobile,$content);

                    ExpireHelperRedis::set_monitor_date($value['keyword_id']);
                }
            }
        }

        $this->ext_log_data = array('result'=>'success');
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        //不是以CLI方式运行的
        if(!$this->input->is_cli_request())
        {
            //判断是否在白名单中
            $ip = $this->input->ip_address();
            if(!Common_service::in_white_list($ip))
            {
                $this->ext_log_data = array('result'=>'failed','msg'=>'ip not in white list','ip'=>$ip);
                $res = array('status'=>'failed','error_code'=>'5','error_msg'=>'ip not in white list');
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
        }
        return TRUE;
    }
}

/* End of file sendmsg.php */
/* Location: ./application/controllers/autobid/sendmsg.php */
