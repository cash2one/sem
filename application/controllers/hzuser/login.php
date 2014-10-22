<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   海智用户登录
 *   
*/
class Login extends CI_Controller {

    
	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/hzuser_service');
	}

    public function index()
    {
        $data = $_REQUEST;
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'','default_bind_user'=>'','mobile_verify'=>'','user_id'=>'','mobile'=>'');
        $check = $this->hzuser_service->init($data);
        if($check['status'] == 'failed')
        {
            $res['error_code'] = $check['error_code'];
            $res['error_msg'] = $check['error_msg'];
            $this->output->set_output(json_encode($res));
            return TRUE;
        }
        //判断用户名密码
        $is_correct = $this->hzuser_service->login($data['username'],$data['password']);
        if(!$is_correct[0])
        {
            $res['error_code'] = $is_correct[1];
            $res['error_msg'] = $is_correct[2];
            $this->output->set_output(json_encode($res));
            return TRUE;
        }
        //是否保持登录
        if(isset($data['keep_login']) && $data['keep_login'] = '1')
        {
            $this->session->set_userdata('keep_login','1');
        }
        $user_info = $is_correct['data'];
        $res['status'] = 'success';
        $res['mobile_verify'] = $user_info['verify_mobile'];
        $res['user_id'] = $user_info['userid'];
        $res['mobile'] = $user_info['mobile'];
        $this->output->set_output(json_encode($res));
        return ;
    }

}

/* End of file hzuser.php */
/* Location: ./application/controllers/hzuser.php */
