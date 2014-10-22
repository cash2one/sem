<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model {

	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		log_message('debug', "Model Class Initialized");
        $id = Auth_filter::current_sem_id();
        if(!empty($id))
        {
            $this->swan_slaves = 'slaves'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
            $this->swan_master = 'master'.Auth_filter::current_sem_id()%SWAN_DB_COUNT;
        }
	}

	/**
	 * __get
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param	string
	 * @access private
	 */
	function __get($key)
	{
		$CI =& get_instance();
		return $CI->$key;
	}

	/**
	 * 获取数据库列名
	 **/
	final public function getfields() {
		return $this->databases->{static::$database}->slaves->list_fields(static::$table);
	}

	/**
	 * 写数据库
	 **/
	final public function insert($params) {
		if (empty($params)) {
			return FALSE;
		}
		$connection = $this->databases->{static::$database}->{$this->swan_master};
		$connection->set($params);
		$connection->insert(static::$table);
		return $connection->insert_id();
	}
	
	/**
	 * 更新数据库
	 **/
	final public function update_params($params, $condition) {
		if (empty($params) || empty($condition)) {
			return FALSE;
		}
		$connection = $this->databases->{static::$database}->{$this->swan_master};
		$connection->set($params);
		foreach ($condition as $key => $value) {
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
		$connection->update(static::$table);
		return $connection->affected_rows();
	}

	/**
	 * 读数据库模块
	 * protocted static $database
	 * protocted static $table
	 **/
	final public function get_by_params($params, $cols = array(), $extra_params = array()) {
		if (empty($params)) {
			return array();
		}
		$default_extra_params = array(
			'from_master'	=> FALSE,
			'groupby'		=> FALSE,
			'having'		=> FALSE,
			'orderby'		=> FALSE,
			'offset'		=> FALSE,
			'limit'			=> FALSE,
			'hash_key'		=> FALSE,
		);
		$extra_params = array_merge($default_extra_params, $extra_params);
		if ($extra_params['from_master']) {
			$connection = $this->databases->{static::$database}->{$this->swan_master};
		} else {
			$connection = $this->databases->{static::$database}->{$this->swan_slaves};
		}
		!empty($cols) && $connection->select($cols);
		$connection->from(static::$table);
		foreach ($params as $key => $value) {
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
		!empty($extra_params['groupby']) && $connection->group_by($extra_params['groupby']);
		!empty($extra_params['having']) && $connection->having($extra_params['having']);
		!empty($extra_params['orderby']) && $connection->order_by($extra_params['orderby']);
		!empty($extra_params['offset']) && $connection->offset($extra_params['offset']);
		!empty($extra_params['limit']) && $connection->limit($extra_params['limit']);
		$query = $connection->get();
		$result = $query->result_array();
		if (!empty($extra_params['hash_key'])) {
			$this->load->helper('array_util');
			$result = change_data_key($result, $extra_params['hash_key']);
		}
		return $result;
	}

	/**
	 * 删除数据库
	 **/
	final public function delete_params($params) {
		if (empty($params)) {
			return FALSE;
		}
		$connection = $this->databases->{static::$database}->{$this->swan_master};
		foreach ($params as $key => $value) {
			if (!is_array($value)) {
				$connection->where($key, $value);
			} elseif (isset($value['op']) && isset($value['value'])) {
				$connection->{$value['op']}($key, $value['value']);
			} else {
				$connection->where_in($key, $value);
			}
		}
		$connection->delete(static::$table);
		return $connection->affected_rows();
	}

}
// END Model Class

/* End of file Model.php */
/* Location: ./system/core/Model.php */
