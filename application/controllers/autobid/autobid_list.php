<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Autobid_list extends CI_Controller {

	private $user_id	= 0;
	private $plan_id	= 0;
	private $unit_id	= 0;
	private $limit		= '10';
	private $offset		= '0';
	private $support_order = array('last_update');
	// 高级搜索
	private $keyword		= '';		// 关键词
	private $bid_status		= array();	// 是否竞价
	private $match_type		= array();	// 匹配模式
	private $quality		= array();	// 质量度
	private $min_price		= '';		// 最低出价
	private $max_price		= '';		// 最高出价
    private $tag_id            = '';       //所属标签id

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
		// unit_id
		$unit_id = isset($_REQUEST['unit_id']) ? intval($_REQUEST['unit_id']) : 0;
		!empty($unit_id) && $this->unit_id = $unit_id;
		// 页码
		$limit = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 0;
		!empty($limit) && $this->limit = $limit;
		$page = isset($_REQUEST['page']) ? max(intval($_REQUEST['page']), 1) : 1;
		$this->offset = $this->limit * ($page - 1);

		// 高级搜索参数
		$keyword = $this->input->get_post('keyword');
		!empty(trim($keyword)) && $this->keyword = trim($keyword);
		$bid_status = isset($_REQUEST['bid_status']) ? trim($_REQUEST['bid_status']) : '';
		$bid_status = array_filter(explode(',', $bid_status));
		!empty($bid_status) && $this->bid_status = $bid_status;
		$match_type = isset($_REQUEST['match_type']) ? trim($_REQUEST['match_type']) : '';
		$match_type = array_filter(explode(',', $match_type));
		!empty($match_type) && $this->match_type = $match_type;
		$quality = isset($_REQUEST['quality']) ? trim($_REQUEST['quality']) : '';
		$quality = array_filter(explode(',', $quality));
		!empty($quality) && $this->quality = $quality;
		$min_price = isset($_REQUEST['min_price']) ? floatval($_REQUEST['min_price']) : 0;
		!empty($min_price) && $this->min_price = $min_price;
		$max_price = isset($_REQUEST['max_price']) ? floatval($_REQUEST['max_price']) : 0;
		!empty($max_price) && $this->max_price = $max_price;

		$tag_id = isset($_REQUEST['tag_id']) ? $_REQUEST['tag_id'] : 0;
		!empty($tag_id) && $this->tag_id = $tag_id;


		return TRUE;
	}

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		if (empty($this->unit_id)) {
			//根据计划查找unit_id
			$this->load->model('unit_model');
			$this->load->helper('array_util_helper');
			$units = $this->unit_model->get_by_params(array('user_id' => $this->user_id, 'plan_id' => $this->plan_id));
			$this->unit_id = data_to_array($units, 'unit_id');
		}

		$params = array(
			'user_id'		=> $this->user_id,
		);
		!empty($this->unit_id)		&& $params['unit_id'] = $this->unit_id;
		!empty($this->plan_id) && is_array($this->unit_id) && empty($this->unit_id) && $params['unit_id'] = '0';
		// 高级搜索参数
		!empty($this->keyword)		&& $params['keyword'] = array('op' => 'like', 'value' => $this->keyword);
		!empty($this->bid_status)	&& $params['bid_status'] = $this->bid_status;
		!empty($this->match_type)	&& $params['match_type'] = $this->match_type;
		!empty($this->quality)		&& $params['quality'] = $this->quality;
		!empty($this->min_price)	&& $params['price >='] = $this->min_price;
		!empty($this->max_price)	&& $params['price <='] = $this->max_price;
        !empty($this->tag_id)          && $params['tag_id'] = $this->tag_id;
        
		$this->load->model('autobid_model');
		$orderby = "bid_status asc,last_update desc,t_swan_baidu_keyword.keyword_id desc";
		$dbdata = $this->autobid_model->get_autobid_join_keyword_by_params(
			$params,
			array('keyword_id', 'unit_id', 'keyword', 'rank','price', 'match_type', 'quality', 'quality_reason', 'pause', 'status', 
				'bid_status', 'min_bid', 'max_bid', 'target_rank', 'bid_area', 'strategy', 
				'snipe', 'snipe_strategy', 'snipe_without_strategy', 'snipe_domain','tag','pause_reason','pause_autobid','round','complete_feedback','complete_time',
			),
			array('offset' => $this->offset, 'limit' => $this->limit, 'orderby' => $orderby)
		);
		// 所属
		$this->load->helper('array_util_helper');
		$unit_ids = data_to_array($dbdata, 'unit_id');
		$plan_ids = array();
		$unit_db = array();
		$plan_db = array();
		if (!empty($unit_ids)) {
			$this->load->model('unit_model');
			$unit_db = $this->unit_model->get_by_params(array('unit_id' => $unit_ids), array(), array('hash_key' => 'unit_id'));
			$plan_ids = data_to_array($unit_db, 'plan_id');
		}
		if (!empty($plan_ids)) {
			$this->load->model('plan_model');
			$plan_db = $this->plan_model->get_by_params(array('plan_id' => $plan_ids), array(), array('hash_key' => 'plan_id'));
		}
		// 监控
		$keyword_ids = data_to_array($dbdata, 'keyword_id');
		$this->load->model('monitor_model');
		$monitor = $this->monitor_model->get_recently(array('keyword_id' => $keyword_ids));
		// 单元下正常的创意个数
		$this->load->model('creative_model');
		$creative_count = $this->creative_model->get_by_params(array('pause' => 0, 'unit_id' => $unit_ids), array('unit_id', 'count(*) as count'), array('groupby' => 'unit_id', 'hash_key' => 'unit_id'));
		$this->load->helper('array');
		$list = array();
		foreach ($dbdata as $bid) {
			$unit_id = isset($bid['unit_id']) ? $bid['unit_id'] : '0';
			$belong_unit = isset($unit_db[$unit_id]) ? $unit_db[$unit_id] : array();
			$plan_id = isset($unit_db[$unit_id]) ? $unit_db[$unit_id]['plan_id'] : '0';
			$belong_plan = isset($plan_db[$plan_id]) ? $plan_db[$plan_id] : array();
			$plan_region = isset($plan_db[$plan_id]) ? $plan_db[$plan_id]['region'] : '';
			$keyword = elements(array('keyword_id', 'unit_id', 'keyword','rank' ,'price', 'match_type', 'quality','quality_reason', 'pause', 'status', 'bid_status'), $bid, '');
            $keyword['rank'] = (empty($keyword['rank']) || $keyword['rank'] < 11) ? '--' : $keyword['rank'];
			$keyword['region'] = $plan_region;
			$keyword['belong_unit'] = elements(array('unit_id', 'unit_name', 'pause', 'status'), $belong_unit);
			$keyword['belong_unit']['creative_count'] = isset($creative_count[$unit_id]) ? $creative_count[$unit_id]['count'] : '0';
			$keyword['belong_plan'] = elements(array('plan_id', 'plan_name', 'pause', 'status'), $belong_plan);
			$autobid = elements(array('min_bid', 'max_bid', 'bid_area', 'target_rank', 'strategy','tag','pause_reason','pause_autobid','round','complete_feedback','complete_time'), $bid, '--');
			$bid_area = $autobid['bid_area'];
			!empty($bid_area) && $autobid['bid_area'] = $bid_area;

            if(empty($autobid['complete_time']) || $autobid['complete_time'] == '--')
                    $autobid['complete_time'] = '';
            else if(date('Ymd',time()) == date('Ymd',strtotime($autobid['complete_time'])))
                    $autobid['complete_time'] = date('H:i:s',strtotime($autobid['complete_time']));
            else if(date('Ymd',time()-24*3600) == date('Ymd',strtotime($autobid['complete_time'])))
                    $autobid['complete_time'] = '昨日 '.date('H:i:s',strtotime($autobid['complete_time']));
            else
                    $autobid['complete_time'] = '较早前';
            
            //竞价暂停、竞价中与关键词停投时，设置当前排名为‘--’，设置原因为空
            if($keyword['bid_status'] == 2 && $autobid['pause_autobid'] == 1) {
                $keyword['rank']= '--';
            }
            if($keyword['bid_status'] == 3 || ($keyword['bid_status'] == 2 && $autobid['pause_autobid'] == 2)) {
                $keyword['rank']= '--';
                $autobid['complete_feedback'] = 0;
                $autobid['complete_time'] == '';
            }
            $this->load->library('service/autobid_service');
            $last_time = $this->autobid_service->get_deadline();

            $yesterday_time = date('Y-m-d H:i:s',strtotime($last_time) - 24*3600);
            $autobid['show_monitor'] = (empty($monitor[$bid['keyword_id']]) || $monitor[$bid['keyword_id']]['moni_time'] < $yesterday_time) ? '0' : '1';

			$snipe = elements(array('snipe', 'snipe_strategy', 'snipe_without_strategy', 'snipe_domain'), $bid, '');
			$list[] = array_merge($keyword, $autobid, $snipe);
		}

		// 数量
		$this->load->model('keyword_model');
		$count = $this->autobid_model->get_autobid_join_keyword_by_params($params, array('count(*) as count'));
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
					'count'			=> $count,	
				)
			)
		)));
	}
}

