<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 套餐购买界面
 */
class Buy extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
    }

	public function index() {
        $this->load->library('smarty_service');
		$this->smarty_service->view('buy.tpl');
	}
}


/* End of file */
