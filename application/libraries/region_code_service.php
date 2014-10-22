<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Region_code_service {

	private static $region_code = array();
	private static $region_autobid_code = array();

	private static function _init_param($param = 'region_code') {
		if (!empty(self::${$param})) {
			return;	
		}

		if (!defined('ENVIRONMENT') OR !file_exists($file_path = APPPATH . 'config/' . ENVIRONMENT . '/region_code.php')) {
			if (!file_exists($file_path = APPPATH . 'config/region_code.php')) {
				show_error('The configuration file region_code.php does not exist.');
			}
		}

		include($file_path);
		if (!isset(${$param}) || empty(${$param})) {
			show_error("The configuration \${$param} does not exist.");
		}
		self::${$param} = ${$param};
	}

	public static function city_to_code($citys = array()) {
		self::_init_param('region_code');
		$ret = array();
		foreach ($citys as $key => $city) {
			if (isset(self::$region_code[$city]) && !in_array(self::$region_code[$city], $ret)) {
				$ret[$key] = self::$region_code[$city];
			}
		}
		return $ret;
	}

	public static function code_to_city($codes = array()) {
		self::_init_param('region_code');
		$citys = array_flip(self::$region_code);
		$ret = array();
		foreach ($codes as $key => $code) {
			if (isset($citys[$code]) && !in_array($citys[$code], $ret)) {
				$ret[$key] = $citys[$code];
			}
		}
		return $ret;
	}

	public static function city_to_autobid_code($citys = array()) {
		self::_init_param('region_autobid_code');
		$ret = array();
		foreach ($citys as $key => $city) {
			if (isset(self::$region_autobid_code[$city]) && !in_array(self::$region_autobid_code[$city], $ret)) {
				$ret[$key] = self::$region_autobid_code[$city];
			}
		}
		return $ret;
	}

	public static function autobid_code_to_city($codes = array()) {
		self::_init_param('region_autobid_code');
		$citys = array_flip(self::$region_autobid_code);
		$ret = array();
		foreach ($codes as $key => $code) {
			if (isset($citys[$code]) && !in_array($citys[$code], $ret)) {
				$ret[$key] = $citys[$code];
			}
		}
		return $ret;
	}
}

