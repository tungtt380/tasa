<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Prefecture_model extends CI_Model
{
	private $table_name = 'prefecture';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['prefcode']))	$this->db->set('prefcode', $data['prefcode']);
		if(isset($data['prefname']))	$this->db->set('prefname', $data['prefname']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);
		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('prefcode', $key);

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
		$this->db->where('prefcode', $data['prefcode']);
		if(isset($data['prefname']))	$this->db->set('prefname', $data['prefname']);

		$this->db->update($this->table_name);
		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('prefcode', $key);
		$this->db->delete($this->table_name);
		return $this->db->affected_rows();
	}

	function get_dropdown($eos=FALSE)
	{
		$this->db->order_by('prefcode');
		$query = $this->db->get($this->table_name);
		$res = array();
		if ($eos !== FALSE) {
			$res[''] = '';
		}
		foreach($query->result_array() as $row) {
			$res[$row['prefname']] = $row['prefname'];
		}
		return $res;
	}
}

// End of file prefecute.php
// Location: ./application/models/prefecture_model.php
// vim:ts=4
