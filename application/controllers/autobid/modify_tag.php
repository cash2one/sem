<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 添加或修改关键词标签
 *   
*/
class Modify_tag extends CI_Controller {

    private $sem_user = '';
    private $keyword_ids = '';
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
        //判断该keyword是否属于user
        if(!$this->autobid_service->belong_user($this->sem_user,$this->keyword_ids))
        {
            $res['error_code'] = '10';
            $res['error_msg'] = 'keyword not belong current user';
            $this->output->set_output(json_encode($res));
            return ;
        }
        //判断id是否存在
        if(!is_null($this->tag_id) && !$this->autobid_service->exist_tag_id($this->tag_id))
        {
            $res['error_code'] = '13';
            $res['error_msg'] = 'tag id not exists,please add before';
            $this->output->set_output(json_encode($res));
            return ;
        }
        
        //过滤掉不需要更新的keywords
        $this->keyword_ids = $this->autobid_service->keywords_filter($this->keyword_ids,$this->tag_id);
        if(empty($this->keyword_ids))
        {
            $res['status'] = 'success';
            $this->output->set_output(json_encode($res));
            return;
        }

        //如果是核心竞价词，判断是否达到上限
        /*if($this->tag_id == CORE_KEYWORD_ID)
        {
            $curr_keyword_tag_count = $this->autobid_service->get_curr_keyword_tag_count($this->sem_user,$this->tag_id);
            if($curr_keyword_tag_count + count($this->keyword_ids) > 60)
            {
                $res['error_code'] = '12';
                $res['error_msg'] = 'core keyword greater than 60';
                $this->output->set_output(json_encode($res));
                return ;
            }
        }*/
        //修改tag
        if(!$this->autobid_service->modify_tag($this->keyword_ids,$this->tag_id))
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
        if(!isset($data['keyword_ids']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'keyword_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $keyword_ids = explode(',',$data['keyword_ids']);
        $this->keyword_ids = $keyword_ids;

        if(empty($data['tag_id']))
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'tag_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->tag_id = $data['tag_id'];

        return TRUE;
    }
}

/* End of file modify_tag.php */
/* Location: ./application/controllers/autobid/modify_tag.php */
