<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 单元模块
 **/
class Modify extends CI_Controller {

	private $user_id		= 0;
	private $unit_ids		= array();
	private $support_fields	= array(
		'unit_name', 'pause', 'max_price', 'negative_words',
	);
	private $update_fields	= array();


	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		// 验证是否该userid的单元
		$this->load->model('unit_model');
		$this->load->helper('array_util_helper');
		$dbdata = $this->unit_model->get_by_params(array('unit_id' => $this->unit_ids, 'user_id' => $this->user_id));
		$unit_ids = data_to_array($dbdata, 'unit_id', 'intval');
		if (empty($unit_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_msg' => '无效单元ID'
			)));
			return FALSE;
		}

		$this->load->library('service/unit_service', array('user_id' => Auth_filter::current_userid(), 'sem_id' => $this->user_id));
		list($status, $error_code, $error_msg) = $this->unit_service->update_unit($unit_ids, $this->update_fields);
		$this->output->set_output(json_encode(array(
			'status' => $status,
			'error_code' => $error_code,
			'error_msg' => $error_msg
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
		// unit_ids
		$unit_ids = isset($_REQUEST['unit_ids']) ? trim($_REQUEST['unit_ids']) : '';
		$unit_ids = explode(',', $unit_ids);
		$unit_ids = array_filter($unit_ids);
		if (empty($unit_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_msg' => '无效单元ID'
			)));
			return FALSE;
		}
		$this->unit_ids = $unit_ids;
		// 更新字段
		$update_fields = array_intersect_key($_REQUEST, array_flip($this->support_fields));
		if (empty($update_fields)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '12', 'error_msg' => '更新字段为空'
			)));
			return FALSE;
		}
		$this->update_fields = $update_fields;
		return TRUE;
	}
}

