<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   获取海智用户信息
 *   
*/
class Info extends CI_Controller {

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
        $user_info = $this->hzuser_service->user_info($this->user_id);
        if(empty($user_info))
        {
            $res['error_code'] = '2';
            $res['error_msg'] = 'user not exists';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $res['user_id'] = $user_info['userid'];
        $res['name'] = $user_info['name'];
        $res['contact'] = $user_info['contact'];
        $res['email'] = $user_info['email'];
        $res['mobile'] = $user_info['mobile'];
        $res['address'] = $user_info['address'];
        $res['keyword_limit'] = $user_info['bid_keyword_num'];
        $res['autobid_keyword_amount'] = $user_info['autobid_keyword_amount'];
        $res['bidword_package_id'] = $user_info['bidword_package_id'];
        $res['apply_status'] = $user_info['apply_status'];
        $res['applying_package_info'] = $user_info['applying_package_info'];
        $res['opened_package_info'] = $user_info['opened_package_info'];
        $init_info = $this->hzuser_service->default_bind_info($this->user_id);
        $res['init_flag'] = $init_info['init_flag'];
        $res['default_bind_user'] = $init_info['user_id'];
        if($user_info['expiration'] == '0000-00-00')
            $res['account'] = '1';
        else if(strtotime(date('Y-m-d'),time()) <= strtotime($user_info['expiration']))
            $res['account'] = '2';
        else if(strtotime(date('Y-m-d'),time()) > strtotime($user_info['expiration']))
            $res['account'] = '3';
        //跟踪对手是否过期
        if(empty($user_info['compete_expiration']) || $user_info['compete_expiration'] == '0000-00-00')
            $res['compete_expiration'] = '1';
        else if(strtotime(date('Y-m-d'),time()) <= strtotime($user_info['compete_expiration']))
            $res['compete_expiration'] = '2';
        else if(strtotime(date('Y-m-d'),time()) > strtotime($user_info['compete_expiration']))
            $res['compete_expiration'] = '3';
        //是否为本人操作
        $super_id = $this->session->userdata('super_id');
        $res['is_agent'] = empty($super_id) ? "0" : "1";
        //上次登录时间
        if(empty($user_info['last_login']) || $user_info['last_login'] == DEFAULT_LAST_LOGIN)
            $res['last_login'] = '';
        else
            $res['last_login'] = $user_info['last_login'];

        $res['status'] = 'success';
        $this->ext_log_data = json_encode($res,JSON_UNESCAPED_UNICODE);
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

/* End of file info.php */
/* Location: ./application/controllers/hzuser/info.php */
