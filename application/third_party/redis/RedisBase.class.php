<?PHP

class RedisBase {
	private static $connections = array();
	static $prefix = NULL;

	private static function connect($host, $port, $timeout = 5) {
		try {
			$redis = new Redis();
			$redis->connect($host, $port, $timeout);
			return $redis;
		} catch (Exception $e) {
			throw new Exception("failed to connect to the redis server $host:$port\t" . $e->getMessage());
		}
		return false;
	}

	private static function getConfig($module) {
		static $config = array();
		if (!isset($config[$module])) {
			if (!defined('ENVIRONMENT') OR !file_exists($file_path = APPPATH . 'config/' . ENVIRONMENT . '/redis.php')) {
				if (!file_exists($file_path = APPPATH . 'config/redis.php')) {
					show_error('The configuration file redis.php does not exist.');
				}
			}

			include($file_path);
			if (isset($redis[$module]) && is_array($redis[$module]) && !empty($redis[$module])) {
				$config[$module] = $redis[$module];
			} else {
				log_message('error', 'Invalid redis requested: ' . $module);
				show_error("can not connect to the redis server because {$module} config is empty");
			}
		}
		return $config[$module];
	}

	private static function getModule() {
		$class = get_called_class();
		if (isset($class::$module)) {
			return $class::$module;
		}
		return 'cache';
	}

	private static function getRedis($key = NULL) {
		$config = self::getConfig(self::getModule());
		$count = count($config);
		$server_id = is_null($key) ? 0 : (hexdec(substr(md5($key), 0, 2)) % $count);

		if (!isset(self::$connections[$server_id])) {
			$host = $config[$server_id]['host'];
			$port = $config[$server_id]['port'];
			self::$connections[$server_id] = self::connect($host, $port);
		}
		return self::$connections[$server_id]; 
	}

	protected static function getPrefix() {
		$class = get_called_class();
		if (!is_null($class::$prefix)) {
			return $class::$prefix;
		}
		return get_called_class();
	}

	protected static function keyAddPrefix($key) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		if (!empty($prefix)) {
			$key = "{$prefix}:{$key}";
		}
		return $key;
	}

	//规范输入输出
	private static function encodeString($input) {
		$output = serialize($input);
		return $output;
	}

	private static function decodeString($input) {
		$output = unserialize($input);
		return $output;
	}

	///////////////////
	////    key    ////
	///////////////////
	public static function del($key) {
		$key = self::keyAddPrefix($key);
		$ret = self::getRedis($key)->del($key);
		return $ret;
	}

	public static function expire($key, $expire) {
		$key = self::keyAddPrefix($key);
		$ret = self::getRedis($key)->expire($key, $expire);
		return $ret;
	}

	///////////////////
	////  string   ////
	///////////////////
	public static function set($key, $value, $expire = 0, $decode = TRUE) {
		$key = self::keyAddPrefix($key);
		if ($decode) {
			$value = self::encodeString($value);
		}

		if ($expire == 0) {
			$ret = self::getRedis($key)->set($key, $value);
		} else {
			$ret = self::getRedis($key)->setex($key, $expire, $value);
		}
		return $ret; 
	}

	public static function get($key) {
		$key = self::keyAddPrefix($key);
		$result = self::getRedis($key)->get($key);
		$result = self::decodeString($result);
		return $result;
	}

	//////////////////
	////   list   ////
	//////////////////
	public static function lpush($key, $value) {
		$key = self::keyAddPrefix($key);
		$ret = self::getRedis($key)->lpush($key, $value);
		return $ret;
	}

	public static function rpush($key, $value) {
		$key = self::keyAddPrefix($key);
		$ret = self::getRedis($key)->rpush($key, $value);
		return $ret;
	}

	public static function llen($key) {
		$key = self::keyAddPrefix($key);
		$ret = self::getRedis($key)->llen($key);
		return $ret;
	}

	public static function lrange($key, $offset, $limit) {
		$key = self::keyAddPrefix($key);
		$stop = $limit + $offset - 1;
		$result = self::getRedis($key)->lrange($key, $offset, $stop);
		return $result;
	}

	//////////////////
	//// hash set ////
	//////////////////
	public static function hset($key, $field, $value) {
		$key = self::keyAddPrefix($key);
		$value = self::encodeString($value);
		$ret = self::getRedis($key)->hset($key, $field, $value);
		return $ret;
	}

	public static function hget($key, $field) {
		$key = self::keyAddPrefix($key);
		$result = self::getRedis($key)->hget($key, $field);
		return self::decodeString($result);
	}

	public static function hmset($key, $array) {
		if (!is_array($array) || empty($array)) return false;

		$key = self::keyAddPrefix($key);

		foreach ($array as &$value) {
			$value = self::encodeString($value);    
		}

		$ret = self::getRedis($key)->hmset($key, $array);
		return $ret;
	}

	public static function hmget($key, $fields, $decode = true) {
		if (!is_array($fields) || empty($fields)) return false;

		$key = self::keyAddPrefix($key);
		$result = self::getRedis($key)->hmget($key, $fields);
		if (!$decode) {
			return $result;
		}
		foreach ($result as &$value) {
			$value = self::decodeString($value);    
		}
		return $result;
	}

	public static function hgetall($key) {
		$key = self::keyAddPrefix($key);
		$result = self::getRedis($key)->hgetall($key);
		foreach ($result as &$value) {
			$value = self::decodeString($value);    
		}
		return $result;
	}

	//////////////////
	/// sorted set ///
	//////////////////
	public static function zadd($key, $score, $member) {
		$key = self::keyAddPrefix($key);
		return self::getRedis($key)->zadd($key, $score, $member);
	}

	public static function zrevrange($key, $offset, $limit, $with_score = TRUE) {
		$key = self::keyAddPrefix($key);
		return self::getRedis($key)->zrevrange($key, $offset, $limit + $offset - 1, $with_score);
	}

	public static function zrange($key, $offset, $limit, $with_score = TRUE) {
		$key = self::keyAddPrefix($key);
		return self::getRedis($key)->zrange($key, $offset, $limit + $offset - 1, $with_score);
	}

	public static function zcard($key) {
		$key = self::keyAddPrefix($key);
		return self::getRedis($key)->zcard($key);
	}

	public static function zscore($key, $member) {
		$key = self::keyAddPrefix($key);
		return self::getRedis($key)->zscore($key, $member);
	}

	public static function zaddnx($key, $score, $member) {
		$key = self::keyAddPrefix($key);
		$existence = self::zscore($key, $member);
		if (!empty($existence)) {
			file_put_contents('/tmp/yuanzeng.log', 'zaddnx for key ' . $member . "\n", FILE_APPEND);
			return TRUE; 
		}

		return self::getRedis($key)->zadd($key, $score, $member);
	}

}
