<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Add extends CI_Controller {
    private $user_id	= 0;
    private $sem_id 	= 0;
    private $keywords	= '';
    private $unit_id	= 0;
    private $match_type	= 3;

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['keywords'])) {
            $this->output->set_error(1003, 'necessary params required');
            return FALSE;
        }
        $this->keywords = explode(',', trim($_REQUEST['keywords']));

        if (empty($_REQUEST['unit_id'])) {
            $this->output->set_error(1003, 'necessary params required');
            return FALSE;
        }
        $this->unit_id = intval($_REQUEST['unit_id']);

        !empty($_REQUEST['match_type']) &&
            $this->match_type = intval($_REQUEST['match_type']);

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/keyword_service', $params);
        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        $result = $this->keyword_service->add_keyword($this->keywords, $this->unit_id, $this->match_type);
        if ($result !== TRUE) {
            print_r($result);
        }

        $this->output->set_json(
            array(
                'status' => $result === TRUE ? 'success' : 'failed',
            )
        );
    }
}
