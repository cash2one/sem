<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   提交调查问卷结果
 *   
*/
class Put extends CI_Controller {

    private $res = '';   //0:否  1:是
    private $user_id = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
        $this->load->model('questionnaire_res_model');
	}

    public function index()
    {
        $res = array('status'=>'success','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $update_data = array('result'=>$this->res);
        $this->questionnaire_res_model->update($this->user_id,$update_data);

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
        $check = Auth_filter::api_check_userid($data['user_id']);
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        if(!isset($data['res']) || !in_array($data['res'],array('1','2')))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'res invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->res = $data['res'];
        return TRUE;
    }
}

/* End of file info.php */
/* Location: ./application/controllers/user/info.php */
