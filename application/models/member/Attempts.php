<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ログイン履歴テーブルの操作
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/config.html
 */
class Attempts extends CI_Model
{
	private $table_name = 'member_attempts';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$ci->load->database();
		$this->table_name = $ci->config->item('prefix', 'member') . $this->table_name;
	}

	function is_exceeded($ipaddress, $username, $count = 5)
	{
		$this->db->select('1', FALSE);
		$this->db->where('ipaddress', $ipaddress);
		if (strlen($username) > 0)
			$this->db->or_where('username', $username);

		$qres = $this->db->get($this->table_name);
		return $qres->num_rows();
	}

	function increase($ipaddress, $username)
	{
		$this->db->insert($this->table_name, array(
			'ipaddress' => $ipaddress,
			'username'  => $username
		));
	}

	function clear($ipaddress, $username, $expire = 86400)
	{
		$this->db->where(array('ipaddress' => $ipaddress, 'username' => $username));
		$this->db->or_where('UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(`updated`) > ' . intval($expire) , NULL, FALSE);
		$this->db->delete($this->table_name);
	}
}

/* End of file login_attempts.php */
/* Location: ./application/models/member/member_attempts.php */
