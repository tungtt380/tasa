<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Category_model extends CI_Model
{
	private $table_name = 'category';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['categorycode']))	$this->db->set('categorycode', $data['categorycode']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['categoryname']))	$this->db->set('categoryname', $data['categoryname']);
		if(isset($data['categoryabbr']))	$this->db->set('categoryabbr', $data['categoryabbr']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('categorycode', $key);

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
		$this->db->where('categorycode', $data['categorycode']);
		if(isset($data['categoryname']))	$this->db->set('categoryname', $data['categoryname']);
		if(isset($data['categoryabbr']))	$this->db->set('categoryabbr', $data['categoryabbr']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('categorycode', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->where('allow', '1');
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['categorycode']] = $row['categoryname'];
		}
		return $res;
	}
}

// End of file category_model.php
// Location: ./application/models/category_model.php
