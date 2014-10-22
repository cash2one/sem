<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   更新用户数据
 *   
*/
class Update extends CI_Controller {

    private $sem_user = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->library('service/common_service');
		$this->load->library('redis/ExpireHelperRedis');
	}

    public function index()
    {
        $res = array('status'=>'success','error_code'=>'','error_msg'=>'');

        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $update_data = array('baiduid'=>$this->sem_user);
        //请求sem api接口
        $this->benchmark->mark('sync_start');
        $sem_res = $this->user_service->sem_sync($update_data);
        $this->benchmark->mark('sync_end');
        $sync_st = $this->benchmark->elapsed_time('sync_start', 'sync_end')*1000;
        //失败
        if(!$sem_res[0])
        {
            $res['error_code'] = $sem_res[1];
            $res['error_msg'] = $sem_res[2];
            $this->ext_log_data = array('sync_st'=>intval($sync_st));
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        ExpireHelperRedis::setAccountSyn($this->sem_user);
        $update_status_data = array('last_update'=>date('Y-m-d H:i:s',time()));
        $this->user_service->modify($this->sem_user,$update_status_data);
        $res['status'] = 'success';
        $this->ext_log_data = array('sync_st'=>intval($sync_st));
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
        return TRUE;
    }
}

/* End of file update.php */
/* Location: ./application/controllers/user/update.php */
