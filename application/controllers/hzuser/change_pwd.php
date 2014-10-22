<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修改密码
 *   
*/
class Change_pwd extends CI_Controller {

    private $password_old = '';
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
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
            
        //判断原密码是否正确
        if(!$this->hzuser_service->pwd_correct(auth_filter::current_userid(),$this->password_old))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'password not correct';
            $this->output->set_output(json_encode($res));
            return ;
        
        }
        //修改密码
        $update_data = array('password'=>encode_password(NULL,$this->password));
        if(!$this->enterprise_user_model->update_user_by_id(auth_filter::current_userid(),$update_data))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'db error';
            $this->output->set_output(json_encode($res));
            return ;
        }
        $this->session->sess_destroy();
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');

        $check = Auth_filter::api_check_userid();
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        if(!isset($data['password_old']))
        {
            $res['error_code'] = '5';
            $res['error_msg'] = 'old password required';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->password_old = $data['password_old'];
        if(!isset($data['password']))
        {
            $res['error_code'] = '6';
            $res['error_msg'] = 'new password required';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->password = $data['password'];
        return TRUE;
    }

}

/* End of file change_pwd.php */
/* Location: ./application/controllers/hzuser/change_pwd.php */
