<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Del extends CI_Controller {
    private $user_id	    = 0;
    private $sem_id         = 0;
    private $creative_ids	= array();

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['creative_ids'])) {
            $this->output->set_error(1002, 'creative_ids required');
            return FALSE;
        }
        $this->creative_ids = explode(',', trim($_REQUEST['creative_ids']));
        array_walk($this->creative_ids, create_function('&$v', '$v=intval($v);'));

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/creative_service', $params);
        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        $result = $this->creative_service->delete_creative($this->creative_ids);

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
