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
class Auth_filter
{
    
    public static function check_auth()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $user_id = $CI->session->userdata('userid');
        if(empty($user_id))
        {
            redirect('/page/login');
        }
        return TRUE;
    }

    public static function api_check_userid($bind_user = NULL) {
        $CI =& get_instance();
        $CI->load->library('session');
        //是否有访问权限
        if(! self::_access_permission())
        {
            return array(FALSE,'102','没有权限');
        }
        $user_id = $CI->session->userdata('userid');
        if (empty($user_id)) {
            return array(FALSE,'1','没有登录');
        }
        $CI->load->library('service/hzuser_service');
        $user_info = $CI->hzuser_service->user_info($user_id);
        if (empty($user_info)) {
            return array(FALSE,'2','帐号不存在');
        }
        if (!isset($user_info['status_product']) || !in_array($user_info['status_product'], array('0'))) 
        {
            return array(FALSE,'3','帐号已被停用');
        }
        if(time() > strtotime($user_info['expiration']))
        {
            return array(FALSE,'4','用户使用过期，请重新充值');
        }
        if(!is_null($bind_user))
        {
            $CI->load->library('service/user_service');
            $bind_user = $CI->user_service->bind_info($user_id,$bind_user);
            if(empty($bind_user))
                return array(FALSE,'5','绑定用户不存在');
//            if($bind_user['status'] != "0")
//                return array(FALSE,'6','绑定用户不是正常状态');
            if($bind_user['init_flag'] == "0")
                return array(FALSE,'6','未被初始化');

            //不在白名单内的检查是否在更新中
            $update_whitelist = $CI->config->item('check_update_whitelist');
            $uri = $CI->uri->uri_string();
            if(!in_array($uri,$update_whitelist) && $bind_user['init_flag'] == "3")
                return array(FALSE,'100','账户在更新中...');
        }
        return array(TRUE, '','');
    }

    public static function current_userid() {
        $CI =& get_instance();
        $user_id = $CI->session->userdata('userid');
        if (empty($user_id)) {
            return 0;
        }
        return $user_id;
    }

    public static function current_sem_id() {
        if (empty($_REQUEST['user_id'])) {
            return 0;
        }
        return $_REQUEST['user_id'];
    }   

    public static function is_competitor_recharge($user_id = NULL)
    {
        if(is_null($user_id))
            $user_id = Auth_filter::current_userid();

        $CI =& get_instance();
        $CI->load->library('service/hzuser_service');
        $user_info = $CI->hzuser_service->user_info($user_id);

        if(empty($user_info))
            return array(FALSE,'2','账号不存在');

        //跟踪对手是否过期
        if(empty($user_info['compete_expiration']) || $user_info['compete_expiration'] == '0000-00-00')
            return array(FALSE,'101','没有充值');
        else if(strtotime(date('Y-m-d'),time()) <= strtotime($user_info['compete_expiration']))
            return array(TRUE,'','');
        else if(strtotime(date('Y-m-d'),time()) > strtotime($user_info['compete_expiration']))
            return array(FALSE,'101','已过期');

    }

    private static function _access_permission()
    {
        $CI =& get_instance();
        $super_id = $CI->session->userdata('super_id');
        //为空代表是客户本人登录
        if(empty($super_id))
            return TRUE;

        $CI->load->library('service/hzuser_service');
        $access = $CI->hzuser_service->user_access(self::current_userid());
        if(empty($access))
            return FALSE;

        $CI->load->config('auth_conf');
        $config = $CI->config->item('access_control');
        //当前的uri
        $uri = $CI->uri->uri_string();
        foreach($access as $value)
        {
            $module_id = $value['id'];
            $acc = $value['access'];
            //是否白名单中
            if(self::_in_list($config['white'],$acc,$uri))
                return TRUE;
            if(self::_in_list($config[$module_id],$acc,$uri))
                return TRUE;
        }

        return FALSE;
    }

    private static function _in_list($list,$access,$uri)
    {
        if(empty($list) || empty($access) || empty($uri))
            return FALSE;

        $allow_urls = array();
        switch($access)
        {
            case 2:
                $allow_urls = array_merge($allow_urls,$list[$access]);
            case 1:
                $allow_urls = array_merge($allow_urls,$list[1]);
                break;
        }

        if(in_array($uri,$allow_urls))
            return TRUE;

        return FALSE;
    }
}
 
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
