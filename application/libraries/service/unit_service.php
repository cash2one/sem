<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unit_service {
	
	private $_ci;
	
	public function __construct($params) {
		$this->_ci =& get_instance();
		$this->_ci->load->model('unit_model');
		$this->_ci->load->library('baidu_service', $params);
	}

	public function update_unit($unit_ids, $params) {
		$update_keys = array(
			'unit_name'				=> 'adgroup_name',
			'pause'					=> 'pause',
			'max_price'				=> 'max_price',
			'negative_words'		=> 'negative_words',
			'exact_negative_words'	=> 'exact_negative_words',
		);
		$adgroup = array();
		foreach ($params as $key => $value) {
			if (!isset($update_keys[$key])) {
				continue;
			}
			// 参数修改
			switch ($update_keys[$key]) {
				case 'pause':
					$value = ($value == 1) ? TRUE : FALSE;
				break;
				case 'negative_words':
				case 'exact_negative_words':
					$value = explode(',', $value);
				break;
				default:
				break;
			}
			$adgroup[$update_keys[$key]] = $value;
		}
		$adgroups = array();
		foreach ($unit_ids as $unit_id) {
			$adgroups[] = array_merge(array('adgroup_id' => $unit_id), $adgroup);
		}
		$ret = $this->_ci->baidu_service->baidu_adgroup_update($adgroups);
		if (!isset($ret['header']['error_code']) || !empty($ret['header']['error_code'])) {
			return array('failed', $ret['header']['error_code'], $ret['header']['error_msg']);
		}
		$this->_ci->unit_model->update_params($params, array('unit_id' => $unit_ids));
		if (isset($params['pause']) && ($params['pause'] == 1)) {
			$this->_ci->load->library('service/operation_association_service');
			$this->_ci->operation_association_service->associate_keyword_bid_status(0, $unit_ids);
		}
		return array('success', '', '');
	}
}

