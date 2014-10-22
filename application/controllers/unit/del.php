<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 单元模块
 **/
class Del extends CI_Controller {

	private $user_id	= 0;
	private $unit_ids	= array();

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		// 验证是否该userid的单元
		$this->load->model('unit_model');
		$this->load->helper('array_util_helper');
		$dbdata = $this->unit_model->get_by_params(array('unit_id' => $this->unit_ids, 'user_id' => $this->user_id));
		$unit_ids = data_to_array($dbdata, 'unit_id', "intval");
		if (empty($unit_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_msg' => '无效单元ID'
			)));
			return FALSE;
		}

		$this->load->library('baidu_service', array('user_id' => Auth_filter::current_userid(), 'sem_id' => $this->user_id));
		$ret = $this->baidu_service->baidu_adgroup_delete($unit_ids);
		if (!isset($ret['header']['error_code']) || !empty($ret['header']['error_code'])) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => $ret['header']['error_code'], 'error_message' => $ret['header']['error_msg']
			)));
			return FALSE;
		}

		$this->unit_model->delete_params(array('unit_id' => $unit_ids));
		// 删关键词
		$this->load->model('keyword_model');
		$this->keyword_model->delete_params(array('unit_id' => $unit_ids));
		// 删创意
		$this->load->model('creative_model');
		$this->creative_model->delete_params(array('unit_id' => $unit_ids));
		$this->output->set_output(json_encode(array('status' => 'success')));
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
		return TRUE;
	}
}

