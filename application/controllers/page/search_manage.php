<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 搜索管理页面
*/
class Search_manage extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
        $this->load->helper('url');
    }

	public function index() {
        $this->load->library('smarty_service');
        //隐藏该页面
		//$this->smarty_service->view('user.tpl');
		redirect('page/smart_bid');
        return ;
	}
}

/* End of file search.php */
