<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'third_party/PHPExcel.php';

class Excel_lib extends PHPExcel {

	protected $CI;

	public function __construct()
	{
		parent::__construct();

		// Store the Codeigniter super global instance... whatever
		$this->CI = get_instance();
		$this->CI->load->config('excel');
	}
}

/* End of file Excel.php */
/* Location: ./application/library/excel.php */
