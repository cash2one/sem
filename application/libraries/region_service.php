<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * *************************************************************************
 *
 * Copyright (c) 2012 haizhi.com, Inc. All Rights Reserved
 *
 * *************************************************************************
 */
class Region_service
{
 
    private static $code_to_region = array();
    private static $code_to_area = array();
    private static $code_region_relation = array();

    function __Construct()
    {
        $region = file(REGION_PATH);
        if(empty($region))
            return FALSE;
        self::$code_to_region = json_decode($region[0],TRUE);
        self::$code_to_area = json_decode($region[1],TRUE);
        self::$code_region_relation = json_decode($region[2],TRUE);
    }

    public function area_belong($area,$region)
    {
        if(empty($area) || empty($region))
            return FALSE;
        
        $code_region_relation = self::$code_region_relation;
        $region_arr = explode(',',$region);
        //如果开启的是全部区域，直接返回TRUE
        if(in_array('9999999',$region_arr))
            return TRUE;

        $area_code = $this->_to_code($area);

        //获取region所有省市code
        $all_code = array();
        foreach($region_arr as $value)
        {
            if(empty($code_region_relation[$value]['childs']))
                $all_code[] = $value;
            else
            {
                $all_code[] = $value;
                $all_code = array_merge($all_code,array_keys($code_region_relation[$value]['childs']));
            }
        }
        
        if(in_array($area_code,$all_code))
            return TRUE;

        return FALSE;
    }
    
    //把智能竞价地域编码转成凤巢地域编码
    private function _to_code($area)
    {
        $area_code = 0;
        $code_to_area = self::$code_to_area;
        $code_region_relation = self::$code_region_relation;
        $area_name = $code_to_area[$area];
        if($area <= 37)
        {
            foreach($code_region_relation as $value)
            {
                if($value['name'] == $area_name)
                {
                    $area_code = $value['id'];
                    break;
                }
            }
        }
        else
        {
            foreach($code_region_relation as $value)
            {
                $end = 0;
                foreach($value['childs'] as $val)
                {
                    if($val['name'] == $area_name)
                    {
                        $area_code = $val['id'];
                        $end = 1;
                        break ;
                    }
                    if($end)
                        break ;
                }
            }
        }
        return $area_code;
    }

}
 
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
