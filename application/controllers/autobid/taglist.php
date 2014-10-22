<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  获取用户下的标签
 *   
*/
class Taglist extends CI_Controller {

    private $sem_user = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //获取tag
        $res['data'] = $this->autobid_service->get_tags($this->sem_user);
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

/* End of file taglist.php */
/* Location: ./application/controllers/autobid/taglist.php */
