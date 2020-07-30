<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * MY Logging Class
 *
 * ログのレベルを７段階に修正
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Logging
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/general/errors.html
 */
class MY_Log extends CI_Log {

	protected $_log_path;
	protected $_threshold	= 1;
	protected $_date_fmt	= 'Y-m-d H:i:s.u';
	protected $_enabled	= TRUE;
	protected $_levels	= array(
		'DEBUG'    =>   1,
		'INFO'     =>   2,
		'NOTICE'   =>   4,
		'WARNING'  =>   8,
		'ERROR'    =>  16,
		'ALERT'    =>  32,
		'CRITICAL' =>  64,
		'ALL'      => 255,
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$config =& get_config();

		$this->_log_path = ($config['log_path'] != '') ? $config['log_path'] : APPPATH.'logs/';

		if ( ! is_dir($this->_log_path) OR ! is_really_writable($this->_log_path))
		{
			$this->_enabled = FALSE;
		}

		if (is_numeric($config['log_threshold']))
		{
			$this->_threshold = $config['log_threshold'];
		}

		if ($config['log_date_format'] != '')
		{
			$this->_date_fmt = $config['log_date_format'];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	public function write_log($level = 'error', $msg, $php_error = FALSE)
	{
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}

		$level = strtoupper($level);

		if ( ! isset($this->_levels[$level]) OR ! ($this->_levels[$level] & $this->_threshold))
		{
			return FALSE;
		}

		$filepath = $this->_log_path.'log-'.date('Y-m-d').EXT;
		$message  = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

//		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";
		$message .= date($this->_date_fmt) . ' [' . strtolower($level) . '] ' . $msg . "\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}

}
// END MY_Log Class

/* End of file Log.php */
/* Location: ./application/libraries/Log.php */