<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   绑定用户
 *   
*/
class Bind extends CI_Controller {

    private $hzuser_id = '';
    private $user_id = '';
    private $username = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->model('enterprise_user_model');
        $this->load->library('service/hzuser_service');
        $this->load->model('enterprise_sem_user_model');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //判断填入的username是否已被绑定
        $status = $this->hzuser_service->is_bind($this->hzuser_id,$this->user_id);
        if($status == 2)
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'has been binded';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        else if($status == 3)
        {
            $res['status'] = 'success';
            $this->output->set_output(json_encode($res));
            return TRUE;
        }
        //判断该hz用户绑定了几个sem账户
        $bind_sem_count = $this->hzuser_service->bind_count($this->hzuser_id);
        if($bind_sem_count > 0)
        {
            $res['error_code'] = '10';
            $res['error_msg'] = '目前只能绑定一个';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        //插入绑定表的数据
        $insert_bind_data = array(
                    'user_id'=>$this->hzuser_id,
                    'baidu_id'=>$this->user_id,
                    'status'=>'1',
                    'update_time'=>date('Y-m-d H:i:s',time()),
                    'ctime'=>date('Y-m-d H:i:s',time()),
                    '`default`'=>'1'
                );
        //插入用户表
        $insert_user_data = array(
                    'user_id'=>$this->user_id,
                    'username'=>$this->username,
                    'status'=>'0',
                    'init_flag'=>'0',
                );
        if(!$this->hzuser_service->insert_bind($insert_bind_data,$insert_user_data))
        {
            $res['error_code'] = '11';
            $res['error_msg'] = 'db error';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $res['status'] = 'success';

        $this->ext_log_data = json_encode($res,JSON_UNESCAPED_UNICODE);
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        $check = Auth_filter::api_check_userid();
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->hzuser_id = Auth_filter::current_userid();
        if(empty($data['user_id']) || empty($data['user_id']))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'user_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->user_id = $data['user_id'];
        if(empty($data['username']) || empty($data['username']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'username invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->username = $data['username'];
        return TRUE;
    }

}

/* End of file bind.php */
/* Location: ./application/controllers/hzuser/bind.php */
