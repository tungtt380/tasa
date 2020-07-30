<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Member extends MY_Controller {

	protected $foreign_value = array( // 入力チェック用に使用するカラムとパターン
		'username'  => 'trim|required|xss_clean|valid_username',
		'password'  => 'trim|required|xss_clean|valid_password',
		'remember'  => 'trim|xss_clean',
	);

	public function __construct()
	{
		parent::__construct();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->load->library('member');
		$this->load->library('member_lib');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->load->library('parser');
	}

	public function index()
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->is_login()) {
		if ($this->member_lib->is_login()) {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$this->redirect_portal();
		}
		redirect('/' . uri_string() . '/login', 'location', 302);
	}

	public function login()
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->is_login()) {
		if ($this->member_lib->is_login()) {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$this->redirect_portal();
		}

		$data = $this->session->flashdata(get_class());
		$this->session->keep_flashdata(get_class());

		if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp' && uri_string() == 'ex/login' && date('Y-m-d H:i:s') <= '2016-11-07 10:00:00') {
			$this->parser->parse('member_sorry.html', $data);
		} else {
			$this->parser->parse('member_login.html', $data);
		}
	}

	public function login_in()
	{
		$this->check_action();

		$data = array();
		$data['foreign'] = array_intersect_key($this->input->post(), $this->foreign_value);
		$data['message'] = array();

		// 入力値をチェック
		foreach($this->foreign_value as $key=>$val) {
			$this->form_validation->set_rules($key, '', $val);
		}
		if ($this->form_validation->run() == FALSE) {
			$data['message']['__all'] = validation_errors();
			foreach($this->foreign_value as $key=>$val) {
				$data['message'][$key] = strip_tags(form_error($key));
			}
		}

		// 入力値はフィルタするため、実際のデータはここで格納する
		foreach($this->foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力不備の場合は、元の画面に戻る
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			redirect('/' . dirname(uri_string()) . '/login');
		}

		// ログインを試みる
		if (!$this->member_lib->login($data['foreign']['username'], $data['foreign']['password'], $data['foreign']['remember'])) {
			$data['message'] = array('username'=>'ユーザ名orパスワードが違います', 'password'=>'ユーザ名orパスワードが違います');
			$this->session->set_flashdata(get_class(), $data);
			redirect('/' . dirname(uri_string()) . '/login');
		}

		$this->load->model('histories_model');

		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->histories_model->log($this->member->get_userid(),'ログイン');
		$this->histories_model->log($this->member_lib->get_userid(),'ログイン');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->redirect_portal();
	}

	// 各ユーザのホームはユーザが持っている権限の一番最初に記述してあるページとする
	private function redirect_portal()
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $username = $this->member->get_username();
		// $rolename = $this->member->get_rolename();
		$username = $this->member_lib->get_username();
		$rolename = $this->member_lib->get_rolename();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM

		$this->config->load('permission', FALSE, TRUE);
		$permission = $this->config->item('permission');

		if (!isset($permission[$rolename])) {
			log_message('notice', sprintf('Permission denied %s /%s', $username, uri_string()));
			$this->parser->parse('prohibited');
		} else {
			$redirect_url = $permission[$rolename][0];
			log_message('info', sprintf('Redirect Portal %s /%s', $username, $redirect_url));
			redirect($redirect_url);
		}
	}

	public function logout()
	{
		$this->load->model('histories_model');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->histories_model->log($this->member->get_userid(),'ログアウト');
		$this->histories_model->log($this->member_lib->get_userid(),'ログアウト');
		// $this->member->logout();
		$this->member_lib->logout();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		redirect('/' . dirname(uri_string()) . '/seeyou');
	}

	public function seeyou()
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->is_login()) {
		if ($this->member_lib->is_login()) {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$this->redirect_portal();
		}
		$data = array();
		$this->parser->parse('member_seeyou.html', $data);
	}
	
	public function preparation()
	{
		$data = array();
		$this->parser->parse('member_preparation.html', $data);
	}

	public function redirect()
	{
		log_message('notice', sprintf('Closed /%s', uri_string()));
		$this->parser->parse('closed');
	}
}
/* End of file member.php */
/* Location: ./application/controllers/member.php */
