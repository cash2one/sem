<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 计划模块
 **/
class Plan_list extends CI_Controller {

	private $user_id	= 0;
	private $start_time	= '';
	private $end_time	= '';
	private $limit		= '10';
	private $offset		= '0';
	private $orderby	= 'plan_id';
	private $ordertype	= 'desc';
	private $support_order = array('plan_id', 'budget', 'price_ratio', 'impression', 'click', 'average_click', 'click_rate', 'cost');


	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->model('plan_model');
		$orderby = "{$this->orderby} {$this->ordertype}";
		$dbdata = $this->plan_model->get_plan_join_stat_by_params(
			array('user_id' => $this->user_id),
			array('baidu_id' => $this->user_id, 'date >=' => $this->start_time, 'date <=' => $this->end_time),
			array('plan_id', 'plan_name', 'budget', 'region', 'price_ratio', 'schedule', 'negative_words', 'pause', 'status', 'sum(impression) as impression', 'format(sum(cost) / sum(click), 2) as average_click', 'sum(click) as click', 'format(sum(click) / sum(impression) * 100, 2) as click_rate', 'sum(cost) as cost'),
			array('groupby' => 'plan_id', 'hash_key' => 'plan_id', 'offset' => $this->offset, 'limit' => $this->limit, 'orderby' => $orderby)
		);

		$this->load->helper('array');
		$this->load->helper('date_helper');
		$list = array();
		foreach ($dbdata as $plan) {
			$content = elements(array('plan_id', 'plan_name', 'budget', 'region', 'price_ratio', 'schedule', 'negative_words', 'pause', 'status'), $plan, '');
			$stat = elements(array('impression', 'click', 'average_click', 'click_rate', 'cost'), $plan, '0');
			($content['budget'] <= 0) && $content['budget'] = '不限定';
			$content['region'] = $content['region'];
			$content['schedule'] = schedule_to_bits(json_decode('[' . strtr($content['schedule'], array('{' => '[', '}' => ']')) . ']', true));
			$content['negative_words_count'] = !empty($content['negative_words']) ? count(explode(',', $content['negative_words'])) . '' : '0';
			empty($stat['average_click']) && $stat['average_click'] = '0.00';
			empty($stat['click_rate']) && $stat['click_rate'] = '0.00';
			$list[] = array_merge($content, $stat);
		}

		// 计划数
		$count = $this->plan_model->get_by_params(array('user_id' => $this->user_id), array('count(*) as count'));
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
		// 排序字段
		$orderby = isset($_REQUEST['orderby']) ? trim($_REQUEST['orderby']) : '';
		!in_array($orderby, $this->support_order) && $orderby = reset($this->support_order);
		$this->orderby = $orderby;
		// 排序方式
		$ordertype = isset($_REQUEST['ordertype']) ? trim($_REQUEST['ordertype']) : '';
		!in_array($ordertype, array('asc', 'desc')) && $ordertype = 'desc';
		$this->ordertype = $ordertype;
		// user_id
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		!empty($user_id) && $this->user_id = $user_id;
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

