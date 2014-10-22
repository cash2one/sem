<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   sem用户信息
 *   
*/
class Info extends CI_Controller {

    private $sem_user = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
	}

    public function index()
    {
        $res = array('status'=>'success','error_code'=>'','error_msg'=>'','data'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //获取用户信息
        $user_info = $this->user_service->user_info($this->sem_user);
        $res['data']['name'] = $user_info['username'];
        $res['data']['status'] = $user_info['status'];
        $res['data']['init_flag'] = $user_info['init_flag'];
        $res['data']['balance'] = $user_info['balance'];
        $res['data']['consume_days'] = $this->user_service->get_consume_days($user_info['balance'],$this->sem_user);
        $res['data']['budget'] = $user_info['budget'];
        $res['data']['budget_type'] = $user_info['budget_type'];
        $res['data']['promote_area'] = $user_info['region_target'];
        $res['data']['ip_exclude'] = $this->user_service->get_ip_count($user_info['exclude_ip']);
        $res['data']['ip_detail'] = $this->user_service->get_ip($user_info['exclude_ip']);
        $res['data']['last_update'] = empty($user_info['last_update']) ? "" : $user_info['last_update'];
        $res['data']['last_sync'] = empty($user_info['last_sync']) ? "" : $user_info['last_sync'];
        $res['data']['white_list'] = $this->user_service->get_whitelist($this->sem_user);

        $this->ext_log_data = json_encode($res,JSON_UNESCAPED_UNICODE);
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
        $this->user_id = Auth_filter::current_userid();
        return TRUE;
    }
}

/* End of file info.php */
/* Location: ./application/controllers/user/info.php */
