<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends OP_Controller {

	public function index()
	{
/*

		$username = $this->member->get_username();
		$rolename = $this->member->get_rolename();

		$permission = $this->config->item('permission');
		$rolepermission = $permission[$rolename];
		$goto = array_shift($rolepermission);
		$home = array_shift($rolepermission);
		if (isset($home) && $home != '') {
			redirect($home);
		}
*/
		$data = $this->setup_data();
		$this->parser->parse('welcome_'.__FUNCTION__, $data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/op/welcome.php */
