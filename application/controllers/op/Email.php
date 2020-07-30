<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Email extends OP_Controller {

	protected $view_prefix  = 'email';
	protected $foreign_value = array(
		// Upgrade CI3 - Avoid circular form valition in CI3  - Start by TTM
		// 'newemail'     => 'trim|required|valid_email|matches[newemailconf]',
		'newemail'     => 'trim|required|valid_email',
		// Upgrade CI3 - Avoid circular form valition in CI3 - End by TTM
		'newemailconf' => 'trim|required|valid_email|matches[newemail]',
	);

	function index()
	{
		redirect(uri_class_string() . '/change');
	}

	function change()
	{
		$data = $this->setup_data();
		$data['message'] = $this->session->flashdata('message');
		$data['foreign'] = $this->session->flashdata('foreign');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $data['session'] = $this->member->get_member();
		$data['session'] = $this->member_lib->get_member();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('message');
		$this->parser->parse('email_change', $data);
	}

	function change_in()
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

		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('foreign', $data['foreign']);
			$this->session->set_flashdata('message', $data['message']);
			redirect('/' . dirname(uri_string()) . '/change');
		}

		$this->session->set_flashdata('foreign', $data['foreign']);
		redirect('/' . dirname(uri_string()) . '/change_confirm');
	}

	function change_confirm()
	{
		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $data['session'] = $this->member->get_member();
		$data['session'] = $this->member_lib->get_member();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->session->keep_flashdata('foreign');
		$this->parser->parse('email_change_confirm', $data);
	}

	function change_confirm_in()
	{
		$this->check_action();

		$data = $this->session->flashdata('foreign');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->change_email($data['newemail'], $data['token'])) {
		if ($this->member_lib->change_email($data['newemail'], $data['token'])) {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$this->changelog();
			$line = $this->lang->line('LOG:M2013');
			log_message('notice', sprintf($line, $data['newemail']));
			log_message('debug', $this->db->last_query());
			$line = $this->lang->line('M2013');
		} else {
			$this->session->keep_flashdata('foreign');
			$line = $this->lang->line('LOG:N4013');
			log_message('notice', sprintf($line, $data['newemail']));
			log_message('debug', $this->db->last_query());
			$line = $this->lang->line('N4013');
		}
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		redirect('/' . dirname(uri_string()) . '/changed');
	}

	function changed()
	{
		$data = $this->setup_data();
		$data['message'] = $this->session->flashdata('message');
		if (!empty($data['message']))
			$data['title'] = array_shift($data['message']);

		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('message');
		$this->parser->parse('email_changed', $data);
	}

	protected function changelog()
	{
		$this->load->model('histories_model');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->histories_model->log($this->member->get_userid(), '変更', 'メールアドレス');
		$this->histories_model->log($this->member_lib->get_userid(), '変更', 'メールアドレス');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
	}
}
