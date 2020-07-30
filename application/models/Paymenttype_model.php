<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Paymenttype_model extends CI_Model
{
	private $table_name = 'payment_type';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['seqno']))		$this->db->set('seqno', $data['seqno']);
		if(isset($data['paymentcode']))	$this->db->set('paymentcode', $data['paymentcode']);
		if(isset($data['paymentname']))	$this->db->set('paymentname', $data['paymentname']);
		// TODO: COLUMN
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('paymentcode', $key);

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
		$this->db->where('paymentcode', $data['paymentcode']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['paymentcode']))	$this->db->set('paymentcode', $data['paymentcode']);
		if(isset($data['paymentname']))	$this->db->set('paymentname', $data['paymentname']);
		// TODO: COLUMN

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('paymentcode', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['paymentcode']] = $row['paymentname'];
		}
		return $res;
	}
}

// End of file paymenttype_model.php
// Location: ./application/models/paymenttype_model.php
// vim:ts=4
