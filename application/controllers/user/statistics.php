<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   用户层级统计数据
 *   
*/
class Statistics extends CI_Controller {

    private $sem_user = '';
    private $s_date = '';
    private $e_date = '';
    private $is_default = '0';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->helper('date');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'','data'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //获取用户信息
        $user_info = $this->user_service->user_info($this->sem_user);
        if($this->s_date != date('Y-m-d',strtotime("-1 days"))  && $this->s_date != $this->e_date && $user_info['init_flag'] == '1')
        {
            $res['error_code'] = '11';
            $res['error_msg'] = 'data is initialize';
            $this->output->set_output(json_encode($res));
            return ;
        }
        //获取用户总体数据
        $res['data']['summary'] = $this->user_service->user_stat_summary($this->sem_user,$this->s_date,$this->e_date);
        //获取图表数据
        if($this->is_default || ($this->s_date == date('Y-m-d',strtotime("-1 days")) && $this->s_date == $this->e_date))
        {
            $this->s_date = date('Y-m-d',strtotime("-7 days"));
        }
        $res['data']['detail'] = $this->user_service->user_stat_detail($this->sem_user,$this->s_date,$this->e_date);

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
        if(empty($data['s_date']) && empty($data['s_date']))
        {
            $this->s_date = date('Y-m-d',strtotime("-1 days"));
            $this->e_date = date('Y-m-d',strtotime("-1 days"));
            $this->is_default = '1';
        }
        else
        {
            if(empty($data['s_date']) || !is_date($data['s_date']))
            {
                $res['error_code'] = '8';
                $res['error_msg'] = 's_date invalid';
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
            if(empty($data['e_date']) || !is_date($data['e_date']))
            {
                $res['error_code'] = '9';
                $res['error_msg'] = 'e_date invalid';
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
            if(strtotime($data['s_date']) > strtotime($data['e_date']))
            {
                $res['error_code'] = '10';
                $res['error_msg'] = 's_date greater than e_date';
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
            $this->s_date = $data['s_date'];
            $this->e_date = $data['e_date'];
        }
        return TRUE;
    }
}

/* End of file statistics.php */
/* Location: ./application/controllers/user/statistics.php */
