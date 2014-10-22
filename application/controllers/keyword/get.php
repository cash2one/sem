<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 添加竞价词---获取关键词
*/
class Get extends CI_Controller 
{
    private $user_id	= 0;
    private $sem_id 	= 0;
    private $params     = array();
    private $page_size  = 200;
    private $type   = 0;    //0:所有关键词 1:返回竞价词

    private function _init() 
    {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if(empty($_REQUEST['plan_id']) && empty($_REQUEST['unit_id']))
        {
            $this->output->set_error('7','plan_id and unit_id is empty');
            return FALSE;
        }
        !empty($_REQUEST['plan_id']) && $this->params['plan_id'] = $_REQUEST['plan_id'];
        !empty($_REQUEST['unit_id']) && $this->params['unit_id'] = $_REQUEST['unit_id'];

        $page = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
        $this->type = (isset($_REQUEST['type']) && in_array($_REQUEST['type'],array('0','1'))) ? $_REQUEST['type'] : 0;

        $this->page_size = empty($this->type) ? $this->page_size : 2000;
        $this->params['index'] = ($page - 1) * $this->page_size;
        $this->params['limit'] = $this->page_size;
        $this->params['user_id'] = $this->sem_id;
        $this->params['keyword'] = empty(trim($_REQUEST['keyword'])) ? '' : trim($_REQUEST['keyword']);
        !empty($this->type) && $this->params['bid_status'] = '1';

        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $this->load->library('service/keyword_service');
        $list = $this->keyword_service->keyword_list($this->params,'list');

        unset($this->params['index']);
        unset($this->params['limit']);
        $count = $this->keyword_service->keyword_list($this->params,'count');
        
        $ret = array(
            'page' => array(
                'page_size' => $this->page_size,
                'total_page' => ceil($count / $this->page_size),
                'count' => $count,
            ),
            'list' => $list,
        );
        $this->output->set_json(
            array(
                'status' => 'success',
                'data' => $ret,
            )
        );
    }
}
