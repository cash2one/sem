<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 单元模块
 **/
class Unit_stat extends CI_Controller {

	private $user_id	= 0;
	private $unit_id	= 0;
	private $start_date	= '';
	private $end_date	= '';
	private $hack_for_chart = FALSE;

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->model('unit_model');
		$dbdata = $this->unit_model->get_by_params(array('user_id' => $this->user_id, 'unit_id' => $this->unit_id));
		$dbdata = reset($dbdata);
		empty($dbdata) && $dbdata = array();
		$this->load->helper('array');
		$this->load->helper('date_helper');
		$unit = elements(array('unit_id', 'plan_id', 'unit_name', 'max_price', 'negative_words', 'pause', 'status'), $dbdata, '');
		$unit['negative_words_count'] = !empty($unit['negative_words']) ? count(explode(',', $unit['negative_words'])) : 0;
		$plan_db = array();
		if (isset($unit['plan_id']) && !empty($unit['plan_id'])) {
			$this->load->model('plan_model');
			$plan_db = $this->plan_model->get_by_params(array('plan_id' => $unit['plan_id']), array(), array('hash_key' => 'plan_id'));
		}
		$unit['price_ratio'] = isset($plan_db[$unit['plan_id']]) ? $plan_db[$unit['plan_id']]['price_ratio'] : '0.00';

		$this->load->model('unit_stat_model');
		$statdata = $this->unit_stat_model->get_by_params(
			array('baidu_id' => $this->user_id, 'unit_id' => $this->unit_id, 'date >=' => $this->start_date, 'date <=' => $this->end_date),
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
				'unit'		=> $unit,
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
		// unit_id
		$unit_id = isset($_REQUEST['unit_id']) ? intval($_REQUEST['unit_id']) : 0;
		!empty($unit_id) && $this->unit_id = $unit_id;
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

