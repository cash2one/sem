<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 计划模块
 **/
class Modify extends CI_Controller {

	private $user_id		= 0;
	private $plan_ids		= array();
	private $support_fields	= array(
		'plan_name', 'budget', 'region', 'show_mode', 'price_ratio', 'pause', 'negative_words',
	);
	private $update_fields	= array();

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		// 验证更新内容
		foreach ($this->update_fields as $key => $value) {
			switch ($key) {
				case 'region':
					$value = json_decode($value, TRUE);
					if(!is_array($value))
                        $value = array();
                    else
                    {
					    if (empty($value)) {
					    	unset($this->update_fields[$key]);
					    	continue 2;
					    }
                    }
				break;
				case 'show_mode':
					$value = intval($value);
					if (!in_array($value, array(1, 2))) {
						unset($this->update_fields[$key]);
						continue 2;
					}
				break;
				case 'price_ratio':
					$value = floatval($value);
				break;
				default:
				break;
			}
			$this->update_fields[$key] = $value;
		}

		if (empty($this->update_fields)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '13', 'error_msg' => '更新字段格式错误'
			)));
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

		$this->load->library('service/plan_service', array('user_id' => Auth_filter::current_userid(), 'sem_id' => $this->user_id));
		list($status, $error_code, $error_msg) = $this->plan_service->update_plan($plan_ids, $this->update_fields);
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
		//
		$update_fields = array_intersect_key($_REQUEST, array_flip($this->support_fields));
		if (empty($update_fields)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '12', 'error_msg' => '更新字段为空'
			)));
			return FALSE;
		}

		$this->update_fields = $update_fields;

		// 展现方式
		$show_mode = isset($_REQUEST['show_mode']) ? intval($_REQUEST['show_mode']) : 1;
		!in_array($show_mode, array(1, 2)) && $show_mode = 1;
		$this->show_mode = $show_mode;
		// 无线出价比例
		$price_ratio = isset($_REQUEST['price_ratio']) ? floatval($_REQUEST['price_ratio']) : 0.1;
		$this->price_ratio = $price_ratio;
		return TRUE;
	}
}

