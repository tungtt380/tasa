<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Location_model extends CI_Model
{
	private $table_name = 'location';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['locationcode']))	$this->db->set('locationcode', $data['locationcode']);
		if(isset($data['seqno']))			$this->db->set('seqno', $data['seqno']);
		if(isset($data['locationname']))	$this->db->set('locationname', $data['locationname']);
		if(isset($data['locationabbr']))	$this->db->set('locationabbr', $data['locationabbr']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);
		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('locationcode', $key);

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
		$this->db->where('locationcode', $data['locationcode']);
		if(isset($data['locationname']))	$this->db->set('locationname', $data['locationname']);
		if(isset($data['locationabbr']))	$this->db->set('locationabbr', $data['locationabbr']);

		$this->db->update($this->table_name);
		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('locationcode', $key);

		$this->db->delete($this->table_name);
		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->order_by('locationcode');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['locationabbr']] = $row['locationabbr'];
		}
		return $res;
	}
}

// End of file location_model.php
// Location: ./application/models/location_model.php
// vim:ts=4
