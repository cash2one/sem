<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 添加标签
 *   
*/
class Add_tag extends CI_Controller {

    private $sem_user = '';
    private $tag = '';

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
        if($this->tag == SELF_KEYOWRD_TAG)
        {
            $res['error_code'] = '9';
            $res['error_msg'] = '我的竞价词分组不能添加';
            $this->output->set_output(json_encode($res));
            return ;
        }

        // 检查已有的tag数目
        $existed_tag_number = count($this->autobid_service->get_tags($this->sem_user));
        if ($existed_tag_number >= 100)
        {
            $res['status'] = 'failed';
            $res['error_code'] = '11';
            $res['error_msg'] = '标签数目已超过100个，不能再继续添加。';
            $this->output->set_output(json_encode($res));
            return ; 
        }

        // 添加tag
        $insert_id = $this->autobid_service->add_tag($this->sem_user,$this->tag);
        if(!$insert_id)
        {
            $res['error_code'] = '10';
            $res['error_msg'] = 'db error';
            $this->output->set_output(json_encode($res));
            return ;
        }
        $res['tag_id'] = $insert_id;
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
        if(!isset($data['tag']) || strlen($data['tag']) > 33)
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'tag invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $data['tag'] = empty($data['tag']) ? NULL : $data['tag'];
        $pattern = '/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u';
        if(!is_null($data['tag']) && !preg_match($pattern,$data['tag']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = "tag:{$data['tag']} invalid";
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->tag = $data['tag'];

        return TRUE;
    }
}

/* End of file modify_tag.php */
/* Location: ./application/controllers/autobid/modify_tag.php */
