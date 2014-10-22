<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   用户层级数据
 *   
*/
class Level_tree extends CI_Controller {

    private $user_id = '';
    private $sem_user = array();
    private $type   = 0;    //0:所有 1:有竞价词的计划

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
	}

    public function index()
    {
        $res = array('status'=>'success','error_code'=>'','error_msg'=>'','data'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        if(empty($this->sem_user))
        {
            $this->sem_user = $this->user_service->get_sem_user($this->user_id);
        }
        //获取hz用户下绑定的所有用户
        $user = $this->user_service->get_user($this->sem_user);
        //获取hz用户下绑定用户的所有plan
        $plan = $this->user_service->get_plan($this->sem_user,$this->type);
        //获取hz用户下绑定用户的所有unit
        $unit = $this->user_service->get_unit($this->sem_user,$this->type);
        //整合数据
        $res['data'] = $this->user_service->combian($user,$plan,$unit);
        $this->output->set_output(json_encode($res,JSON_UNESCAPED_UNICODE));
        return ;
    }

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');

        $check = Auth_filter::api_check_userid();
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_user = empty(Auth_filter::current_sem_id()) ? '' : array(Auth_filter::current_sem_id()); 
        $this->type = (isset($data['type']) && in_array($data['type'],array('0','1'))) ? $data['type'] : 0;
        return TRUE;
    }
}

/* End of file level_tree.php */
/* Location: ./application/controllers/user/level_tree.php */
