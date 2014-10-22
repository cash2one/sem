<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Add extends CI_Controller {
    private $user_id	            = 0;
    private $sem_id                 = 0;
    private $unit_id	            = 0;
    private $title	                = '';
    private $description1	        = '';
    private $description2	        = '';
    private $pc_destination_url	    = '';
    private $pc_display_url	        = '';
    private $mobile_destination_url	= '';
    private $mobile_display_url	    = '';

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['title']) || 
            empty($_REQUEST['unit_id']) ||
            empty($_REQUEST['description1']) ||
            empty($_REQUEST['description2']) ||
            empty($_REQUEST['pc_destination_url'])) 
        {
            $this->output->set_error(1001, 'params invalid');
            return FALSE;
        }
        $this->title = trim($_REQUEST['title']);
        $this->unit_id = intval($_REQUEST['unit_id']);
        $this->description1 = trim($_REQUEST['description1']);
        $this->description2 = trim($_REQUEST['description2']);
        $this->pc_destination_url = trim($_REQUEST['pc_destination_url']);
        $this->mobile_destination_url = trim($_REQUEST['mobile_destination_url']);
        !empty($_REQUEST['pc_display_url']) &&
            $this->pc_display_url = trim($_REQUEST['pc_display_url']);
        !empty($_REQUEST['mobile_display_url']) &&
            $this->mobile_display_url = trim($_REQUEST['mobile_display_url']);

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/creative_service', $params);
        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        $params = array(
            'description1'          => $this->description1,
            'description2'          => $this->description2,
            'pc_destination_url'    => $this->pc_destination_url,
            'mobile_destination_url'=> $this->mobile_destination_url,
        );
        !empty($this->pc_display_url) &&
            $params['pc_display_url'] = $this->pc_display_url; 
        !empty($this->mobile_display_url) &&
            $params['mobile_display_url'] = $this->mobile_display_url; 
        $result = $this->creative_service->add_creative($this->unit_id, $this->title, $params);

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
