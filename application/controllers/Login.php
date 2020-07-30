<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
		$this->load->library('parser');
	}

	public function index()
	{
		redirect('/ex/login');
	}
}

// End of file ./application/controllers/login.php
// vim:ts=4
