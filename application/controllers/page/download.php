<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 下载界面
*/
class Download extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('index.tpl');
	}
}
