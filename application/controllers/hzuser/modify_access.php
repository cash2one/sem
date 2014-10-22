<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修改用户模块权限
 *   
*/
class modify_access extends CI_Controller {

    private $user_id = '';
    private $update_data = array();
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->model('enterprise_user_model');
        $this->load->library('service/hzuser_service');
	}

    public function index()
    {
        $res = array('status'=>"success",'error_code'=>'','error_msg'=>'');
        if(!$this->_init())
        {
            return FALSE;
        }
        if(!$this->hzuser_service->update_access($this->update_data))
        {
            $this->output->set_error('8','db error');
            return FALSE;
        }
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init()
    {
        list($status,$error_code,$error_msg) = Auth_filter::api_check_userid();
        if(!$status)
        {
            $res['status'] = 'failed';
            $res['error_code'] = $error_code;
            $res['error_msg'] = $error_msg;
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();

        if(empty($_REQUEST['data']))
        {
            $this->output->set_error('7','data is null');
            return FALSE;
        }
        $update = json_decode($_REQUEST['data'],TRUE);
        if(empty($update))
        {
            $this->output->set_error('7','data invalid');
            return FALSE;
        }
        $count = 0;
        foreach($update as $value)
        {
            if(is_numeric($value['module_id']) && in_array($value['access'],array('0','1','2')))
            {
                $this->update_data[$count]['user_id'] = $this->user_id;
                $this->update_data[$count]['module_id'] = $value['module_id'];
                $this->update_data[$count]['access'] = $value['access'];
                ++$count;
            }
        }
        return TRUE;
    }

}

/* End of file modify_access.php */
/* Location: ./application/controllers/hzuser/modify_access.php */
