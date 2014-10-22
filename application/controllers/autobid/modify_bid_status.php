<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modify_bid_status extends CI_Controller {

	private $user_id		= 0;
	private $keyword_ids	= array();
	private $update_fields	= array();
	private $support_fields = array(
		'bid_status'
	);

	public function index() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->load->model('keyword_model');
		$this->load->helper('array_util_helper');
		$keyword = $this->keyword_model->get_by_params(array('keyword_id' => $this->keyword_ids, 'user_id' => $this->user_id, 'bid_status !=' => '1'), array(), array('hash_key' => 'keyword_id'));
		$keyword_ids = data_to_array($keyword, 'keyword_id');
		if (empty($keyword_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => '无效关键词ID'
			)));
			return FALSE;
		}
		
		if (isset($this->update_fields['bid_status']) && !in_array($this->update_fields['bid_status'], array('3', '2'))) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '13', 'error_message' => '竞价状态错误'
			)));
			return FALSE;
		}
       /* //如果是要启用，那么判断智能竞价的地域是否所属计划或账户中
        if($this->update_fields['bid_status'] == 2)
        {
            $this->load->library('service/autobid_service');
            if(!$this->autobid_service->area_belong_plan($this->keyword_ids))
            {
			    $this->output->set_output(json_encode(array(
				    'status' => 'failed', 'error_code' => '14', 'error_message' => '关键词地域与计划地域不符'
			    )));
			    return FALSE;
            }
        }*/

		// 添加限制
		// 不在t_swan_baidu_keyword_autobid, bid_status不能改为2
		$this->load->model('autobid_model');
		$keyword_autobid = $this->autobid_model->get_by_params(array('keyword_id' => $keyword_ids));
		$keyword_ids = data_to_array($keyword_autobid, 'keyword_id');
		if (empty($keyword_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => '无效关键词ID'
			)));
			return FALSE;
		}
		$this->keyword_model->update_params(array_merge($this->update_fields,array('last_update'=>date('Y-m-d H:i:s',time()))), array('keyword_id' => $keyword_ids));

		// 更新cur_bid
		if ($this->update_fields['bid_status'] == 2) {
			$unit_ids = data_to_array($keyword, 'unit_id');
			$unit_db = array();
			if (!empty($unit_ids)) {
				$this->load->model('unit_model');
				$unit_db = $this->unit_model->get_by_params(array('unit_id' => $unit_ids), array(), array('hash_key' => 'unit_id'));
			}
			$params = array();
			foreach ($keyword_ids as $keyword_id) {
				$unit_id = isset($keyword[$keyword_id]) ? $keyword[$keyword_id]['unit_id'] : '0';
				$cur_bid = isset($keyword[$keyword_id]) ? $keyword[$keyword_id]['price'] : NULL;
				!isset($cur_bid) && $cur_bid = isset($unit_db[$unit_id]) ? $unit_db[$unit_id]['max_price'] : '0';
				$params[] = array(
					'keyword_id' => $keyword_id,
					'cur_bid' => $cur_bid,
                    'start_time'=>date('Y-m-d H:i:s',time()),
                    'pause_reason'=>'0',
                    'pause_autobid'=>'2',
                    'round'=>'0',
                    'complete_feedback'=>'0',
                    'complete_time'=>NULL,
				);
			}
			$this->autobid_model->update_autobid_by_keyword_ids($params);
		}

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
		// keyword_ids
		$keyword_ids = isset($_REQUEST['keyword_ids']) ? trim($_REQUEST['keyword_ids']) : '';
		$keyword_ids = array_filter(explode(',', $keyword_ids));
		if (empty($keyword_ids)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '11', 'error_message' => '无效关键词ID'
			)));
			return FALSE;
		}
		$this->keyword_ids = $keyword_ids;
		// 更新字段
		$update_fields = array_intersect_key($_REQUEST, array_flip($this->support_fields));
		if (empty($update_fields)) {
			$this->output->set_output(json_encode(array(
				'status' => 'failed', 'error_code' => '12', 'error_message' => '更新字段为空'
			)));
			return FALSE;
		}
		$this->update_fields = $update_fields;
		return TRUE;
	}
}

