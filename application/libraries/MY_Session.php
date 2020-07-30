<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * MY Session Class(by Cookie)
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Sessions
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/sessions.html
 */
class MY_Session {

	protected $_flash = array();
	protected $flashdata_key = 'flash';

	public function __construct($params = array())
	{
		log_message('debug', 'Session routines initialized');
		if (!$this->sess_read()) {
			$this->sess_create();
		} else {
			$this->sess_update();
		}
		$this->_flashdata_sweep();
		$this->_flashdata_mark();
//		if ($_SESSION['LAST_ACTIVITY'] > $_SERVER['REQUEST_TIME'] + 10));
//			session_regenerate_id(TRUE);
//		}
		$_SESSION['LAST_ACTIVITY'] = $_SERVER['REQUEST_TIME'];
		log_message('debug', 'Session routines successfully run');
	}

	function sess_read()
	{
		session_start();
		return TRUE;
	}
	function sess_write() {		// override - Write the session data
	}
	function sess_create() {	// override - Create a new session
		session_start();
	}
	function sess_update() {	// override - Update an existing session
	}
	function sess_destroy()		// override - Destroy the current session
	{
		session_unset();
		session_destroy();
	}

	// --------------------------------------------------------------------
	function userdata($item)		// compat - Fetch a specific item from the session array
	{
		return (!isset($_SESSION[$item])) ? FALSE : $_SESSION[$item];
	}
	function all_userdata($item)	// compat - Fetch all session data
	{
		return (!isset($_SESSION)) ? FALSE : $_SESSION;
	}
	function set_userdata($newdata = array(), $newval = '')
	{
		if (is_string($newdata)) {
			$newdata = array($newdata=>$newval);
		}
		if (count($newdata) > 0) {
			foreach ($newdata as $key=>$val) {
				$_SESSION[$key] = $val;
			}
		}
		$this->sess_write();
	}
	function unset_userdata($newdata = array())
	{
		if (is_string($newdata)) {
			$newdata = array($newdata=>'');
		}
		if (count($newdata) > 0) {
			foreach ($newdata as $key=>$val) {
				unset($_SESSION[$key]);
			}
		}
		$this->sess_write();
	}

	// --------------------------------------------------------------------
	function set_flashdata($newdata=array(), $newval='')
	{
		if (is_string($newdata)) {
			$newdata = array($newdata=>$newval);
		}

		if (count($newdata) > 0) {
			foreach ($newdata as $key=>$val) {
				$flashdata_key = $this->flashdata_key.':new:'.$key;
				$_SESSION[$flashdata_key] = $val;
			}
		}
	}

	function keep_flashdata($key)
	{
		$old_flashdata_key = $this->flashdata_key.':old:'.$key;
		$new_flashdata_key = $this->flashdata_key.':new:'.$key;
		$this->set_userdata($new_flashdata_key, $this->flashdata($key));
	}

	function flashdata($key)
	{
		$flashdata_key = $this->flashdata_key.':old:'.$key;
		return (!isset($_SESSION[$flashdata_key])) ? FALSE : $_SESSION[$flashdata_key];
	}

	protected function _flashdata_mark()
	{
		foreach($_SESSION as $key=>$value) {
			$parts = explode(':new:', $key);
			if (is_array($parts) && count($parts) === 2) {
				$newkey = $this->flashdata_key.':old:'.$parts[1];
				$_SESSION[$newkey] = $value;
				unset($_SESSION[$key]);
			}
		}
	}

	protected function _flashdata_sweep()
	{
		foreach($_SESSION as $key=>$value) {
			if (strpos($key, ':old:')) {
				unset($_SESSION[$key]);
			}
		}
	}

	protected function _serialize($data) {}			// override - Serialize an array
	protected function _unserialize($data) {}		// override - Unserialize
	protected function _sess_gc(){}
}
