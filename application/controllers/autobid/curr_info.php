<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 智能竞价----实时获取竞价状态等数据
*/
class Curr_info extends CI_Controller 
{
    private $user_id	= 0;
    private $sem_user	= 0;
    private $keyword_ids = array();
    private $cols   = array('keyword_id','keyword','price','rank','bid_status','target_rank','pause_reason','pause_autobid','round','complete_feedback','complete_time');

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
        $this->load->model('model/autobid_model');
	}

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        //判断该keyword是否属于user
        if(!$this->autobid_service->belong_user($this->sem_user,$this->keyword_ids))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'keyword not belong current user';
            $this->output->set_output(json_encode($res));
            return ;
        }
        //获取keyword实时数据
        $res = array('status'=>'success','data'=>array());
        $params = array('keyword_id'=>$this->keyword_ids);
        $curr_res = $this->autobid_model->get_curr_keyword_info($params,$this->cols,'keyword_id');
        foreach($curr_res as &$value)
        {
            //竞价暂停、竞价中与关键词停投时，设置当前排名为‘--’，设置原因为空
            if($value['bid_status'] == 2 && $value['pause_autobid'] == 1) {
                $value['rank']= '--';
            }
            if($value['bid_status'] == 3 ||($value['bid_status'] == 2 && $value['pause_autobid'] == 2)) {
                $value['rank']= '--';
                $value['complete_feedback'] = 0;
                $value['complete_time'] == '';
            }

            if(empty($value['complete_time']) || $value['complete_time'] == '--')
                $value['complete_time'] = '';
            else if(date('Ymd',time()) == date('Ymd',strtotime($value['complete_time'])))
                $value['complete_time'] = date('H:i:s',strtotime($value['complete_time']));
            else if(date('Ymd',time()-24*3600) == date('Ymd',strtotime($value['complete_time'])))
                $value['complete_time'] = '昨日 '.date('H:i:s',strtotime($value['complete_time']));
            else
                $value['complete_time'] = '较早前';
        }
        $res['data'] = $curr_res;

        $this->output->set_output(json_encode($res));
        return;
    }

    private function _init() 
    {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_user = Auth_filter::current_sem_id();

        if(empty($_REQUEST['keyword_ids']))
        {
            $this->output->set_error('7','keyword_ids invalid');
            return FALSE;
        }
        $this->keyword_ids = explode(',',$_REQUEST['keyword_ids']);

        return TRUE;
    }
}
