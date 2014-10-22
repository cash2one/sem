<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   海智用户登出
 *   
*/
class Logout extends CI_Controller {

    
	function __Construct()
	{
		parent::__Construct();
	}

    public function index()
    {
        $this->session->sess_destroy();
        $res = array('status'=>'success');
        $this->output->set_output(json_encode($res));
        return ;
    }

}

/* End of file logout.php */
/* Location: ./application/controllers/hzuser/logout.php */
