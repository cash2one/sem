<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 计划模块
 **/
class Add extends CI_Controller {

	private $user_id		= 0;
	private $plan_name		= '';
	private $region			= array();
	private $show_mode		= 1;			// 1：优选；2：轮显
	private $price_ratio	= 0.1;			// 无线出价比例

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$region = $this->region;

		$this->load->library('baidu_service', array('user_id' => Auth_filter::current_userid(), 'sem_id' => $this->user_id));
		$ret = $this->baidu_service->baidu_campaign_add($this->plan_name, $region, $this->show_mode, $this->price_ratio);
		if (!isset($ret['header']['error_code']) || !empty($ret['header']['error_code'])) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => $ret['header']['error_code'], 'error_message' => $ret['header']['error_msg']
			)));
			return FALSE;
		}
		$plan_id = $ret['body']['campaign_id'];
		$this->load->model('plan_model');
		$this->plan_model->insert(array(
			'plan_id'		=> $plan_id, 
			'user_id'		=> $this->user_id, 
			'plan_name'		=> $this->plan_name,
			'show_mode'		=> $this->show_mode,
			'price_ratio'	=> $this->price_ratio,
			'region'		=> implode(',', $region),
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
		// 计划名
		$plan_name = isset($_REQUEST['plan_name']) ? trim($_REQUEST['plan_name']) : '';
		if (empty($plan_name)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => '无效计划名称'
			)));
			return FALSE;
		}
		$this->plan_name = $plan_name;
		// 地域
		$region = isset($_REQUEST['region']) ? trim($_REQUEST['region']) : '';
		empty($region) && $region = '9999999';
		$region = explode(',', $region);
        array_walk($region, create_function('&$v', '$v=intval($v);'));
		$this->region = $region;
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

