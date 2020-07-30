<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Eventtype_model extends CI_Model
{
	private $table_name = 'event_type';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['eventtype']))	$this->db->set('eventtype', $data['eventtype']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('eventtypeid', $key);

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
		$this->db->where('eventtypeid', $data['eventtypeid']);
		if(isset($data['eventtype']))	$this->db->set('eventtype', $data['eventtype']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('eventtypeid', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['eventtype']] = $row['eventtype'];
		}
		return $res;
	}
}

// End of file event_type_model.php
// Location: ./application/models/event_type_model.php
// vim:ts=4
