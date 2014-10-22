<?php

require_once(APPPATH . "third_party/redis/RedisBase.class.php");

class ExpireHelperRedis extends RedisBase {

	public static $prefix = 'expire';
	public static $module = 'cache';
	public static $expire = 60;             // 一分钟
	public static $captcha_expire = 300;

	public static function setMobileExpire($mobile) {
		return parent::set('sem_mobile:' . $mobile, 1, self::$expire);
	}

	public static function getMobile($mobile) {
		return parent::get('sem_mobile:' . $mobile);
	}

	public static function setMobileCaptcha($mobile, $captcha) {
		return parent::set('sem_captcha:' . $mobile, $captcha, self::$captcha_expire);
	}

	public static function getMobileCaptcha($mobile) {
		return parent::get('sem_captcha:' . $mobile);
	}

    public static function setAccountSyn($user_id)
    {
        return parent::set('sync_account:'.$user_id,1,600);
    }

	public static function getAccountSyn($user_id) {
		return parent::get('sync_account:'.$user_id);
	}

    public static function set_monitor_date($keyword_id)
    {
        return parent::set('monitor:'.$keyword_id,1,24*3600);
    }

	public static function get_monitor_date($keyword_id) {
		return parent::get('monitor:'.$keyword_id);
	}

	public static function del_monitor_date($keyword_id) {
		return parent::del('monitor:'.$keyword_id);
	}

    //参考出价中恢复资源标记
    public static function set_refbid_calc($sem_id)
    {
        return parent::set('refbid:'.$sem_id,time(),660);
    }

	public static function get_refbid_calc($sem_id) {
		return parent::get('refbid:'.$sem_id);
	}
}

