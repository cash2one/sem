<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  设置云端配置
 *   
*/
class Uninterruptible_set extends CI_Controller {

    private $user_id = '';
    private $uninterruptible = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
        $this->load->model('enterprise_sem_user_model');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $update_data = array(
                'uninterruptible'=>$this->uninterruptible,
            );
        if(!$this->enterprise_sem_user_model->update_user_by_id($this->user_id,$update_data))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'db error';
            $this->output->set_output(json_encode($res));
            return ;
        }
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
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

        if(!isset($data['uninterruptible']) || !in_array($data['uninterruptible'],array('0','1')))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'uninterruptible invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->uninterruptible = $data['uninterruptible'];
        return TRUE;
    }
}

/* End of file uninterruptible_set.php */
/* Location: ./application/controllers/autobid/uninterruptible_set.php */
