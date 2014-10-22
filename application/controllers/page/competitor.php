<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 对手跟踪页面
*/
class Competitor extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('competitor.tpl');
	}
}
