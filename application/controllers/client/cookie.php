<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cookie extends CI_Controller {

    public function index() {
		$post = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : "";	
		if ($this->check($post)) {
			echo "good";	
		} else {
			echo "bad";	
		}
    }

	private function check($data) {
		$len = strlen($data);
		if ($len < 10) {
			return FALSE;
		}

		$str = "";
		for ($i = 0; $i < $len; $i++) {
			$ord = ord($data[$i]);
			if ($ord > 64 && $ord < 91) {
				$str .= chr($ord - ord("A") + ord("a"));
				continue;
			}
			if ($ord > 96 && $ord < 123) {
				$str .= chr($ord - ord("a") + ord("A"));
				continue;
			}
			$str .= $data[$i];
		}

		$str = base64_decode($str);
		if ($str === FALSE) {
			return FALSE;
		}
		$str = json_decode($str, true);
		if (empty($str) || !is_array($str)) {
			return FALSE;
		}
		if (!isset($str['id']) || !isset($str['cookie'])) {
			return FALSE;
		}
		if (strlen($str['cookie']) < 10) {
			return FALSE;
		}

		$this->load->library('redis/client_cookie_redis');
		Client_cookie_redis::set($str['id'], $str['cookie'], 0, FALSE);

		return TRUE;
	}	

}

