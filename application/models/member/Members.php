<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 会員テーブルの操作
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/config.html
 */
class Members extends CI_Model
{
	private $table_name = 'members';
	private $table_req_name = 'member_requests';

	function __construct()
	{
		parent::__construct();

		$ci =& get_instance();
		$ci->load->database();
		$this->table_name = $ci->config->item('prefix', 'member') . $this->table_name;
		$this->table_req_name = $ci->config->item('prefix', 'member') . $this->table_req_name;
	}

	/**
	 * ユーザ名よりユーザレコードの取得
	 *
	 * @param	string	$username
	 * @return	object
	 */
	function get_user($username)
	{
		$where = "(`username` = '".$username."' OR `email` = '".strtolower($username)."')";
		$this->db->where($where)
				 ->where('expired', 0);

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() != 1) {
			log_message('notice', $this->db->last_query());
			return NULL;
		}
		return $query->row();
	}

	/**
	 * お客様コードよりユーザレコードの取得
	 *
	 * @param	string	$memberid
	 * @param	bool	$activate
	 * @return	object
	 */
	function get_user_by_id($memberid, $activate)
	{
		$this->db
			->where('memberid', $memberid)
			->where('activate', $activate ? 1:0)
			->where('expired', 0);

		$query = $this->db->get($this->table_name);
		if ($query === FALSE || $query->num_rows() != 1) {
			log_message('notice', $this->db->last_query());
			return NULL;
		}
		return $query->row();
	}

	function get_user_by_pcode($exhid, $boothid)
	{
		$this->db
			->where('pcode1', $exhid)
			->where('pcode2', $boothid)
			->where('expired', 0);

		$query = $this->db->get($this->table_name);
		if ($query === FALSE || $query->num_rows() != 1) {
			log_message('notice', $this->db->last_query());
			return NULL;
		}
		return $query->row();
	}

	/**
	 * 既にユーザ名が登録されているか調べる
	 *
	 * @param	string	$username
	 * @return	bool
	 */
	function is_username_available($username)
	{
		$this->db->select('1', FALSE)
			->where('username', strtolower($username));

		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 0;
	}

	/**
	* 既にメールアドレスが登録されているか調べる
	*
	* @param	string	$email
	* @return	bool
	*/
	function is_email_available($email)
	{
		$this->db->select('1', FALSE)
			->from($this->table_name)
			->join($this->table_req_name, $this->table_name.'.memberid = '.$this->table_req_name.'.memberid', 'left')
			->where('email', strtolower($email))
			->or_where('new_email', strtolower($email));

		$query = $this->db->get();
		return $query->num_rows() == 0;
	}

	/**
	* ユーザの作成
	*/
	function create_user($data, $activate=TRUE)
	{
		$this->db->set('memberid', "nextuid('members.memberid','M')", FALSE);
		$this->db->set('token', $this->create_token());
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('activate', $activate ? 1 : 0);

		if (!$this->db->insert($this->table_name, $data)) {
			return NULL;
		}

		$memberid = $this->db->insert_id();
		if ($activate) {
			$this->create_profile($memberid);
		}
		log_message('notice', sprintf('[M2011] CREATED MEMBER=%d', $memberid));
		return array('memberid' => $memberid);
	}

	/**
	* ユーザの論理削除
	*/
	function delete_user($memberid, $token=FALSE)
	{
		$this->db->set('expired', 1);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->where('memberid', $memberid);
		if ($token !==FALSE)
			$this->db->where('token', $token);

		$this->db->update($this->table_name);
		if ($this->db->affected_rows() <= 0) {
			log_message('notice', $this->db->last_query());
			return FALSE;
		}

		$this->delete_profile($user_id);
		log_message('notice', sprintf('[M2014] DELETED MEMBER=%d', $memberid));
		return TRUE;
	}

	/**
	* ユーザを使用可能にする
	*/
	function activate_user($memberid, $activate_key, $activate_email)
	{
		$this->db->select('1', FALSE)
			->where('memberid', $memberid);

		if ($activate_email) {
			$this->db->where('new_email', $activate_key);
		} else {
			$this->db->where('new_password', $activate_key);
		}
		$query = $this->db->get($this->table_req_name);

		if ($query->num_rows() == 1) {

			$this->db->set('activated', 1);
			$this->db->where('memberid', $memberid);
			$this->db->update($this->table_name);

			$this->db->where('memberid', $memberid);
			$this->db->delete($this->table_req_name);

			$this->create_profile($user_id);
			return TRUE;
		}
		return FALSE;
	}

	/**
	* ユーザの拒否
	*/
	function reject_user($memberid, $reason = NULL)
	{
		$this->db
			->where('memberid', $memberid)
			->where('expired', 0);
		$this->db->update($this->table_name, array(
			'reject' => 1,
			'reason' => $reason,
		));
	}

	/**
	* 拒否ユーザの再許可
	* @param	string	$memberid
	*/
	function permit_user($memberid)
	{
		$this->db
			->where('memberid', $memberid)
			->where('expired', 0);
		$this->db->update($this->table_name, array(
			'reject' => 1,
			'reason' => NULL,
		));
	}

	/**
	* パスワードの変更
	*
	* @param	string	$memberid
	* @param	string	$password
	* @return	bool
	*/
	function change_password($memberid, $password)
	{
		$this->db
			->set('password', $password)
			->where('memberid', $memberid)
			->where('expired', 0);

		$this->db->update($this->table_name);
		if ($this->db->affected_rows() < 0) {
			log_message('notice', $this->db->last_query());
			return FALSE;
		}
		return TRUE;
	}

	/**
	* ユーザー名の変更
	*
	* @param	string	$memberid
	* @param	string	$username
	* @return	bool
	*/
	function change_username($memberid, $username, $token)
	{
		$this->db
			->set('username', $username)
			->set('token', $this->create_token())
			->where('memberid', $memberid)
			->where('token', $token)
			->where('expired', 0);

		$this->db->update($this->table_name);
		if ($this->db->affected_rows() <= 0) {
			log_message('notice', $this->db->last_query());
			return FALSE;
		}
		return TRUE;
	}

	/**
	* メールアドレスの変更
	*
	* @param	string	$memberid
	* @param	string	$email
	* @param	string	$token
	* @return	bool
	*/
	function change_email($memberid, $email, $token)
	{
		$this->db
			->set('email', $email)
			->set('token', $this->create_token())
			->where('memberid', $memberid)
			->where('token', $token)
			->where('expired', 0);

		$this->db->update($this->table_name);
		if ($this->db->affected_rows() <= 0) {
			log_message('notice', $this->db->last_query());
			return FALSE;
		}
		return TRUE;
	}

	/**
	* メールアドレスの変更
	*
	* @param	string	$token
	* @return	bool
	*/
	function change_email_by_token($token)
	{
		$this->db->set($this->table_name.'.email', $this->table_req_name.'.new_email', FALSE);
		$this->db->where($this->table_name.'.memberid', $this->table_req_name.'.memberid', FALSE);
		$this->db->where($this->table_req_name.'activate_token', $token);

		$this->db->update($this->table_name.','.$this->table_req_name);
		if ($this->db->affected_rows() <= 0) {
			log_message('notice', $this->db->last_query());
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * トークン文字列の作成
	 */
	private function create_token()
	{
		return base64_encode(sha1(uniqid(rand() . 'ABC'), TRUE) . 'Z');
	}

	/**
	 * ユーザープロファイルの空レコードの追加
	 * @param	int		$memberid
	 */
	private function create_profile($memberid)
	{
	}

	/**
	 * ユーザープロファイルの削除
	 * @param	int		$memberid
	 */
	private function delete_profile($memberid)
	{
	}
}

// vim:ts=4
/* End of file members.php */
/* Location: ./application/models/member/members.php */
