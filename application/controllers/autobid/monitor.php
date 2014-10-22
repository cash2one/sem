<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  关键词排名监控图表数据
 *   
*/
class Monitor extends CI_Controller {

    private $sem_user = '';
    private $keyword_id = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //判断该keyword是否属于user
        if(!$this->autobid_service->belong_user($this->sem_user,array($this->keyword_id)))
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'keyword not belong current user';
            $this->output->set_output(json_encode($res));
            return ;
        }
        //获取监控数据的截止时间
        $e_time = $this->autobid_service->get_deadline();
        $s_time = date('Y-m-d H:i:s',strtotime($e_time)-24*3600);
        
        //获取监控数据
        $res['monitor_rank'] = $this->autobid_service->get_rank_set($this->keyword_id);
        $res['data'] = $this->autobid_service->get_keyword_rank($this->keyword_id,$s_time,$e_time);
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');

        if(empty($data['user_id']))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'user_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->sem_user = $data['user_id'];
        $check = Auth_filter::api_check_userid($this->sem_user);
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        if(!isset($data['keyword_id']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'keyword_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->keyword_id = $data['keyword_id'];
        return TRUE;
    }
}

/* End of file monitor.php */
/* Location: ./application/controllers/autobid/monitor.php */
