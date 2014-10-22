<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  获取云端配置
 *   
*/
class Uninterruptible_get extends CI_Controller {

    private $user_id = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/hzuser_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //获取uninterruptible
        $user_info = $this->hzuser_service->user_info($this->user_id);
        $uninterruptible = empty($user_info['uninterruptible']) ? "0" : $user_info['uninterruptible'];
        $res['data'] = array('uninterruptible'=>$uninterruptible);
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
        $this->user_id = Auth_filter::current_userid();
        return TRUE;
    }
}

/* End of file uninterruptible_get.php */
/* Location: ./application/controllers/autobid/uninterruptible_get.php */
