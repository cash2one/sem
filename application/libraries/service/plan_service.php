<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_service {
	
	private $_ci;
	
	public function __construct($params = NULL) {
		$this->_ci =& get_instance();
		$this->_ci->load->model('plan_model');
		$this->_ci->load->library('baidu_service', $params);
	}

	public function update_plan($plan_ids, $params) {
		$update_keys = array(
			'plan_name'				=> 'campaign_name',
			'region'				=> 'region_target',
			'show_mode'				=> 'show_prob',
			'pause'					=> 'pause',
			'budget'				=> 'budget',
			'exclude_ip'			=> 'exclude_ip',
			'negative_words'		=> 'negative_words',
			'exact_negative_words'	=> 'exact_negative_words',
			'schedule'				=> 'schedule',					// 特殊处理
			'price_ratio'			=> 'price_ratio',
		);
		$campaign = array();
		foreach ($params as $key => $value) {
			if (!isset($update_keys[$key])) {
				continue;
			}
			// 参数修改
			switch ($update_keys[$key]) {
				case 'budget':
					if ($value <= 0) {
						$update_keys[$key] = NULL;
						$value = 0;
					}
				break;
				case 'pause':
					$value = ($value == 1) ? TRUE : FALSE;
				break;
				case 'region_target':
					$params[$key] = implode(',', $value);
				break;
				case 'exclude_ip':
				case 'negative_words':
				case 'exact_negative_words':
					$value = explode(',', $value);
				break;
				case 'schedule':
					$db_value = '';
					$schedule = array();
					foreach ($value as $one) {
						$db_value .= "{" . implode(',', $one) . "},";
						list($week_day, $start_hour, $end_hour) = $one;
						$schedule[] = array('week_day' => $week_day, 'start_hour' => $start_hour, 'end_hour' => $end_hour);
					}
					$params[$key] = rtrim($db_value, ',');
					$value = $schedule;
				default:
				break;
			}
			$campaign[$update_keys[$key]] = $value;
		}
		$campaigns = array();
		foreach ($plan_ids as $plan_id) {
			$campaigns[] = array_merge(array('campaign_id' => $plan_id), $campaign);
		}
		$ret = $this->_ci->baidu_service->baidu_campaign_update($campaigns);
		if (!isset($ret['header']['error_code']) || !empty($ret['header']['error_code'])) {
			return array('failed', $ret['header']['error_code'], $ret['header']['error_msg']);
		}
		$this->_ci->plan_model->update_params($params, array('plan_id' => $plan_ids));
		// 暂停计划时，同时暂停竞价
		if (isset($params['pause']) && ($params['pause'] == 1)) {
			$this->_ci->load->library('service/operation_association_service');
			$this->_ci->operation_association_service->associate_keyword_bid_status($plan_ids);
		}
        //修改计划推广地域时，判断是否暂定所属智能竞价关键词
        if(isset($params['region']))
        {
            $this->_association_region($plan_ids,$params['region']);
        }
		return array('success', '', '');
	}

	// 判断计划ID在当前时间内是否暂停
	public function in_schedule($plan_id) {
		if (empty($plan_id)) {
			return TRUE;
		}
		$plan_db = $this->_ci->plan_model->get_by_params(array('plan_id' => $plan_id));
		$plan_db = reset($plan_db);
		if (empty($plan_db)) {
			return TRUE;
		}

		$cur_week_day = date('w', $_SERVER['REQUEST_TIME']);
		empty($cur_week_day) && $cur_week_day = 7;
		$cur_hour = date('H', $_SERVER['REQUEST_TIME']); 
		$this->_ci->load->helper('date_helper');
		$schedule = json_decode('[' . strtr($plan_db['schedule'], array('{' => '[', '}' => ']')) . ']', true);
		foreach ($schedule as $one_schedule) {
			@list($week_day, $start_hour, $end_hour) = $one_schedule;
			if (($week_day == $cur_week_day) && ($cur_hour >= $start_hour) && ($cur_hour < $end_hour)) {
				return TRUE;
			}
		}
		return FALSE;
	}

    public function _association_region($plan_ids,$region)
    {
        if(empty($plan_ids))
            return FALSE;
        if(empty($region))
        {
            $this->_ci->load->model('user_info_model');
            $user_info = $this->_ci->user_info_model->user_info(Auth_filter::current_sem_id(),'region_target');
            $region = empty($user_info[0]['region_target']) ? NULL : $user_info[0]['region_target'];
        }            
        //获取计划下开启智能竞价的关键词信息
        $this->_ci->load->model('autobid_model');
        $autobid_keyword = $this->_ci->autobid_model->get_by_plan($plan_ids,"A.keyword_id,bid_area");
        if(empty($autobid_keyword))
            return ;

        //智能竞价的地域不在修改后地域中的关键词
        $this->_ci->load->library('region_service');
        $update_keyword_id = array();
        foreach($autobid_keyword as $value)
        {
            if(!$this->_ci->region_service->area_belong($value['bid_area'],$region))
            {
                $update_keyword_id[] = $value['keyword_id'];
            }
        }
        if(empty($update_keyword_id))
            return ;
        //暂停智能竞价状态
        $this->_ci->load->model('keyword_model');
        $this->_ci->keyword_model->update_status(array('bid_status'=>'3'),array('keyword_id'=>$update_keyword_id));
        //修改
        $this->_ci->autobid_model->update_status(array('pause_reason'=>'1'),array('keyword_id'=>$update_keyword_id));

    }
}

