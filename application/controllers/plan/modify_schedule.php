<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 计划模块
 **/
class Modify_schedule extends CI_Controller {

	private $user_id		= 0;
	private $plan_ids		= array();
	private $schedule		= '';

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		// 验证是否该userid的计划
		$this->load->model('plan_model');
		$this->load->helper('array_util_helper');
		$dbdata = $this->plan_model->get_by_params(array('plan_id' => $this->plan_ids, 'user_id' => $this->user_id));
		$plan_ids = data_to_array($dbdata, 'plan_id', 'intval');
		if (empty($plan_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_msg' => '无效计划ID'
			)));
			return FALSE;
		}

		$this->load->helper('date_helper');
		$this->load->library('service/plan_service', array('user_id' => Auth_filter::current_userid(), 'sem_id' => $this->user_id));
		list($status, $error_code, $error_msg) = $this->plan_service->update_plan($plan_ids, array('schedule' => bits_to_schedule($this->schedule)));
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
		// plan_ids
		$plan_ids = isset($_REQUEST['plan_ids']) ? trim($_REQUEST['plan_ids']) : '';
		$plan_ids = explode(',', $plan_ids);
		$plan_ids = array_filter($plan_ids);
		if (empty($plan_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_msg' => '无效计划ID'
			)));
			return FALSE;
		}
		$this->plan_ids = $plan_ids;
		// 推广时间
		$schedule = isset($_REQUEST['schedule']) ? trim($_REQUEST['schedule']) : '';
		if (empty($schedule)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '12', 'error_msg' => '日期字段为空'
			)));
			return FALSE;
		}
		$this->schedule = $schedule;
		return TRUE;
	}
}

