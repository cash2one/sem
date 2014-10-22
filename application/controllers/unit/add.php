<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 单元模块
 **/
class Add extends CI_Controller {

	private $user_id		= 0;
	private $plan_id		= 0;
	private $unit_name		= '';
	private $max_price		= 0.1;

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->library('baidu_service', array('user_id' => Auth_filter::current_userid(), 'sem_id' => $this->user_id));
		$ret = $this->baidu_service->baidu_adgroup_add($this->plan_id, $this->unit_name, $this->max_price);
		if (!isset($ret['header']['error_code']) || !empty($ret['header']['error_code'])) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => $ret['header']['error_code'], 'error_message' => $ret['header']['error_msg']
			)));
			return FALSE;
		}
		$unit_id = $ret['body']['adgroup_id'];
		$this->load->model('unit_model');
		$this->unit_model->insert(array(
			'unit_id'		=> $unit_id, 
			'plan_id'		=> $this->plan_id,
			'user_id'		=> $this->user_id, 
			'unit_name'		=> $this->unit_name,
			'max_price'		=> $this->max_price,
			'ctime'			=> date('Y-m-d H:i:s', $_SERVER["REQUEST_TIME"]),
		));
		$this->output->set_output(json_encode(array(
			'status' => 'success',				
		)));
	}

	private function _init() {
		// session
		$this->load->library('auth_filter');
		list($success, $code, $message) = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
		if (!$success) {
			$this->output->set_output(json_encode(array('status' => 'failed', 'error_code' => $code, 'message' => $message)));
			return FALSE;
		}
		// user_id
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$this->user_id = $user_id;
		// plan_id
		$plan_id = isset($_REQUEST['plan_id']) ? intval($_REQUEST['plan_id']) : 0;
		if (empty($plan_id)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => '无效计划ID'
			)));
			return FALSE;
		}
		$this->plan_id = $plan_id;
		// unit_name
		$unit_name = isset($_REQUEST['unit_name']) ? trim($_REQUEST['unit_name']) : '';
		if (empty($unit_name)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '12', 'error_message' => '无效单元名称'
			)));
			return FALSE;
		}
		$this->unit_name = $unit_name;
		// max_price
		$max_price = isset($_REQUEST['max_price']) ? floatval($_REQUEST['max_price']) : 0.1;
		$this->max_price = $max_price;
		return TRUE;
	}
}

