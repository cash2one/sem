<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modify extends CI_Controller {
    private $sem_user 	= '';
    private $keyword_ids = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/competitor_service');
        $this->load->library('service/autobid_service');
	}

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        $res = array('status'=>'success','error_code'=>'','error_msg'=>'');
        //判断该keyword是否属于user
        if(!$this->autobid_service->belong_user($this->sem_user,$this->keyword_ids))
        {
            $this->output->set_error('10', 'keyword not belong current user');
            return ;
        }
        
        $condition = array(
                    'keyword_id'=>$this->keyword_ids,
                    'competitor_status != '=>'1',
                );
        $this->competitor_service->update_status($this->status,$condition);
        $this->output->set_json($res);
        return ;
    }

    private function _init() {
        
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }

        if(empty($_REQUEST['user_id']))
        {
            $this->output->set_error('7', 'user_id invalid');
            return FALSE;
        }
        $this->sem_user = Auth_filter::current_sem_id();

        if(empty($_REQUEST['keyword_ids']))
        {
            $this->output->set_error('8', 'keyword_ids invalid');
            return FALSE;
        }
        $this->keyword_ids = explode(',',$_REQUEST['keyword_ids']);

        if(empty($_REQUEST['status']) || !in_array($_REQUEST['status'],array('2','3')))
        {
            $this->output->set_error('9', 'status invalid');
            return FALSE;
        }
        $this->status = $_REQUEST['status'];

        list($is_recharge,$code,$msg) = Auth_filter::is_competitor_recharge();
        if(!$is_recharge)
        {
            $this->output->set_error($code,$msg);
            return ;
        }

        return TRUE;
    }

}
