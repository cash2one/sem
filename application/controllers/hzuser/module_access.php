<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   获取用户模块权限
 *   
*/
class Module_access extends CI_Controller {

    private $user_id = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->model('enterprise_user_model');
        $this->load->library('service/hzuser_service');
	}

    public function index()
    {
        $res = array('status'=>"success",'error_code'=>'','error_msg'=>'');
        if(!$this->_init())
        {
            return FALSE;
        }
        //获取权限
        $res['data'] = $this->hzuser_service->user_access($this->user_id);

        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init()
    {
        list($status,$error_code,$error_msg) = Auth_filter::api_check_userid();
        if(!$status)
        {
            $res['status'] = 'failed';
            $res['error_code'] = $error_code;
            $res['error_msg'] = $error_msg;
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        return TRUE;
    }

}

/* End of file module_access.php */
/* Location: ./application/controllers/hzuser/module_access.php */
