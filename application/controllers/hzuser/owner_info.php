<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   获取海智用户所属上级信息
 *   
*/
class Owner_info extends CI_Controller {

    private $user_id = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/hzuser_service');
	}

    public function index()
    {
        $res = array('status'=>"failed",'error_code'=>'','error_msg'=>'');
        if(!$this->_init())
        {
            return FALSE;
        }
        //获取用户信息
        $owner_info = $this->hzuser_service->get_owner($this->user_id);
        if(empty($owner_info))
        {
            $res['error_code'] = '2';
            $res['error_msg'] = 'user not exists';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $res['contact'] = $owner_info['contact'];
        $res['mobile'] = $owner_info['mobile'];
        $res['email'] = $owner_info['email'];
        $res['user_type'] = $owner_info['usertype'];
        $res['branch_level'] = $owner_info['branch_level'];

        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init()
    {
        $user_id = Auth_filter::current_userid();
        if(empty($user_id))
        {
            $res['error_code'] = '1';
            $res['error_msg'] = 'login required';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->user_id = $user_id;
        return TRUE;
    }

}

/* End of file owner_info.php */
/* Location: ./application/controllers/hzuser/owner_info.php */
