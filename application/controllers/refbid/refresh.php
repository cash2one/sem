<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refbid extends CI_Controller {
    private $user_id	= 0;
    private $sem_id 	= 0;
    private $keyword_id = 0;

    private function _init() {
        $user_status = Auth_filter::api_check_userid();
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['keyword_id'])) {
            $this->output->set_error(1003, 'necessary params required');
            return FALSE;
        }

        $this->keyword_id = intval(trim($_REQUEST['keyword_id']));

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/refbid_service', $params);
        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $refbid_info = $this->refbid_service->get_refbid_info(array($this->keyword_id));
        if (empty($refbid_info[$this->keyword_id])) {
            $this->output->set_error(1004, 'keyword info not found');
            return FALSE;
        }
        $refbid_info = $refbid_info[$this->keyword_id];

        $params = array();
        foreach ($this->keyword_ids as $keyword_id) {
            $params[] = array(
                'keyword_id'    => $keyword_id,
                'min_bid'       => $refbid_info['min_bid'],
                'max_bid'       => $refbid_info['max_bid'],
                'bid_area'      => $refbid_info['bid_area'],
                'target_rank'   => $refbid_info['target_rank'],
            );
        }

        $this->refbid_service->calculate($params);
        
        $this->output->set_json(
            array(
                'status' => 'success',
            )
        );
    }
}
