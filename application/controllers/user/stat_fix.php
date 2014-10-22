<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修复数据
 *   
*/
class Stat_fix extends CI_Controller {

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
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $user_info = $this->user_service->user_info($this->sem_user);
        $last_update = date('Y-m-d',strtotime($user_info['last_update']));
        if($last_update == date('Y-m-d',time()))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'last update is today';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $days = (int)(strtotime(date('Y-m-d',time())) - strtotime($last_update))/(24*3600);
        $update_data = array('date'=>date('Y-m-d',strtotime($last_update)-24*3600),'report_history_days'=>$days);
        //获取token信息
        $token = $this->common_service->get_token_info(Auth_filter::current_userid(),$this->sem_user);
        if(empty($token))
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'token is null';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //请求sem api接口
        $sem_res = $this->user_service->sem_sync(array_merge($token,$update_data));
        //失败
        if(!$sem_res[0])
        {
            $res['error_code'] = $sem_res[1];
            $res['error_msg'] = $sem_res[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        //修改数据库最新同步时间
        $user_update = array('last_update'=>date('Y-m-d H:i:s',time()));
        $this->user_service->modify($this->sem_user,$user_update);

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
        return TRUE;
    }
}

/* End of file stat_fix.php */
/* Location: ./application/controllers/user/stat_fix.php */
