<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payee_model extends CI_Model
{
	private $table_name = 'payee';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['bankcode']))	$this->db->set('bankcode', $data['bankcode']);
		if(isset($data['bankname']))	$this->db->set('bankname', $data['bankname']);
		// TODO: COLUMN
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('payeeid', $key);

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function readAll()
	{
		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($key, $data, $token='')
	{
		$this->db->where('payeeid', $data['payeeid']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['bankcode']))	$this->db->set('bankcode', $data['bankcode']);
		if(isset($data['bankname']))	$this->db->set('bankname', $data['bankname']);
		// TODO: COLUMN

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('payeeid', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['payeeid']] = $row['bankname'];
		}
		return $res;
	}
}

// End of file payee_model.php
// Location: ./application/models/payee_model.php
// vim:ts=4
