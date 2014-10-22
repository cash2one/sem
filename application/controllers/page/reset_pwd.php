<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 充值密码
*/
class Reset_pwd extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('reset_password.tpl');
	}
}
