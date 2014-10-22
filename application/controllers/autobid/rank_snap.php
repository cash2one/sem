<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  关键词排名快照
 *   
*/
class Rank_snap extends CI_Controller {

    private $sem_user = '';
    private $keyword_id = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
        $this->load->library('mongo/ranksnap_mongo_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'','data'=>array());
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
        
        //获取监控快照
        $snap = $this->ranksnap_mongo_service->get_snap(array(floatval($this->keyword_id)));
        if(!empty($snap[0]))
        {
            $res['data']['snap'] = empty($snap[0]['body']) ? '' : $snap[0]['body'];
            $res['data']['time'] = empty($snap[0]['timestamp']) ? '' : date('Y-m-d H:i:s',$snap[0]['timestamp']->sec);
        }
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
