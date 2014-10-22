<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 验证手机
*/
class Valid_mobile extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('valid_mobile.tpl');
	}
}
