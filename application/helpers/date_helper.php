<?php // if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('is_date'))
{

	/**
	 * 校验时间
	 */
	function is_date($date) {
		
        if(!isset($date))
            return FALSE;
        $pattern = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";

        return preg_match($pattern,$date);
		
	}
}

// 验证合法日期
if (!function_exists('datecheck')) {
	function datecheck($ymd, $sep = '-') {
		if (empty($ymd)) return FALSE;
		@list($year, $month, $day) = explode($sep, $ymd);
		if (!_isint($year) || !_isint($month) || !_isint($day))
			return FALSE;
		if (!@checkdate($month, $day, $year))
			return FALSE;
		return TRUE;
	}

	function _isint($str) {
		$str = (string)$str;
		$pos = 0;
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++) {
			if ($str[$i] == '0') $pos++;
			else break;
		}
		$str = substr($str,$pos);
		$int = (int)$str;
		if ($str == (string)$int)
			return TRUE;
		else
			return FALSE;
	}
}


// 推广时间段转换成数据库字段
if (!function_exists('bits_to_schedule')) {
	function bits_to_schedule($bits) {
		// str_split
		$schedule = array();
		for ($i = 0, $len = ceil(strlen($bits) / 24); $i < $len; ++$i) {
			$week = $i + 1;
			$subbits = substr($bits, $i * 24, 24);
			$schedule = array_merge($schedule, _24_bits_to_schedule($week, $subbits));
		}
		return $schedule;
	}

	function _24_bits_to_schedule($week, $bits = '') {
		$schedule = array();
		$hour = -1;
		for ($i = 0, $len = 24; $i < $len; ++$i) {
			if (!isset($bits[$i]) || ($bits[$i] == 0)) {
				if ($hour == -1) {
					$hour = $i;
				}
			} else {
				if ($hour != -1) {
					$schedule[] = array($week, $hour, $i);
					$hour = -1;
				}
			}
		}
		if ($hour != -1) {
			$schedule[] = array($week, $hour, 24);
		}
		return $schedule;
	}
}

if (!function_exists('schedule_to_bits')) {
	function schedule_to_bits($schedule) {
		$bits = str_repeat('1', 7 * 24);
		foreach ($schedule as $one_schedule) {
			@list($week_day, $start_hour, $end_hour) = $one_schedule;
			for ($i = $start_hour; $i < $end_hour; ++$i) {
				$bits[($week_day - 1) * 24 + $i] = '0';
			}
		}
		return $bits;
	}
}


if ( ! function_exists('date_less_equal')) {
    function date_less_equal($date1, $date2) {
        return strtotime($date1) <= strtotime($date2);
    }
}

if ( ! function_exists('date_large')) {
    function date_large($date1, $date2) {
        return strtotime($date1) > strtotime($date2);
    }
}

if (!function_exists('date_minus')) {
    function date_minus($date1, $date2) {
        return intval(ceil((strtotime($date1) - strtotime($date2)) / (3600 * 24)));
    }
}
