<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   初始化
 *   
*/
class Init extends CI_Controller {

    private $sem_user = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->library('service/common_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $yesterday = date('Y-m-d',time()-24*3600);
        $init_data = array('baiduid'=>$this->sem_user,'date'=>$yesterday,'report_history_days'=>1);
        //获取用户信息
        $user_info = $this->user_service->user_info($this->sem_user);
        if(empty($user_info))
        {
            $res['error_code'] = '5';
            $res['error_msg'] = 'user not exist';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //判断是否需要初始化
        if($user_info['init_flag'] == '1')
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'user is initing';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        else if($user_info['init_flag'] == '2')
        {
            $res['error_code'] = '12';
            $res['error_msg'] = 'has already init';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        //请求sem api接口
        $sem_res = $this->user_service->sem_sync($init_data);
        //失败
        if(!$sem_res[0])
        {
            $res['error_code'] = $sem_res[1];
            $res['error_msg'] = $sem_res[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

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
        $check = Auth_filter::api_check_userid();
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        return TRUE;
    }
}

/* End of file init.php */
/* Location: ./application/controllers/user/init.php */
