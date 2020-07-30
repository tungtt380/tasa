<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 自動ログインテーブルの操作
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/config.html
 */

class Autologin extends CI_Model
{
	private $table_name         = 'member_autologin';
	private $members_table_name = 'members';

	function __construct()
	{
		parent::__construct();

		$ci =& get_instance();
		$this->table_name = $ci->config->item('prefix', 'member') . $this->table_name;
		$this->members_table_name = $ci->config->item('prefix', 'member') . $this->members_table_name;
	}

	function get($memberid, $hashid)
	{
		$this->db
			->select($this->members_table_name.'.memberid')
			->select($this->members_table_name.'.username')
			->from($this->members_table_name)
			->join($this->table_name, $this->table_name.'.memberid = '.$this->members_table_name.'.memberid')
			->where($this->table_name.'.memberid', $memberid)
			->where($this->table_name.'.hashid', $hashid);

		$query = $this->db->get();
		if ($query->num_rows() != 1)
			return NULL;

		return $query->row();
	}

	function set($memberid, $hashid)
	{
		return $this->db->insert($this->table_name, array(
			'memberid'   => $memberid,
			'hashid'     => $hashid,
			'useragent'  => substr($this->input->user_agent(), 0, 159),
			'lastip'     => $this->input->ip_address(),
		));
	}

	function delete($memberid, $hashid)
	{
		$this->db
			->where('memberid', $memberid)
			->where('hashid', $hashid)
			->delete($this->table_name);
	}

	function clear($memberid)
	{
		$this->db
			->where('memberid', $memberid)
			->delete($this->table_name);
	}

	function purge($memberid)
	{
		$this->db
			->where('memberid', $memberid)
			->where('useragent', substr($this->input->user_agent(), 0, 159))
			->where('lastip', $this->input->ip_address())
			->delete($this->table_name);
	}
}

/* End of file autologin.php */
/* Location: ./application/models/member/autologin.php */
