<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Feed extends CI_Controller {
    private $user_id	= 0;
    private $sem_id 	= 0;

    private $params = array();

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $list = $this->competitor_service->get_list($this->params);

        $item_count = $this->competitor_service->get_list_count($this->params);

        $ret = array(
            'list' => $list,
            'page' => array(
                'page_size' => $this->params['limit'],
                'cur_page' => ceil($this->params['index'] / $this->params['limit']) + 1,
                'total_page' => ceil($item_count / $this->params['limit']),
                'count' => $item_count,
            )
        );
        $this->output->set_json(
            array(
                'status' => 'success',
                'data' => $ret,
            )
        );
    }

    private function _init() {
        
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }

        $this->params['sem_id'] = Auth_filter::current_sem_id();

        if(empty($_REQUEST['page_size']))
            $this->params['limit'] = 10 ;
        else if(!is_numeric($_REQUEST['page_size']))
        {
            $this->output->set_error('7', 'page_size invalid');
            return FALSE;
        }
        else
            $this->params['limit'] = $_REQUEST['page_size'];

        //page
        if(empty($_REQUEST['page']) || !is_numeric($_REQUEST['page']))
        {
            $this->output->set_error('8', 'page invalid');
            return FALSE;
        }
        else
            $this->params['index']  = $this->params['limit'] * ($_REQUEST['page'] - 1);
        
        if(!empty($_REQUEST['unit_id']))
            $this->params['unit_id'] = $_REQUEST['unit_id'];
        if(!empty($_REQUEST['plan_id']))
            $this->params['plan_id'] = $_REQUEST['plan_id'];

        // 高级搜索参数
		$keyword = $this->input->get_post('keyword');
		!empty($keyword) && $this->params['keyword'] = $keyword;

        $tag_id = isset($_REQUEST['tag_id']) ? trim($_REQUEST['tag_id']) : '';
        !empty($tag_id) && $this->params['tag_id'] = $tag_id;

        list($is_recharge,$code,$msg) = Auth_filter::is_competitor_recharge();
        if(!$is_recharge)
        {
            $this->output->set_error($code,$msg);
            return ;
        }
        $this->load->library('service/competitor_service');

        return TRUE;
    }

}
