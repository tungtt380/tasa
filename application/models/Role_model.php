<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Role_model extends CI_Model
{
	private $table_name = 'roles';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['rolename']))	$this->db->set('rolename', $data['rolename']);
		if(isset($data['comment']))	$this->db->set('comment', $data['comment']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('roleid', $key);

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function readAll()
	{
		$this->db->order_by('seqno');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($key, $data, $token='')
	{
		$this->db->where('roleid', $data['roleid']);
		if(isset($data['rolename']))	$this->db->set('rolename', $data['rolename']);
		if(isset($data['comment']))	$this->db->set('comment', $data['comment']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('roleid', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['rolename']] = $row['rolename'];
		}
		return $res;
	}
}

// End of file roles_model.php
// Location: ./application/models/roles_model.php
// vim:ts=4
