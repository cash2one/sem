<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修改用户投放地域
 *   
*/
class Mod_area extends CI_Controller {

    private $sem_user = '';
    private $area = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->library('service/plan_service');
        $this->load->library('service/common_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $update_data1 = array('region_target'=>$this->area);
        $update_data2 = array('region_target'=>implode(',',$this->area));
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
        $sem_res = $this->user_service->sem_modify(array_merge($token,$update_data1));
        //失败
        if(!$sem_res[0])
        {
            $res['error_code'] = $sem_res[1];
            $res['error_msg'] = $sem_res[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        //成功后修改数据库
        $up_res = $this->user_service->modify($this->sem_user,$update_data2);
        //判断客户下面，如果有计划使用了账户推广地域，那么计划下的只能竞价暂停
        $plan_ids = $this->user_service->plan_area_belong_user($this->sem_user);
        $this->plan_service->_association_region(array_keys($plan_ids),NULL);

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
        if(!isset($data['area']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'area invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $area_arr = json_decode($data['area'],TRUE);
        if(empty($area_arr))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'area invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $area_arr = array_unique($area_arr);
        $this->area = $area_arr;
        return TRUE;
    }
}

/* End of file mod_area.php */
/* Location: ./application/controllers/user/mod_area.php */
