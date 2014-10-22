<?php // if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('s_mb_substr'))
{

	/**
	 * 按字符截取
	 */
	function s_mb_substr($str,$index,$len) {
		
        if(empty($str) || !is_numeric($index) || !is_numeric($len))
            return $str;

        if(mb_strlen($str,'UTF-8') <= $len)
            return $str;
        else
            return mb_substr($str,$index,$len,'UTF-8').'...';
		
	}
}


