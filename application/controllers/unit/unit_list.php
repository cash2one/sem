<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 单元模块
 **/
class Unit_list extends CI_Controller {

	private $user_id	= 0;
	private $plan_id	= 0;
	private $start_time	= '';
	private $end_time	= '';
	private $limit		= '10';
	private $offset		= '0';
	private $orderby	= 'unit_id';
	private $ordertype	= 'desc';
	private $support_order = array('unit_id', 'max_price', 'impression', 'click', 'average_click', 'click_rate', 'cost');


	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->model('unit_model');
		$orderby = "{$this->orderby} {$this->ordertype}";
		$params = array('user_id' => $this->user_id);
		!empty($this->plan_id) && $params['plan_id'] = $this->plan_id;
		$dbdata = $this->unit_model->get_unit_join_stat_by_params(
			$params,
			array('baidu_id' => $this->user_id, 'date >=' => $this->start_time, 'date <=' => $this->end_time),
			array('unit_id', 'unit_name', 'plan_id', 'max_price', 'negative_words', 'pause', 'status', 'sum(impression) as impression', 'format(sum(cost) / sum(click), 2) as average_click', 'sum(click) as click', 'format(sum(click) / sum(impression) * 100, 2) as click_rate', 'sum(cost) as cost'),
			array('groupby' => 'unit_id', 'hash_key' => 'unit_id', 'offset' => $this->offset, 'limit' => $this->limit, 'orderby' => $orderby)
		);
		$this->load->helper('array_util_helper');
		$plan_ids = data_to_array($dbdata, 'plan_id');
		$plan_db = array();
		if (!empty($plan_ids)) {
			$this->load->model('plan_model');
			$plan_db = $this->plan_model->get_by_params(array('plan_id' => $plan_ids), array(), array('hash_key' => 'plan_id'));
		}
		$this->load->helper('array');
		$list = array();
		foreach ($dbdata as $unit) {
			$belong_plan = isset($plan_db[$unit['plan_id']]) ? $plan_db[$unit['plan_id']]['plan_name'] : '';
			$price_ratio = isset($plan_db[$unit['plan_id']]) ? $plan_db[$unit['plan_id']]['price_ratio'] : '0.00';
			$content = elements(array('unit_id', 'unit_name', 'max_price', 'negative_words', 'pause', 'status'), $unit, '');
			$stat = elements(array('impression', 'click', 'average_click', 'click_rate', 'cost'), $unit, '0');
			$content['negative_words_count'] = !empty($content['negative_words']) ? count(explode(',', $content['negative_words'])) . '' : '0';
			$content['belong_plan'] = $belong_plan;
			$content['price_ratio'] = $price_ratio;
			empty($stat['average_click']) && $stat['average_click'] = '0.00';
			empty($stat['click_rate']) && $stat['click_rate'] = '0.00';
			$list[] = array_merge($content, $stat);
		}

		// 单元数
		$count = $this->unit_model->get_by_params($params, array('count(*) as count'));
		$count = reset($count);
		$count = isset($count['count']) ? $count['count'] : '0';

		$this->output->set_output(json_encode(array(
			'status'	=> 'success',
			'data'		=> array(
				'list'	=> $list,
				'page'	=> array(
					'page_size'		=> $this->limit,
					'cur_page'		=> ceil($this->offset / $this->limit) + 1,
					'total_page'	=> ceil($count / $this->limit),
					'count'			=> $count 
				)
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
		// 页码
		$limit = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 0;
		!empty($limit) && $this->limit = $limit;
		$page = isset($_REQUEST['page']) ? max(intval($_REQUEST['page']), 1) : 1;
		$this->offset = $this->limit * ($page - 1); 
		// user_id
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		!empty($user_id) && $this->user_id = $user_id;
		// plan_id
		$plan_id = isset($_REQUEST['plan_id']) ? intval($_REQUEST['plan_id']) : 0;
		!empty($plan_id) && $this->plan_id = $plan_id;
		// 排序字段
		$orderby = isset($_REQUEST['orderby']) ? trim($_REQUEST['orderby']) : '';
		!in_array($orderby, $this->support_order) && $orderby = reset($this->support_order);
		$this->orderby = $orderby;
		// 排序方式
		$ordertype = isset($_REQUEST['ordertype']) ? trim($_REQUEST['ordertype']) : '';
		!in_array($ordertype, array('asc', 'desc')) && $ordertype = 'desc';
		$this->ordertype = $ordertype;
		// 合法日期
		$this->load->helper('date_helper');
		$start_time = isset($_REQUEST['start_time']) ? trim($_REQUEST['start_time']) : '';
		!datecheck($start_time) && $start_time = date('Y-m-d', strtotime('-1 day'));
		$this->start_time = $start_time;
		$end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
		!datecheck($end_time) && $end_time = date('Y-m-d', strtotime('-1 day'));
		$this->end_time = $end_time;
		return TRUE;
	}

}

