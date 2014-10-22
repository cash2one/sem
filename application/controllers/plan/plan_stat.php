<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 计划模块
 **/
class Plan_stat extends CI_Controller {

	private $user_id	= 0;
	private $plan_id	= 0;
	private $start_date	= '';
	private $end_date	= '';
	private $hack_for_chart = FALSE;

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->model('plan_model');
		$dbdata = $this->plan_model->get_by_params(array('user_id' => $this->user_id, 'plan_id' => $this->plan_id));
		$dbdata = reset($dbdata);
		empty($dbdata) && $dbdata = array();
		$this->load->helper('array');
		$this->load->helper('date_helper');
		$plan = elements(array('plan_id', 'plan_name', 'budget', 'region', 'price_ratio', 'schedule', 'negative_words', 'pause', 'status'), $dbdata, '');
		($plan['budget'] <= 0) && $plan['budget'] = '不限定';
		$plan['region'] = $plan['region'];
		$plan['schedule'] = schedule_to_bits(json_decode('[' . strtr($plan['schedule'], array('{' => '[', '}' => ']')) . ']', true));
		$plan['schedule_period'] = (strcmp($plan['schedule'], str_repeat('1', 7 * 24)) == 0) ? '全天' : '自定义时段';
		$plan['negative_words_count'] = !empty($plan['negative_words']) ? count(explode(',', $plan['negative_words'])) : 0;

		$this->load->model('plan_stat_model');
		$statdata = $this->plan_stat_model->get_by_params(
			array('baidu_id' => $this->user_id, 'plan_id' => $this->plan_id, 'date >=' => $this->start_date, 'date <=' => $this->end_date),
			array('date', 'impression', 'format(cost / click, 2) as average_click', 'click', 'format(click / impression * 100, 2) as click_rate', 'cost'),
			array('hash_key' => 'date')
		);
		
		$detail_stat = array('impression' => 0, 'click' => 0, 'cost' => 0);
		$detail = array();
		foreach ($statdata as $key => $value) {
			$detail[$key] = elements(array('impression', 'average_click', 'click', 'click_rate', 'cost'), $value, '0');
			if ($this->hack_for_chart && ($this->end_date != $key)) {
				continue;
			}
			foreach (array('impression', 'click', 'cost') as $statkey) {
				$detail_stat[$statkey] += isset($value[$statkey]) ? $value[$statkey] : 0;
			}
		}
		$summary = array(
			'impression'		=> number_format($detail_stat['impression']),
			'average_click'		=> !empty($detail_stat['click']) ? number_format($detail_stat['cost'] / $detail_stat['click'], 2) : 0,
			'click'				=> number_format($detail_stat['click']),
			'click_rate'		=> !empty($detail_stat['impression']) ? number_format($detail_stat['click'] / $detail_stat['impression'] * 100, 2) : 0,
			'cost'				=> number_format($detail_stat['cost'], 2),
		);

		$this->output->set_output(json_encode(array(
			'status'	=> 'success',
			'data'		=> array(
				'plan'		=> $plan,
				'summary'	=> $summary,
				'detail'	=> $detail,
			)
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
		!empty($user_id) && $this->user_id = $user_id;
		// plan_id
		$plan_id = isset($_REQUEST['plan_id']) ? intval($_REQUEST['plan_id']) : 0;
		!empty($plan_id) && $this->plan_id = $plan_id;
		// 合法日期
		$start_date = isset($_REQUEST['s_date']) ? trim($_REQUEST['s_date']) : '';
		$end_date = isset($_REQUEST['e_date']) ? trim($_REQUEST['e_date']) : '';
		if (empty($start_date) && empty($end_date)) {
			$this->hack_for_chart = TRUE;
			$start_date = date('Y-m-d', strtotime('-7 day'));
		}
		$this->load->helper('date_helper');
		!datecheck($start_date) && $start_date = date('Y-m-d', strtotime('-1 day'));
		$this->start_date = $start_date;
		!datecheck($end_date) && $end_date = date('Y-m-d', strtotime('-1 day'));
		$this->end_date = $end_date;
		return TRUE;
	}
}

