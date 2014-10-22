<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 操作关联的服务
 **/
class Operation_association_service {
	// 暂停计划，单元，关键词时，同时暂停竞价状态
	public function associate_keyword_bid_status($plan_id = 0, $unit_id = 0, $keyword_id = 0, $bid_status = 3) {
		$_ci =& get_instance();
		$_ci->load->helper('array_util_helper');

		$unit_ids = array();
		if (!empty($plan_id)) {
			$_ci->load->model('unit_model');
			$unit_db = $_ci->unit_model->get_by_params(array('plan_id' => $plan_id));
			$unit_ids = data_to_array($unit_db, 'unit_id');
		} else {
			$unit_ids = $unit_id;
		}

		$condition = array(
			'bid_status' => 2,	
		);
		if (!empty($unit_ids)) {
			$condition['unit_id'] = $unit_ids;
		} else {
			$condition['keyword_id'] = $keyword_id;
		}
		$_ci->load->model('keyword_model');
		return $_ci->keyword_model->update_params(array('bid_status' => $bid_status), $condition);
	}
}

