<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rank extends CI_Controller {
    private $sem_user 	= '';
    private $keyword_id = '';

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
        //判断该keyword是否属于user
        if(!$this->autobid_service->belong_user($this->sem_user,array($this->keyword_id)))
        {
            $this->output->set_error('9', 'keyword not belong current user');
            return ;
        }
        
        //获取监控数据的截止时间
        $e_time = $this->autobid_service->get_deadline();
        $s_time = date('Y-m-d H:i:s',strtotime($e_time)-24*3600);
        $self_rank = $this->competitor_service->keyword_rank($this->keyword_id,$s_time,$e_time);
        $competitor_rank = $this->competitor_service->competitor_rank($this->keyword_id,$s_time,$e_time);

        $this->output->set_json(
            array(
                'status' => 'success',
                'data' => array(
                        'self_rank'=>$self_rank,
                        'competitor_rank'=>$competitor_rank,
                    ),
            )
        );
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

        if(empty($_REQUEST['keyword_id']))
        {
            $this->output->set_error('8', 'keyword_id invalid');
            return FALSE;
        }
        $this->keyword_id = $_REQUEST['keyword_id'];

        list($is_recharge,$code,$msg) = Auth_filter::is_competitor_recharge();
        if(!$is_recharge)
        {
            $this->output->set_error($code,$msg);
            return ;
        }

        return TRUE;
    }

}
