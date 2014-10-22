<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . "third_party/redis/RedisBase.class.php");

class Client_cookie_redis extends RedisBase {

	public static $module = 'client_cookie';
	public static $prefix = '';

}

