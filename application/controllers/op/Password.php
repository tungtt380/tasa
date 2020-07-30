<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password extends OP_Controller {

	protected $view_prefix  = 'password';
	protected $foreign_value = array(
		'newpassword' => 'trim|required|valid_password|min_length[4]',
		'newpassconf' => 'trim|required|valid_password|min_length[4]|matches[newpassword]',
	);

	public function index() {
		redirect(uri_class_string() . '/change');
	}

	public function change()
	{
		$data = $this->setup_data();
		$data['message'] = $this->session->flashdata('message');
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');
		$this->parser->parse('password_change', $data);
	}

	public function change_in()
	{
		$this->check_action();

		$data['foreign'] = $this->input->post();

		foreach($this->foreign_value as $key=>$val) {
			$this->form_validation->set_rules($key, 'lang:'.$key, $val);
		}

		if ($this->form_validation->run() == FALSE) {
			$data['message']['__all'] = validation_errors();
			foreach($this->foreign_value as $key=>$val) {
				$data['message'][$key] = strip_tags(form_error($key));
			}
		}
		foreach($this->foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}

		// Upgrade CI3 - Prevent error when data length > 60 - Start by TTM
		if(strlen($data['foreign']['newpassword']) >60)
		{
			$data['foreign']['newpassword'] = substr($data['foreign']['newpassword'], 0, 60);
		}
		// Upgrade CI3 - Prevent error when data length > 60 - End by TTM

		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('foreign', $data['foreign']);
			$this->session->set_flashdata('message', $data['message']);
			redirect(uri_class_string() . '/change');
		} else {
			$this->session->set_flashdata('foreign', $data['foreign']);
			redirect(uri_class_string() . '/change_confirm');
		}
	}

	public function change_confirm()
	{
		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');
		$this->parser->parse('password_change_confirm', $data);
	}

	public function change_confirm_in()
	{
		$this->check_action();

		$data = $this->session->flashdata('foreign');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->change_password($data['newpassword'])) {
		if ($this->member_lib->change_password($data['newpassword'])) {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$this->changelog();
			$line = $this->lang->line('LOG:M2011');
			log_message('notice', sprintf($line, base64_encode($data['newpassword'])));
			log_message('debug', $this->db->last_query());
			$line = $this->lang->line('M2011');
			$message = explode("\n", $line);
		} else {
			$this->session->keep_flashdata('foreign');
			$line = $this->lang->line('LOG:N4011');
			log_message('notice', sprintf($line, base64_encode($data['newpassword'])));
			log_message('debug', $this->db->last_query());
			$line = $this->lang->line('N4011');
			$message = explode("\n", $line);
		}
		$this->session->set_flashdata('message', $message);
		redirect(uri_class_string() . '/changed');
	}

	public function changed()
	{
		$data = $this->setup_data();
		$data['message'] = $this->session->flashdata('message');
		if (!empty($data['message']))
			$data['title'] = array_shift($data['message']);

		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('message');
		$this->parser->parse('password_changed', $data);
	}

	protected function changelog()
	{
		$this->load->model('histories_model');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->histories_model->log($this->member->get_userid(), '変更', 'パスワード');
		$this->histories_model->log($this->member_lib->get_userid(), '変更', 'パスワード');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
	}
}

/* End of file password.php */
/* Location: ./application/controllers/(:any)/password.php */
