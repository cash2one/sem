<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  短信发送
 *   
*/
class send_msg extends CI_Controller {

    private $baidu_id = '';
    private $type = '';
    private $msg_config = array();

	function __Construct()
	{
		parent::__Construct();
		$this->load->library('sms/sms_service');
        $this->load->library('service/common_service');
        $this->load->model('account_bind_model');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //根据百度id获取手机号
        $id_list = array($this->baidu_id);
        $hzuser_info = $this->account_bind_model->get_hzuser_info($id_list);
        if(empty($hzuser_info[0]))
        {
            $res['error_code'] = '1003';
            $res['error_msg'] = 'mobile is null';
             $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $user_info = $hzuser_info[0];

        $msg_type = NULL;
        if($this->type == 1)
        {
            if(empty($user_info['uninterruptible']))
                $msg_type = 0;
            else
                $msg_type = 1;
        }
        else
            $msg_type = $this->type;

        $localtime = localtime(time(),TRUE);
        $curr_hour = $localtime['tm_hour'];
        if($msg_type != '3')
        {
            //是否在0点到7点之间
            if($curr_hour >= 7)
            {
                $mobile = $user_info['mobile'];
                $content = $this->msg_config[$msg_type].ZTY_MSG_SUFFIX;
                $suc = $this->sms_service->mt($mobile,$content);
            }
        }
        else
        {
            //是否在10点到4点之间
            if($curr_hour >= 10 && $curr_hour <=16)
            {
                $mobile = $user_info['mobile'];
                $content = $this->msg_config[$msg_type].ZTY_MSG_SUFFIX;
                $suc = $this->sms_service->mt($mobile,$content);
            }
        }

        $this->ext_log_data = array('result'=>'success');
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        //判断是否在白名单中
        $ip = $this->input->ip_address();
        if(!Common_service::in_white_list($ip))
        {
            $res = array('status'=>'failed','error_code'=>'1000','error_msg'=>'ip not in white list');
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        if(empty($data['baidu_id']))
        {
            $res = array('status'=>'failed','error_code'=>'1001','error_msg'=>'baidu_id invalid');
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->baidu_id = $data['baidu_id'];

        $this->msg_config = $this->config->item('msg_content');
        if(empty($data['type']) || !in_array($data['type'],array('1','2','3')))
        {
            $res = array('status'=>'failed','error_code'=>'1002','error_msg'=>'type invalid');
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->type = $data['type'];
        return TRUE;
    }
}

/* End of file send_msg.php */
/* Location: ./application/controllers/api/send_msg.php */
