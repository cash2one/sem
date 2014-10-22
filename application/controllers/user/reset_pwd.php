<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修改密码
 *   
*/
class Reset_pwd extends CI_Controller {

    private $sem_user = '';
    private $password = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->library('service/common_service');
        $this->load->helper('account_util');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $init_data = array('date'=>date('Y-m-d',strtotime("-1 days")),'report_history_days'=>183);
        //获取用户信息
        $user_info = $this->user_service->user_info($this->sem_user);
        //判断是否需要修改密码，当状态为密码不对时才修改
        if($user_info['status'] != '2')
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'operator forbidden';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        //获取token信息
        $token = $this->common_service->get_token(Auth_filter::current_userid(),$user_info['username'],$this->password);
        if(empty($token))
        {
            $res['error_code'] = '12';
            $res['error_msg'] = 'token is null';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //请求sem api接口
        $sem_res = $this->hzuser_service->get_account($token);
        //失败
        if(!$sem_res[0])
        {
            $res['error_code'] = $sem_res[1];
            $res['error_msg'] = $sem_res[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //更新密码
        $update_data = array('status'=>'0','password'=>encode_password(NULL,$this->password));
        $this->user_service->modify($this->sem_user,$update_data);
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');

        if(empty($data['user_id']))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'user_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->sem_user = $data['user_id'];
        $check = Auth_filter::api_check_userid($this->sem_user);
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        if(!isset($data['password']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'password invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->password = $data['password'];
        return TRUE;
    }
}

/* End of file reset_pwd.php */
/* Location: ./application/controllers/user/reset_pwd.php */
