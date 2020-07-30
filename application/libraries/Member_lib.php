<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * Member Class
 *
 * 会員情報をデータベース連携して参照する
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Member
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/member.html
 */

class Member_lib {

	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->config('member', TRUE, TRUE);

		$this->CI->load->library('session');
		$this->CI->load->model('member/members');

		$this->autologin();
	}

	//----------------------------------------------------------------
	public function login($username, $password, $remember)
	{
		if ((strlen($username) <= 0) || (strlen($password) <= 0))
			return FALSE;

		$user = $this->CI->members->get_user($username);

		if (is_null($user)) {
			$this->increase_attempt($username);
			$this->error = array('username' => 'auth_incorrect_username');
			return FALSE;
		}
		if ($password !== $user->password) {
			$this->increase_attempt($username);
			$this->error = array('password' => 'auth_incorrect_password');
			return FALSE;
		}
		if ($user->reject) {
			$this->error = array('reject' => $user->reject_reason);
			return FALSE;
		}

		// OKay, additional session.
		$this->CI->session->set_userdata(array(
			'userid'	=> $user->memberid,
			'username'	=> $user->username,
			'status'	=> ($user->activate != 0) ? '1':'0',
		));
		if ($user->activate == 0) {
			$this->error = array('expire' => '');
			return FALSE;
		}

		if ($remember) {
			$this->create_autologin($user->memberid);
		}

		$this->clear_attempts($username);
//		$this->CI->users->update_login_info($user->id);
		return TRUE;
	}

	public function logout()
	{
		$this->delete_autologin();
		$this->CI->session->set_userdata(array('userid'=>'', 'username'=>'', 'status'=>''));
		$this->CI->session->sess_destroy();
	}


	//----------------------------------------------------------------
	public function is_login($activated = TRUE)
	{
		return $this->CI->session->userdata('status') === ($activated ? '1':'0');
	}
	public function get_userid()
	{
		return $this->CI->session->userdata('userid');
	}
	public function get_username()
	{
		return $this->CI->session->userdata('username');
	}
	public function get_exhid()
	{
		if (is_null($user = $this->CI->members->get_user_by_id($this->get_userid(), TRUE))) {
			return FALSE;
		}
		return $user->pcode1;
	}
	public function get_exhboothid()
	{
		if (is_null($user = $this->CI->members->get_user_by_id($this->get_userid(), TRUE))) {
			return FALSE;
		}
		return $user->pcode2;
	}
	public function get_rolename()
	{
		if (is_null($user = $this->CI->members->get_user_by_id($this->get_userid(), TRUE))) {
			return FALSE;
		}
		return $user->rolename;
	}
	public function get_member()
	{
		if (is_null($user = $this->CI->members->get_user_by_id($this->get_userid(), TRUE))) {
			return FALSE;
		}
		return (array)$user;
	}

	//----------------------------------------------------------------

	/**
	 * ユーザーの作成
	 */
	function create_user($username, $email, $password, $need_activate)
	{
		if ((strlen($username) > 0) AND !$this->CI->members->is_username_available($username)) {
			$this->error = array('username' => 'username_in_use');
		} elseif (!$this->CI->members->is_email_available($email)) {
			$this->error = array('email' => 'email_in_use');
		} else {
			$data = array(
				'rolename'	=> 'guest',
				'username'	=> $username,
				'password'	=> $password,
				'email'		=> $email,
			);

			if ($need_activate) {
				$data['active_token'] = md5(rand().microtime());
			}

			if (!is_null($res = $this->ci->members->create_user($data, !$need_activate))) {
				$data['memberid'] = $res['memberid'];
				$data['password'] = $password;
				return $data;
			}
		}
		return NULL;
	}

	/**
	 * ユーザーの削除(ログイン中のときのみ可能)
	 *
	 * @param	string	$password
	 * @return	bool
	 */
	function delete_user($password)
	{
		$memberid = $this->CI->session->userdata('userid');
		if (is_null($user = $this->CI->members->get_user_by_id($memberid, TRUE)))
			return FALSE;

		if ($password != $user->password) {
			$this->error = array('password' => 'incorrect_password');
			return FALSE;
		}

		$this->CI->members->delete_user($memberid);
		$this->logout();
		return TRUE;
	}

	/**
	 * パスワードの変更(ログイン中のときのみ可能)
	 *
	 * @param	string	$newpassword
	 * @param	string	$oldpassword
	 * @return	bool
	 */
	function change_password($newpassword, $oldpassword = FALSE)
	{
		$memberid = $this->CI->session->userdata('userid');
		if (is_null($user = $this->CI->members->get_user_by_id($memberid, TRUE)))
			return FALSE;

		if ($oldpassword !== FALSE && $oldpassword != $user->password) {
			$this->error = array('oldpassword' => 'incorrect_password');
			return FALSE;
		}

		if (!$this->CI->members->change_password($memberid, $newpassword))
			return FALSE;

		return TRUE;
	}

	//----------------------------------------------------------------

	/**
	 * ユーザー名の変更(ログイン中のときのみ可能)
	 *
	 * @param	string	$newusername
	 * @return	bool
	 */
	function change_username($newusername, $token)
	{
		$memberid = $this->CI->session->userdata('userid');
		if (is_null($user = $this->CI->members->get_user_by_id($memberid, TRUE)))
			return FALSE;

		if (!$this->CI->members->change_username($memberid, $newusername, $token))
			return FALSE;

		return TRUE;
	}

	//----------------------------------------------------------------

	/**
	 * メールアドレスの変更
	 *
	 * @param	string	$email
	 * @return	array
	 */
	function change_email($email, $token)
	{
		$memberid = $this->CI->session->userdata('userid');
		if (is_null($user = $this->CI->members->get_user_by_id($memberid, TRUE)))
			return FALSE;

		if (!$this->CI->members->change_email($memberid, strtolower($email), $token))
			return FALSE;

		return TRUE;
	}

	/**
	 * メールアドレスの変更(トークンを使用した場合)
	 *
	 * @param	string	$token
	 * @return	array
	 */
	function change_email_by_token($token)
	{
		// トークンを利用してメールアドレスを置き換える
	}

	/**
	 * メールアドレスの変更受付(ログイン中のときのみ可能)
	 *
	 * @param	string	$email
	 * @param	string	$hash
	 * @return	array
	 */
	function pending_email($email)
	{
		$memberid = $this->CI->session->userdata('userid');
		if (is_null($user = $this->CI->members->get_user_by_id($memberid, FALSE)))
			return NULL;

		// 仮置きテーブルに保存(トークンを取得)
		// メールを送信する
	}

	//----------------------------------------------------------------

	/*
	 * 自動ログインのセッションを作成
	 */
	private function create_autologin($userid)
	{
		$this->CI->load->helper('cookie');
		$hashid = base64_encode(sha1(uniqid(rand().get_cookie($this->CI->config->item('sess_cookie_name')))));

		$this->CI->load->model('member/autologin');
		$this->CI->autologin->purge($userid);

		if ($this->CI->autologin->set($userid, md5($hashid))) {
			set_cookie(array(
				'data'   => serialize(array('userid' => $userid, 'hashid' => $hashid)),
				'name'   => $this->CI->config->item('autologin_cookie', 'member'),
				'expire' => $this->CI->config->item('autologin_expire', 'member'),
			));
			return TRUE;
		}
		return FALSE;
	}

	/*
	 * 自動ログインのセッションを削除
	 */
	private function delete_autologin()
	{
		$this->CI->load->helper('cookie');
		if ($cookie = get_cookie($this->CI->config->item('autologin_cookie', 'member'), TRUE)) {
			$data = unserialize($cookie);
			$this->CI->load->model('member/autologin');
			$this->CI->autologin->delete($data['userid'], md5($data['hashid']));
			delete_cookie($this->CI->config->item('autologin_cookie', 'member'));
		}
	}

	/*
	 * 自動ログイン
	 */
	private function autologin()
	{
		if ($this->is_login() || $this->is_login(FALSE))
			return FALSE;

		$this->CI->load->helper('cookie');
		$cookie = get_cookie($this->CI->config->item('autologin_cookie', 'member'), TRUE);
		if (!$cookie)
			return FALSE;

		$data = unserialize($cookie);
		if (isset($data['userid']) && isset($data['hashid']))
			return FALSE;

		$this->CI->load->model('member/autologin');
		$user = $this->CI->autologin->get($data['userid'], md5($data['hashid']));
		if (is_null($user))
			return FALSE;

		// セッションに詰める
		$this->CI->session->set_userdata(array(
			'userid'   => $user->id,
			'username' => $user->username,
			'status'   => '1',
		));

		// 自動ログイン用セッション(cookie)を再作成する。
		set_cookie(array(
			'data'     => $cookie,
			'name'     => $this->CI->config->item('autologin_cookie', 'member'),
			'expire'   => $this->CI->config->item('autologin_expire', 'member'),
		));

		$this->CI->members->update_logininfo($user->id);
		return TRUE;
	}

	//----------------------------------------------------------------

	/**
	 * 一定回数以上ログイン失敗しているかの調査
	 *
	 * @param $username
	 * @return boolean
	 */
	function is_exceeded_attempts($username)
	{
		if ($this->CI->config->item('attempts', 'member', TRUE)) {
			$this->CI->load->model('member/attempts');
			return $this->CI->attempts->is_exceeded($this->CI->input->ip_address(), $username, 10);
		}
		return FALSE;
	}

	/**
	 * ログイン失敗回数の記録
	 *
	 * @param $username
	 */
	private function increase_attempt($username)
	{
		if ($this->CI->config->item('attempts', 'member', TRUE)) {
			if (!$this->is_exceeded_attempts($username)) {
				$this->CI->load->model('member/attempts');
				$this->CI->attempts->increase($this->CI->input->ip_address(), $username);
			}
		}
	}

	/**
	 * ログイン失敗回数の破棄
	 *
	 * @param $username
	 */
	private function clear_attempts($username)
	{
		if ($this->CI->config->item('attempts', 'member', TRUE)) {
			$this->CI->load->model('member/attempts');
			$this->CI->attempts->clear($this->CI->input->ip_address(), $username, 86400);
		}
	}
}
