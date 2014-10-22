<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修改用户预算
 *   
*/
class Mod_budget extends CI_Controller {

    private $sem_user = '';
    private $budget_type = '';
    private $budget = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->library('service/common_service');
        $this->load->helper('date');
        $this->load->helper('number_format');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $update_data = array('budget_type'=>$this->budget_type,'budget'=>$this->budget);
        //获取token信息
        $token = $this->common_service->get_token_info(Auth_filter::current_userid(),$this->sem_user);
        if(empty($token))
        {
            $res['error_code'] = '12';
            $res['error_msg'] = 'token is null';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //请求sem api接口
        $sem_res = $this->user_service->sem_modify(array_merge($token,$update_data));
        //失败
        if(!$sem_res[0])
        {
            $res['error_code'] = $sem_res[1];
            $res['error_msg'] = $sem_res[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //成功后修改数据库
        $up_res = $this->user_service->modify($this->sem_user,$update_data);
            
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
        if(!isset($data['budget_type']) || !in_array($data['budget_type'],array('0','1','2')))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'budget_type invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->budget_type = intval($data['budget_type']);
        if($this->budget_type == '1')
        {
            if(!isset($data['budget']) || !is_numeric($data['budget']) || $data['budget'] < 50 || $data['budget'] > 10000000)
            {
                $res['error_code'] = '9';
                $res['error_msg'] = 'budget invalid';
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
            $this->budget = float_format3($data['budget']);
        }
        else if($this->budget_type == '2')
        {
            if(!isset($data['budget']) || !is_numeric($data['budget']) || $data['budget'] < 388 || $data['budget'] > 70000000)
            {
                $res['error_code'] = '9';
                $res['error_msg'] = 'budget invalid';
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
            $this->budget = float_format3($data['budget']);
        }
        else
        {
            $this->budget = float_format3(0);
        }
        return TRUE;
    }
}

/* End of file mod_budget.php */
/* Location: ./application/controllers/user/mod_budget.php */
