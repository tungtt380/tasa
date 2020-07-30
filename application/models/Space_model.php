<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Space_model extends CI_Model
{
	private $table_name = 'spaces';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['eventid']))	$this->db->set('eventid', $data['eventid']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['spacename']))	$this->db->set('spacename', $data['spacename']);
		if(isset($data['spaceabbr']))	$this->db->set('spaceabbr', $data['spaceabbr']);
		if(isset($data['memberprice']))	$this->db->set('memberprice', $data['memberprice']);
		if(isset($data['assocprice']))	$this->db->set('assocprice', $data['assocprice']);
		if(isset($data['maxspaces']))	$this->db->set('maxspaces', $data['maxspaces']);
		if(isset($data['comments']))	$this->db->set('comments', $data['comments']);
		if(isset($data['carlimits']))	$this->db->set('carlimits', $data['carlimits']);
		if(isset($data['total_count']))	$this->db->set('total_count', $data['total_count']);
		if(isset($data['notsale_count']))	$this->db->set('notsale_count', $data['notsale_count']);
		if(isset($data['forsale_count']))	$this->db->set('forsale_count', $data['forsale_count']);
		if(isset($data['token']))	$this->db->set('token', $data['token']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('spaceid', $key);
		$this->db->where('expired', '0');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function readAll()
	{
		$this->db->order_by('seqno');
		$this->db->where('expired', '0');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($key, $data, $token='')
	{
		$this->db->where('spaceid', $data['spaceid']);
		if(isset($data['eventid']))	$this->db->set('eventid', $data['eventid']);
		if(isset($data['spacename']))	$this->db->set('spacename', $data['spacename']);
		if(isset($data['spaceabbr']))	$this->db->set('spaceabbr', $data['spaceabbr']);
		if(isset($data['memberprice']))	$this->db->set('memberprice', $data['memberprice']);
		if(isset($data['assocprice']))	$this->db->set('assocprice', $data['assocprice']);
		if(isset($data['maxspaces']))	$this->db->set('maxspaces', $data['maxspaces']);
		if(isset($data['comments']))	$this->db->set('comments', $data['comments']);
		if(isset($data['carlimits']))	$this->db->set('carlimits', $data['carlimits']);
		if(isset($data['total_count']))	$this->db->set('total_count', $data['total_count']);
		if(isset($data['notsale_count']))	$this->db->set('notsale_count', $data['notsale_count']);
		if(isset($data['forsale_count']))	$this->db->set('forsale_count', $data['forsale_count']);
		$this->db->set('token', $this->create_token());
		$this->db->where('token', $token);
		$this->db->where('expired', '0');

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('spaceid', $key);
		$this->db->set('token', $this->create_token());
		$this->db->where('token', $token);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function get_dropdown()
	{
		// Temprary, 2014
		$this->db->where('eventid', 'AS020200');
		$this->db->order_by('seqno');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['spaceid']] = $row['spacename'];
		}
		return $res;
	}

	function get_spacecount($uid)
	{
		$this->db->select('spaceid, maxspaces');
		$this->db->where('spaceid', $uid);
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['spaceid']] = array('maxspaces' => $row['maxspaces']);
		}

		$this->db->select('booths.spaceid');
		$this->db->select_sum('`boothcount` * `count`', 'counter', FALSE);
		$this->db->from('booths');
		$this->db->join('exhibitor_booth', 'exhibitor_booth.boothid = booths.boothid');
		$this->db->join('exhibitors', 'exhibitors.exhid = exhibitor_booth.exhid');
		$this->db->where('booths.spaceid', $uid);
		$this->db->where('exhibitors.statusno <', '900');
		$this->db->where('exhibitors.expired', '0');
		$this->db->group_by('booths.spaceid');
		$query = $this->db->get();
		foreach($query->result_array() as $row) {
			$res[$row['spaceid']]['counter'] = $row['counter'];
		}
		return $res[$uid];
	}
}

// End of file spaces_model.php
// Location: ./application/models/spaces_model.php
