<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  删除一个标签
 *   
*/
class Del_tag extends CI_Controller {

    private $sem_user = '';
    private $tag_id = '';

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/autobid_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //删除tag
        if(!$this->autobid_service->del_tag($this->sem_user,$this->tag_id))
        {
            $res['error_code'] = '11';
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
        //if(empty($data['tag_id']) || $data['tag_id'] == CORE_KEYWORD_ID || $data['tag_id'] == NULL_KEYWORD_ID)
        if(empty($data['tag_id']) || $data['tag_id'] == NULL_KEYWORD_ID)
        {
            $res['error_code'] = '9';
            $res['error_msg'] = "tag_id:{$data['tag_id']} invalid";
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->tag_id = $data['tag_id'];

        return TRUE;
    }
}

/* End of file del_tag.php */
/* Location: ./application/controllers/autobid/del_tag.php */
