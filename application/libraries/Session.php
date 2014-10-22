<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class CI_Session {
	
	var $sess_save_handler	= 'redis';		// redis, memcache
	var $sess_server		= array(
		array(
			'host'			=> '127.0.0.1',
			'port'			=> '6379',
			'params'		=> array(
				'prefix'			=> 'SEM:',
				'persistent'		=> 0,
				'weight'			=> 1,
				'timeout'			=> 30,
			)
		),
	);
	var $sess_cookie_name	= 'PHPSESSID';
	var $sess_expiration	= 0;
	var $flash_key			= 'flash';		// prefix for "flash" variables (eg. flash:new:message)
	
	public function __construct() {
		log_message('debug', "Redis_session Class Initialized");
		
		$instance =& get_instance();
		foreach (array('sess_save_handler', 'sess_server', 'sess_cookie_name', 'sess_expiration', 'flash_key') as $key) {
			$instance->config->item($key) && $this->$key = $instance->config->item($key);
		}

		if (!is_array(reset($this->sess_server))) {
			$this->sess_server = array($this->sess_server);
		}
		if ($this->sess_expiration == 0) {
			$this->sess_expiration = (60*60*24*14);
		}

		$this->_sess_run();
	}

	/**
	 * Starts up the session system for current request
	 */
	private function _sess_run() {
		// session
		ini_set('session.save_handler', $this->sess_save_handler);
		$path = array();
		foreach ($this->sess_server as $server) {
			if (isset($server['host']) && isset($server['port'])) {
				$path[] = "tcp://{$server['host']}:{$server['port']}?" . http_build_query(isset($server['params']) ? $server['params'] : array());
			}
		}
		if (empty($path)) {
			show_error('Session save_path is empty');
		}
		ini_set('session.save_path', implode(',', $path));

		ini_set('session.gc_maxlifetime',   $this->sess_expiration);	// 过期时间
		// cookie
		ini_set('session.cookie_secure',    0);		// 0 http://    1 https://
		ini_set('session.cookie_httponly',  1);		// 不让JS读取session的cookie
		session_name($this->sess_cookie_name);

		session_start();
		// delete old flashdata (from last request)
		$this->_flashdata_sweep();
		// mark all new flashdata as old (data will be deleted before next request)
		$this->_flashdata_mark();
	}
	
	/**
	 * Destroys the session and erases session storage
	 */
	public function sess_destroy() {
		session_unset();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
    }
	
	/**
	 * Reads given session attribute value
	 */    
    public function userdata($item) {
		if (strcmp($item, 'session_id') === 0) {
			return session_id();
		} else {
			return isset($_SESSION[$item]) ? $_SESSION[$item] : FALSE;
		}
	}

	public function all_userdata() {
		return $_SESSION;
	}

	/**
	 * Sets session attributes to the given values
	 */
	public function set_userdata($newdata = array(), $newval = '') {
		if (is_string($newdata)) {
			$newdata = array($newdata => $newval);
		}
		
		if (count($newdata) > 0) {
			foreach ($newdata as $key => $val) {
				$_SESSION[$key] = $val;
			}
		}
	}
	
	/**
	 * Erases given session attributes
	 */
	public function unset_userdata($newdata = array()) {
		if (is_string($newdata)) {
			$newdata = array($newdata => '');
		}
		
		if (count($newdata) > 0) {
			foreach ($newdata as $key => $val) {
				unset($_SESSION[$key]);
			}
		}
	}
	
	// ------------------------------------------------------------------------
	/**
	 * Add or change flashdata, only available
	 * until the next request
	 *
	 * @access    public
	 * @param    mixed
	 * @param    string
	 * @return    void
	 */
	public function set_flashdata($newdata = array(), $newval = '') {
		if (is_string($newdata)) {
			$newdata = array($newdata => $newval);
		}
		if (count($newdata) > 0) {
			foreach ($newdata as $key => $val) {
				$flash_key = $this->flash_key.':new:'.$key;
				$this->set_userdata($flash_key, $val);
			}
		}
	}
	
	// ------------------------------------------------------------------------
	/**
	 * Keeps existing flashdata available to next request.
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function keep_flashdata($key) {
		$old_flash_key = $this->flash_key.':old:'.$key;
		$value = $this->userdata($old_flash_key);

		$new_flash_key = $this->flash_key.':new:'.$key;
		$this->set_userdata($new_flash_key, $value);
	}

	// ------------------------------------------------------------------------
	/**
	 * Fetch a specific flashdata item from the session array
	 *
	 * @access		public
	 * @param		string
	 * @return		string
	 */
	public function flashdata($key) {
		$flash_key = $this->flash_key.':old:'.$key;
		return $this->userdata($flash_key);
	}
	
	// ------------------------------------------------------------------------
	/**
	 * Identifies flashdata as 'old' for removal
	 * when _flashdata_sweep() runs.
	 *
	 * @access    private
	 * @return    void
	 */
	private function _flashdata_mark() {
		foreach ($_SESSION as $name => $value) {
			$parts = explode(':new:', $name);
			if (is_array($parts) && count($parts) == 2) {
				$new_name = $this->flash_key.':old:'.$parts[1];
				$this->set_userdata($new_name, $value);
				$this->unset_userdata($name);
			}
		}
	}

	// ------------------------------------------------------------------------
	/**
	 * Removes all flashdata marked as 'old'
	 *
	 * @access    private
	 * @return    void
	 */
	private function _flashdata_sweep() {
		foreach ($_SESSION as $name => $value) {
			$parts = explode(':old:', $name);
			if (is_array($parts) && count($parts) == 2 && $parts[0] == $this->flash_key) {
				$this->unset_userdata($name);
			}
		}
	}
}
// END Session Class

/* End of file Session.php */
/* Location: ./system/libraries/Session.php */
