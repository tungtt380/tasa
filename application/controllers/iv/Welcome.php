<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends OP_Controller {

	public function index()
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $username = $this->member->get_username();
		// $rolename = $this->member->get_rolename();
		$username = $this->member_lib->get_username();
		$rolename = $this->member_lib->get_rolename();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM

		$permission = $this->config->item('permission');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// $rolepermission = $permission[$rolename];
		// $goto = array_shift($rolepermission);
		// $home = array_shift($rolepermission);
		if(!empty($permission)) {
			$rolepermission = $permission[$rolename];
			$goto = array_shift($rolepermission);
			$home = array_shift($rolepermission);
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM

		if (isset($home) && $home != '') {
			redirect($home);
		}
		$data = $this->setup_data();
		$this->parser->parse('welcome_'.__FUNCTION__, $data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/op/welcome.php */
