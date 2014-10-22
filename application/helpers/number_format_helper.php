<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('num_format'))
{

	/**
	 * 数据转换
	 *
	 * @param unknown_type $ad_id
	 * @return string
	 */
	function num_format($number) {
		
        if(!isset($number) || !is_numeric($number))
            return NULL;

		return number_format($number,0,'.',',');
		
	}
}

if ( ! function_exists('float_format'))
{

	/**
	 * 浮点数据转换
	 *
	 * @param unknown_type $ad_id
	 * @return string
	 */
    function float_format($number)
    {
        if(!isset($number) || !is_numeric($number))
            return NULL;

		return number_format($number,2,'.',',');
    }

}

if ( ! function_exists('float_format2'))
{

	/**
	 * 浮点数据转换,不需要千分位
	 *
	 * @param unknown_type $ad_id
	 * @return string
	 */
    function float_format2($number)
    {
        if(!isset($number) || !is_numeric($number))
            return NULL;

		return number_format($number,2,'.','');
    }

}

if ( ! function_exists('float_format3'))
{

	/**
	 * 浮点数据转换,不需要千分位,用整数表示
	 *
	 * @param unknown_type $ad_id
	 * @return string
	 */
    function float_format3($number)
    {
        if(!isset($number) || !is_numeric($number))
            return NULL;

		return doubleval(number_format($number,2,'.',''));
    }

}
