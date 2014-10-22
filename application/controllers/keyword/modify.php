<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modify extends CI_Controller {
    private $user_id    = 0;
    private $sem_id 	= 0;
    private $keyword_ids  = array();
    private $field  = '';
    private $value  = '';

    private $field_format = array(
        'keyword_id'            => 'intval',
        'unit_id'               => 'intval',
        'user_id'               => 'intval',
        'keyword'               => 'strval',
        'price'                 => 'floatval',
        'pc_destination_url'    => 'strval',
        'mobile_destination_url'=> 'strval',
        'match_type'            => 'intval',
        'quality'               => 'intval',
        'pause'                 => 'intval',
        'status'                => 'intval',
    );

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['keyword_ids']) || empty($_REQUEST['field']) || !isset($_REQUEST['value'])) {
            $this->output->set_error(1003, 'necessary params required');
            return FALSE;
        }
        //这里还需要判断字段是否合法
        
        $this->keyword_ids = explode(',', trim($_REQUEST['keyword_ids']));
        array_walk($this->keyword_ids, create_function('&$v', '$v=intval($v);'));

        $this->field = trim($_REQUEST['field']);
        $this->value = $this->field_format[$this->field](trim($_REQUEST['value']));

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/keyword_service', $params);

        if (in_array($this->field, array('pc_destination_url', 'mobile_destination_url')) && 
            !empty($_REQUEST['apply_all'])) {
            //更改单元下所有关键词的url
            $keyword_ids = $this->keyword_service->get_keywords_in_unit_by_keyword_id($this->keyword_ids);
            !empty($keyword_ids) && $this->keyword_ids = $keyword_ids;
        }
        
        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $params = array();
        foreach ($this->keyword_ids as $keyword_id) {
            $params[] = array(
                'keyword_id' => $keyword_id,
                $this->field => $this->value,
            );
        }
        $result = $this->keyword_service->update_keyword($params);
        if ($result !== TRUE) {
            $this->output->set_error($result['error_code'], $result['error_msg']);
            return;
        }

        $this->output->set_json(
            array(
                'status' => 'success',
            )
        );
    }
}
