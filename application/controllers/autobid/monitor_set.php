<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  关键词排名监控报警设置
 *   
*/
class Monitor_set extends CI_Controller {

    private $sem_user = '';
    private $keyword_id = '';
    private $rank = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
        $this->load->library('redis/ExpireHelperRedis');
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
            $res['error_code'] = '10';
            $res['error_msg'] = 'keyword not belong current user';
            $this->output->set_output(json_encode($res));
            return ;
        }
        //设置监控阈值
        $set_res = $this->autobid_service->monitor_set($this->keyword_id,$this->rank);
        if(!$set_res)
        {
            $res['error_code'] = '11';
            $res['error_msg'] = 'db error';
            $this->output->set_output(json_encode($res));
            return ;
        }
        //修改redis库
        ExpireHelperRedis::del_monitor_date($this->keyword_id);
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
        $rank_list = array(
                '11','12','13','14','15','16','17','18',
 //               '21','22','23','24','25','26','27','28',
            );
        if(empty($data['rank']))
            $data['rank'] = NULL;
        else if(!empty($data['rank']) && !in_array($data['rank'],$rank_list))
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'rank invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->rank = $data['rank'];
        return TRUE;
    }
}

/* End of file monitor_set.php */
/* Location: ./application/controllers/autobid/monitor_set.php */
