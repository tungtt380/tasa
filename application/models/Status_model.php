<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Status_model extends CI_Model
{
	private $table_name = 'receipt_status';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['statusno']))	$this->db->set('statusno', $data['statusno']);
		if(isset($data['statusname']))	$this->db->set('statusname', $data['statusname']);
		if(isset($data['statusabbr']))	$this->db->set('statusabbr', $data['statusabbr']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('rstatusid', $key);

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
		$this->db->where('rstatusid', $data['rstatusid']);
		if(isset($data['statusno']))	$this->db->set('statusno', $data['statusno']);
		if(isset($data['statusname']))	$this->db->set('statusname', $data['statusname']);
		if(isset($data['statusabbr']))	$this->db->set('statusabbr', $data['statusabbr']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('rstatusid', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->order_by('statusno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['statusno']] = $row['statusabbr'];
		}
		return $res;
	}
}

// End of file receipt_status_model.php
// Location: ./application/models/receipt_status_model.php
// vim:ts=4
