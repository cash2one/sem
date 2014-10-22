<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Add extends CI_Controller {

    private $sem_user 	= '';
    private $keyword_ids = array();
    private $domains = array();
    private $ares = '';

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
            $this->output->set_error('11', 'keyword not belong current user');
            return ;
        }
        //判断有没有达到关键词的上限
        if($this->competitor_service->reach_limit($this->keyword_ids,$this->sem_user))
        {
            $this->output->set_error('12', 'keyword amount more than limit');
            return ;
        }
        //插入跟踪对手信息，并且获取id值
        $competitor_to_keyword = $this->competitor_service->insert_get_id($this->keyword_ids,$this->domains,$this->area);
        //插入关系表,并修改状态
        $insert_res = $this->competitor_service->insert_relation($this->sem_user,$competitor_to_keyword);
        if(!$insert_res)
        {
            $this->output->set_error('13', 'add failed');
            return ;
        }

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

        //校验跟踪对手域名
        if(empty($_REQUEST['domains']))
        {
            $this->output->set_error('9', 'domains invalid');
            return FALSE;
        }
        $domains = explode(',',$_REQUEST['domains']);
        $this->load->helper('url');
        foreach($domains as &$value)
        {
            $res = parse_url(prep_url($value));
            if(!$res) 
            {
                $this->output->set_error('9', "domain:$value invalid");
                return FALSE;
            }
            $value = $res['host'];
        }
        if(count($domains) > 3)
        {
            $this->output->set_error('9', 'domains greater than 3');
            return FALSE;
        }
        $this->domains = $domains;

        //校验area
        if(empty($_REQUEST['area']) || !is_numeric($_REQUEST['area']))
        {
            $this->output->set_error('10', "area invalid");
            return FALSE;
        }
        $this->area = $_REQUEST['area'];

        list($is_recharge,$code,$msg) = Auth_filter::is_competitor_recharge();
        if(!$is_recharge)
        {
            $this->output->set_error($code,$msg);
            return ;
        }

        return TRUE;
    }

}
