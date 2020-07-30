<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends OP_Controller {

	public function __construct()
	{
		parent::__construct();

		$ar = array('/op','/op/exhibitor/*','/test','/test/change/*', '/test/regist*');
		if($this->uri_compare_regex($ar)) {
			echo "TRUE";
		} else {
			echo "FALSE";
		}
	}

	public function index()
	{
	}
}

/* End of file test.php */
/* Location: ./application/controllers/(:any)/test.php */
