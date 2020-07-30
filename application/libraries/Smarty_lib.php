<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'third_party/Smarty/Smarty.class.php';

// ------------------------------------------------------------------------

/**
 * Smarty 3.0 Parse Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Smarty
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/smarty.html
 */

class Smarty_lib extends Smarty {

	protected $CI;

	public function __construct($config = array())
	{
		parent::__construct();

		// Store the Codeigniter super global instance... whatever
		$this->CI =& get_instance();
		$this->CI->load->config('smarty');
		$this->template_dir = $this->CI->config->item('template_directory');
		$this->compile_dir  = $this->CI->config->item('compile_directory');
		$this->cache_dir    = $this->CI->config->item('cache_directory');
		$this->config_dir	= $this->CI->config->item('config_directory');
		$this->template_ext = $this->CI->config->item('template_ext');
		$this->error_reporting = E_ALL & ~E_NOTICE;
		$this->exception_handler = null;

		if (count($config) > 0) {
			$this->initialize($config);
		}

		// Add all helpers to plugins_dir
		$helpers = glob(APPPATH.'helpers/', GLOB_ONLYDIR|GLOB_MARK);

		foreach ($helpers as $helper) {
			$this->plugins_dir[] = $helper;
		}
		// Should let us access Codeigniter stuff in views
		$this->assign('this', $this->CI);
	}

	public function initialize($config = array())
	{
		foreach ($config as $key => $val) {
			if (isset($this->$key)) {
				$this->$key = $val;
			}
		}
	}

}

/* End of file Smarty.php */
/* Location: ./application/libraries/Smarty.php */
