<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 搜索管理页面
*/
class User extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('user.tpl');
	}
}
