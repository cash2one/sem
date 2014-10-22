<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   验证手机验证码
 *   
*/
class Captcha_verify extends CI_Controller {

    private $captcha = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/hzuser_service');
		$this->load->library('redis/ExpireHelperRedis');
        $this->load->model('enterprise_user_model');
        $this->load->model('enterprise_sem_user_model');
	}

    public function index()
    {
        if(!$this->_init())
        {
            return FALSE;
        }
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
            $user_info = $this->hzuser_service->is_user($mobile);
            if(!$user_info)
            {
                $res['error_code'] = '6';
                $res['error_msg'] = 'mobile not exist in db';
                $this->output->set_output(json_encode($res));
                return ;
            }
            $user_id = $user_info['userid'];
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
        //获取手机验证码
		$captcha = ExpireHelperRedis::getMobileCaptcha($mobile);
        if(empty($captcha) || ($this->captcha != strtolower($captcha)))
        {
            $res['error_code'] = '3';
            $res['error_msg'] = 'wrong captcha';
            $this->output->set_output(json_encode($res));
            return ;
        }
        
        //更新状态
        $update_data = array('verify_mobile'=>'1');
        $this->enterprise_user_model->update_user_by_id($user_id,$update_data);

        $this->session->set_userdata('reset_id',$user_id);
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init()
    {
        if(!isset($_REQUEST['captcha']))
        {
            $res['error_code'] = '4';
            $res['error_msg'] = 'captcha required';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->captcha = strtolower($_REQUEST['captcha']);
        return TRUE;
    }

}

/* End of file captcha_verify.php */
/* Location: ./application/controllers/hzuser/captcha_verify.php */
