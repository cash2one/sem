<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 出价参考页面
*/
class Ref_bid extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('ref_bid.tpl');
	}
}
