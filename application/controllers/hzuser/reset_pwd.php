<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   充值密码
 *   
*/
class Reset_pwd extends CI_Controller {

    private $password = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->model('enterprise_user_model');
        $this->load->library('service/hzuser_service');
        $this->load->helper('account_util');
	}

    public function index()
    {
        if(!$this->_init())
        {
            return FALSE;
        }
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        $reset_id = $this->session->userdata('reset_id');
        if(empty($reset_id))
        {
            $res['error_code'] = '3';
            $res['error_msg'] = '没有过手机验证的步骤';
            $this->output->set_output(json_encode($res));
            return ;
        }
        else
        {
            $user_id = $reset_id;
            $this->session->unset_userdata('reset_id');
        }
        
        //充值密码
        $update_data = array('password'=>encode_password(NULL,$this->password));
        if(!$this->enterprise_user_model->update_user_by_id($user_id,$update_data))
        {
            $res['error_code'] = '3';
            $res['error_msg'] = 'reset failed';
            $this->output->set_output(json_encode($res));
            return ;
        }
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init()
    {
        if(!isset($_REQUEST['password']))
        {
            $res['error_code'] = '4';
            $res['error_msg'] = 'password required';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->password = $_REQUEST['password'];
        return TRUE;
    }

}

/* End of file reset_pwd.php */
/* Location: ./application/controllers/hzuser/reset_pwd.php */
