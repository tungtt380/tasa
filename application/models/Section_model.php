<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Section_model extends CI_Model
{
	private $table_name = 'section';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['sectioncode']))	$this->db->set('sectioncode', $data['sectioncode']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['sectionname']))	$this->db->set('sectionname', $data['sectionname']);
		if(isset($data['sectionabbr']))	$this->db->set('sectionabbr', $data['sectionabbr']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('sectioncode', $key);

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
		$this->db->where('sectioncode', $data['sectioncode']);
		if(isset($data['sectionname']))	$this->db->set('sectionname', $data['sectionname']);
		if(isset($data['sectionabbr']))	$this->db->set('sectionabbr', $data['sectionabbr']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('sectioncode', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->where('allow', 1);
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['sectioncode']] = $row['sectionabbr'];
		}
		return $res;
	}
}

// End of file section_model.php
// Location: ./application/models/section_model.php
// vim:ts=4
