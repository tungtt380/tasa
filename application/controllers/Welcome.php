<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	public function index()
	{
		if (date('Y-m-d H:i:s') <= '2018-07-23 10:00:00') {
			$this->CI = get_instance();
			$this->load->library('parser');
			$this->parser->parse('comingsoon.html');
		} elseif ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp' || $_SERVER['HTTP_HOST'] == 'cus2019.tokyoautosalon.jp') {
			redirect('/agreement');
		} else {
			redirect('/member/login');
		}
	}
}

// End of ./application/controllers/welcome.php
// vim:ts=4
