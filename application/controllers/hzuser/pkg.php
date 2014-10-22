<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* *
 * 获取套餐信息
 * */
class Pkg extends CI_Controller {

    private $user_id = '';
    private $baidu_id = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->model('enterprise_user_model');
        $this->load->library('service/package_service');
	}

    public function index()
    {
        $res = array('status'=>"failed",'error_code'=>'','error_msg'=>'');
        if(!$this->_init()) {
            return FALSE;
        }

        $pkg_info = $this->package_service->pkg_info(
            $this->user_id,
            $this->baidu_id);

        $res['user_id'] = $this->user_id;
        $res['all_pkg_info'] = $pkg_info['all_pkg_info'];
        $res['renew_pkg_info'] = $pkg_info['renew_pkg_info'];
        $res['upgrade_pkg_info'] = $pkg_info['upgrade_pkg_info'];
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }


    private function _init()
    {
        $user_id = Auth_filter::current_userid();
        if(empty($user_id)) {
            $res['error_code'] = '1';
            $res['error_msg'] = '请先登录';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        
        $baidu_id = Auth_filter::current_sem_id();
        list($success, $code, $message) 
            = Auth_filter::api_check_userid($baidu_id);

		if ( ! $success) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => $code,
                'message' => $message)));
			return FALSE;
		}
        $this->user_id = $user_id;
        $this->baidu_id= $baidu_id;
        return TRUE;
    }
}

/* End of file */
