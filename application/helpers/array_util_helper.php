<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if ( ! function_exists('data_to_array'))
{
    function data_to_array($array, $key, $func = NULL) {
        $retArray = array ();
        if (!is_array($array) || empty($array)) {
            return $retArray;
        }

        foreach ($array as $oneData) {
            if (isset($oneData[$key]) && !empty($oneData[$key])) {
				!is_null($func) && $oneData[$key] = $func($oneData[$key]);
                $retArray[] = $oneData[$key];
            }
        }

        return $retArray;
    }
}


if ( ! function_exists('change_data_key'))
{
    function change_data_key($array, $key, $toLowerCase = false) {
        $resArr = array();
        if(empty($array)){
            return $resArr;
        }
        foreach ($array as $v) {
            if (!isset($v[$key]) || empty($v[$key]))
                continue;
			$k = $v[$key];
            if($toLowerCase === TRUE) {
                $k = strtolower($k);
            }
            $resArr [$k] = $v;
        }
        return $resArr;
    }
}

if ( ! function_exists('array_field'))
{
    function array_field($array, $fields = array(), $default = array()) {
        $retArray = array();
        if (!is_array($fields) || empty($fields)) {
            return $retArray;
        }
        foreach ($fields as $field) {
            $value = '';
            if (isset($array[$field]) && !is_null($array[$field])) {
                $value = $array[$field];
            } elseif (isset($default[$field])) {
                $value = $default[$field];
            }
            $retArray[$field] = $value;
        }
        return $retArray;
    }
}
