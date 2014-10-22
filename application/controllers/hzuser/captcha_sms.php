<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   发送手机验证码
 *   
*/
class Captcha_sms extends CI_Controller {

    
	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/hzuser_service');
		$this->load->library('sms/sms_service');
		$this->load->library('redis/ExpireHelperRedis');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        if(!empty($_REQUEST['mobile']))
        {
            $mobile = $_REQUEST['mobile'];
            if(!preg_match("/^1[3458]{1}\d{9}$/",$mobile))
            {
                $res['error_code'] = '5';
                $res['error_msg'] = 'mobile invalid';
                $this->output->set_output(json_encode($res));
                return ;
            }
            if(!$this->hzuser_service->is_user($mobile))
            {
                $res['error_code'] = '6';
                $res['error_msg'] = 'mobile not exist in db';
                $this->output->set_output(json_encode($res));
                return ;
            }
        }
        else
        {
            $user_id = Auth_filter::current_userid();
            if(empty($user_id))
            {
                $res['error_code'] = '1';
                $res['error_msg'] = 'login required';
                $this->output->set_output(json_encode($res));
                return ;
            }
            //手机是否已绑定
            if($this->hzuser_service->is_mob_bind($user_id))
            {
                $res['error_code'] = '2';
                $res['error_msg'] = 'mobile had been binded';
                $this->output->set_output(json_encode($res));
                return ;
            }
            //获取手机号码
            $mobile = $this->hzuser_service->get_mobile($user_id);
        }
        //发送手机验证码
		if (ExpireHelperRedis::getMobile($mobile)) {
            $res['error_code'] = '3';
            $res['error_msg'] = 'mobile had been sended';
            $this->output->set_output(json_encode($res));
			return ;
		}
		ExpireHelperRedis::setMobileExpire($mobile);
        $code = $this->hzuser_service->SetCaptchaCode();
		$suc = $this->sms_service->mt($mobile, "欢迎使用智投易，您的请求验证码：{$code}".ZTY_MSG_SUFFIX);
        if(!$suc)
        {
            $res['error_code'] = '4';
            $res['error_msg'] = 'send failed';
            $this->output->set_output(json_encode($res));
			return ;
        }
        ExpireHelperRedis::setMobileCaptcha($mobile, $code);
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

}

/* End of file hzuser.php */
/* Location: ./application/controllers/hzuser.php */
