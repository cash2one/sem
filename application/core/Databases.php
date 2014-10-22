<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class HZ_Databases {

	/**
	 * 数据库联接
	 **/
	private $databases = array();

	/**
	 * 配置
	 **/
	private $config = array();

	/**
	 * __get magic method
	 **/
	public function __get($database) {
		if (isset($this->databases[$database])) {
        //  用缓存会出问题的，注释了
		//	return $this->databases[$database];
		}

		if (empty($this->config)) {
			$this->getConfig();
		}

		if (!isset($this->config[$database])) {
			// 数据库不存在
			log_message('error', 'Invalid database requested: ' . $database);
			show_error("Invalid database requested: " . $database);
		}

// bug : load_class 会变成全局静态变量，Databasecluster 不能成为静态变量
//		$DBC = & load_class('Databasecluster', 'core', 'HZ_');
		$this->databases[$database] = new Databasecluster($this->config[$database]);
		return $this->databases[$database];
	}

	private function getConfig() {
		if (!defined('ENVIRONMENT') OR !file_exists($file_path = APPPATH . 'config/' . ENVIRONMENT . '/databases.php')) {
			if (!file_exists($file_path = APPPATH . 'config/databases.php')) {
				show_error('The configuration file databases.php does not exist.');
			}
		}

		include($file_path);
		if (isset($databases)) {
			$this->config = $databases;
		}
	}

	public function close() {
		unset($this->databases);
	}
}


/**
  * 单个数据库联接
  * 联接簇
  */
class Databasecluster {

	/**
	 * 数据库联接
	 **/
	private $connection = array();

	/**
	 * 配置
	 **/
	private $config = array();
	private $default_db_parameter = array(
		'hostname'		=> 'localhost',
		'port'			=> 3306,
		'username'		=> '',
		'password'		=> '',
		'database'		=> 'database',
		'dbdriver'		=> 'pdo',
		'dbprefix'		=> '',
		'pconnect'		=> FALSE,
		'db_debug'		=> FALSE,
		'cache_on'		=> FALSE,
		'cachedir'		=> '',
		'char_set'		=> 'utf8',
		'dbcollat'		=> 'utf8_general_ci',
		'swap_pre'		=> '',
		'autoinit'		=> TRUE,
		'striction'		=> FALSE,
	);
	
	/**
	 * __get magic method
	 **/
	public function __get($type) {
        //提取出序号
        $match = array();
        preg_match('/^([a-zA-Z]+)(\d+)$/',$type,$match);

        if(!empty($match))
        {
            $type = trim($match[1]);
            $num = trim($match[2]);
        }
		$type = strtolower($type);
		if (isset($this->connection[$type])) {
			return $this->connection[$type];
		}

		if (!isset($this->config[$type])) {
			// type 不存在
			log_message('error', 'Invalid type requested: ' . $type);
			show_error("Invalid type requested: " . $type);
		}

		$conf = $this->config[$type];
		//根据num值取出数据库配置
		if (is_array(reset($conf))) {
            //if(empty($num))
                //$num = SWAN_DB_DEFAULT_PREFIX ;
			$conf = $conf[$num];
		}
		$conf = array_merge($this->default_db_parameter, $conf);			// 布尔值变成了数字
		// for pdo
		if (isset($conf['dbdriver']) && (strcmp($conf['dbdriver'], 'pdo') === 0)) {
			$conf['hostname'] = 'mysql:host=' . $conf['hostname'];
		}

		require_once(BASEPATH . 'database/DB.php');
		$this->connection[$type] =& DB($conf, TRUE);
		return $this->connection[$type];
	}

	public function __construct($config = array()) {
		$this->config = $config;
	}

	public function __destruct() {
		foreach($this->connection as $connection) {
			$connection->close();
		}
	}
}

// END HZ_Databases class

/* End of file Databases.php */
/* Location: ./application/core/Databases.php */

