<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Add extends CI_Controller {

    //最多一次批量插入多少个关键词
    private $group_count    = 2;
	private $user_id		= 0;
	private $baidu_id		= 0;
	private $keyword_ids	= array();
    private $unit_ids       = array();
    private $plan_id        = '';
    private $rest_ids       = array();
    private $tag_id         = '';
    private $op_type         = 0;
	private $params			= array(			// 必填
		//'min_bid'		=> 0,
		'max_bid'		=> 0,
		'bid_area'		=> '',
        'target_rank'   => '',
		//'strategy'		=> 0,	
	);
	private $snipe_default	= array(
		//'target_rank'	=> NULL,				// snipe为0时，必填参数	
	);
	private $snipe_method	= array(
		'snipe_strategy'			=> NULL,
		'snipe_without_strategy'	=> NULL,
		'snipe_domain'				=> NULL
	);

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->model('keyword_model');
		$this->load->helper('array_util_helper');
        $this->load->library('service/autobid_service');
        //根据传入的值计算出keyword_id组
		list($keyword_ids,$plan_id,$message) = $this->autobid_service->autobid_keywords($this->baidu_id,$this->plan_id,$this->unit_ids,$this->keyword_ids,$this->rest_ids);
		if (empty($keyword_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => $message
			)));
			return FALSE;
        }

        // 检查是否能继续添加新的关键词
        $status = $this->autobid_service->check_if_can_add(
            array_keys($keyword_ids),$this->baidu_id,$this->user_id);

		if ( ! $status) {
			$this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '14',
                'error_message' => '已添加智能竞价关键词数超过阈值，不能再继续添加。'))
            );
			return FALSE;
        }

		if (empty($this->params['bid_area']) || !is_numeric($this->params['bid_area'])) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '13', 'error_message' => '竞价地域错误'
			)));
			return FALSE;
		}
		// 过滤http://
		isset($this->params['snipe_domain']) && $this->params['snipe_domain'] = rtrim(preg_replace('#^https?://#','',$this->params['snipe_domain']), '/');

		$params = array();
        $origin_update_data = array();
		foreach ($keyword_ids as $keyword_info) {
            $cur_bid = empty($keyword_info['price']) ? $keyword_info['max_price'] : $keyword_info['price'];
			$params[] = array_merge(
				$this->params, 
				array(
                    'cur_bid' => $cur_bid,
					'keyword_id' => $keyword_info['keyword_id'], 
					'plan_id' => $plan_id,
                    'pause_reason'=>'0',
                    'pause_autobid'=>'2',
                    'round'=>'0',
                    'complete_feedback'=>'0',
                    'complete_time'=>NULL,
				)
			);
            if($keyword_info['original_price'] < 0)
            {
                $tmp = array();
                $tmp['keyword_id'] = $keyword_info['keyword_id'];
                $tmp['original_price'] = $cur_bid;
                $tmp['is_bid'] = '2';
                $origin_update_data[] = $tmp; 
            }
		}
        
        $group_params = array_chunk($params,$this->group_count,FALSE);
        unset($params);

		$this->load->model('autobid_model');
        foreach($group_params as $params)
        {
		    $this->autobid_model->insert_batch($params);
        }
        $update_data = array();
        //add时竞价，修改时不改竞价状态
        if($this->op_type == 0) {
            $update_data['bid_status'] = '2';
            $update_data['last_update'] = date('Y-m-d H:i:s',time());
            $update_data['price'] = NULL;
            $update_data['rank'] = '-1';
        }
        if($this->tag_id != NULL_KEYWORD_ID)
            $update_data['tag_id'] = $this->tag_id;

		$this->keyword_model->update_params($update_data, array('keyword_id' => array_keys($keyword_ids)));
        //设定关键词的初始价格，是否竞价过标志,设定一次就不会改了
        if(!empty($origin_update_data))
            $this->keyword_model->update_batch($origin_update_data,'keyword_id');

        //向后端发送数据
        $s_keyword = array_keys($keyword_ids);
        array_walk($s_keyword,create_function('&$v','$v=strval($v);'));
        $send_data = array(
            'userid'=>"$this->baidu_id",
            'keywordid'=>$s_keyword);
        $this->load->library('baidu_service');
        $this->baidu_service->add_autobid_msg($send_data);


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
        
        $this->baidu_id
            = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$this->user_id = Auth_filter::current_userid();
        //plan,unit,keyword
        if(empty($_REQUEST['keyword_ids']) && empty($_REQUEST['unit_ids']) && empty($_REQUEST['plan_id']))
        {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => '计划，单元，关键词不能都为空'
			)));
			return FALSE;
        }
		// keyword_ids
		$keyword_ids = isset($_REQUEST['keyword_ids']) ? trim($_REQUEST['keyword_ids']) : '';
		$this->keyword_ids = array_filter(explode(',', $keyword_ids));
        //unit_ids
		$unit_ids = isset($_REQUEST['unit_ids']) ? trim($_REQUEST['unit_ids']) : '';
		$this->unit_ids = array_filter(explode(',', $unit_ids));
        //plan_id
		$this->plan_id = isset($_REQUEST['plan_id']) ? trim($_REQUEST['plan_id']) : '';
        //rest_ids
		$rest_ids = isset($_REQUEST['rest_ids']) ? trim($_REQUEST['rest_ids']) : '';
		$this->rest_ids = array_filter(explode(',', $rest_ids));

		// params
		$snipe = isset($_REQUEST['snipe']) ? intval($_REQUEST['snipe']) : 0;
		!in_array($snipe, array(0, 1)) && $snipe = 0;
		$this->params = array_merge($this->params, (empty($snipe) ? $this->snipe_default : $this->snipe_method));

		$params = array_intersect_key($_REQUEST, $this->params);
		if (count($params) < count($this->params)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '12', 'error_message' => '缺少参数'
			)));
			return FALSE;
		}
        $this->tag_id = (isset($_REQUEST['tag_id']) && is_numeric($_REQUEST['tag_id'])) ? $_REQUEST['tag_id'] : NULL_KEYWORD_ID;
		$this->op_type = isset($_REQUEST['op_type']) ? intval($_REQUEST['op_type']) : 0;

		$this->params = array_merge($params, array('snipe' => $snipe));
        //开启时间
        $this->params['start_time'] = date('Y-m-d H:i:s',time());
		return TRUE;
	}
}

