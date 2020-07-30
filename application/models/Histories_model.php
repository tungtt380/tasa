<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Histories_model extends CI_Model
{
	private $table_name = 'histories';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['memberid']))	$this->db->set('memberid', $data['memberid']);
		if(isset($data['ipaddress']))	$this->db->set('ipaddress', $data['ipaddress']);
		if(isset($data['operation']))	$this->db->set('operation', $data['operation']);
		if(isset($data['opcomment']))	$this->db->set('opcomment', $data['opcomment']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function log($memberid, $operation, $opcomment='')
	{
		$this->db->set('memberid', $memberid);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('ipaddress', $this->input->server('REMOTE_ADDR'));
		$this->db->set('operation', $operation);

		if($opcomment != '')
			$this->db->set('opcomment', $opcomment);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('historyid', $key);

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function readAll()
	{
		$this->db->order_by('created', 'desc');
		$query = $this->db->get($this->table_name);

		return $query;
	}

	function readMemberAll($memberid)
	{
		$this->db->where('memberid', $memberid);
		$this->db->order_by('created', 'desc');
		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($key, $data, $token='')
	{
		$this->db->where('historyid', $data['historyid']);
		if(isset($data['memberid']))	$this->db->set('memberid', $data['memberid']);
		if(isset($data['ipaddress']))	$this->db->set('ipaddress', $data['ipaddress']);
		if(isset($data['operation']))	$this->db->set('operation', $data['operation']);
		if(isset($data['opcomment']))	$this->db->set('opcomment', $data['opcomment']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('historyid', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

}

/* End of file histories_model.php */
/* Location: ./application/models/histories_model.php */
