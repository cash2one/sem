<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 登录界面
*/
class Login extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        $this->load->library('service/hzuser_service');
        $this->load->library('service/common_service');
        $this->load->helper('url');
    }

	public function index() {

        //判断是否为客户端登录
        if(CHECK_CLIENT_UA && !$this->common_service->is_client())
        {
            redirect('http://www.zhitouyi.com');
            return ;
        }
        $user_id = $this->session->userdata('userid');
        $keep_login = $this->session->userdata('keep_login');
        if(!empty($user_id) && !empty($keep_login))
        {
            if(!$this->hzuser_service->is_mob_bind($user_id))       
            {
                redirect('page/valid_mobile');
                return ;
            }
            if(empty($this->hzuser_service->default_bind_info($user_id)))
            {
                redirect('page/bind_user');
                return ;
            }
            redirect('page/smart_bid');
            return;

        }
        $this->load->library('smarty_service');
		$this->smarty_service->view('login.tpl');
	}
}
