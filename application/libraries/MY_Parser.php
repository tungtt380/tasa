<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Parser extends CI_Parser {

	protected $CI;
	protected $theme_location;

	public function __construct() {
		// Upgrade CI3 - Update method calling - Start by TTM
		// $this->CI = get_instance();
		$this->CI =& get_instance();
		// Upgrade CI3 - Update method calling - End by TTM
		// Upgrade CI3 - Replace smarty library name to avoid duplicate - Start by TTM
		// $this->load->library('smarty')
		$this->load->library('smarty_lib');
		// Upgrade CI3 - Replace smarty library name to avoid duplicate - End by TTM
	}

	public function __get($blah) {
		return $this->CI->$blah;
	}

	public function parse($template, $data='', $return=FALSE, $use_theme=FALSE)
	{
		if ($template == '') {
			return FALSE;
		}

		if ($use_theme != FALSE) {
			$this->load->library('template');
			$template = 'file:/'.$this->template->get_theme_path().$template.'';
		}

		if (!stripos($template, '.')) {
			// Upgrade CI3 - Replace smarty library name to avoid duplicate - Start by TTM
			// $template = $template.'.'.$this->smarty->template_ext;
			$template = $template.'.'.$this->smarty_lib->template_ext;
			// Upgrade CI3 - Replace smarty library name to avoid duplicate - End by TTM
		}

		// Merge in any cached variables with our supplied variables
		if (is_array($data)) {
			$data = array_merge($data, $this->load->get_vars());
		}

		if ($data) {
			foreach ($data as $key=>$val) {
				// Upgrade CI3 - Replace smarty library name to avoid duplicate - Start by TTM
				// $this->smarty->assign($key, $val);
				$this->smarty_lib->assign($key, $val);
				// Upgrade CI3 - Replace smarty library name to avoid duplicate - End by TTM
			}
		}
		
		// Upgrade CI3 - Replace smarty library name to avoid duplicate - Start by TTM
		// $output = $this->smarty->fetch($template);
		$output = $this->smarty_lib->fetch($template);
		// Upgrade CI3 - Replace smarty library name to avoid duplicate - End by TTM

		if ($return === FALSE) {
			$this->output->append_output($output);
		}
		return $output;
	}

	public function parse_string($template, $data='', $return=FALSE, $use_theme=FALSE)
	{
		return $this->parse($template, $data, $return, $use_theme);
	}

}

/* End of file MY_Parser.php */
/* Location: ./application/libraries/MY_Parser.php */
