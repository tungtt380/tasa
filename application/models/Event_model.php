<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Event_model extends CI_Model
{
	private $table_name = 'events';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['eventid']))	$this->db->set('eventid', $data['eventid']);
		if(isset($data['eventname']))	$this->db->set('eventname', $data['eventname']);
		if(isset($data['eventabbr']))	$this->db->set('eventabbr', $data['eventabbr']);
		if(isset($data['eventsite']))	$this->db->set('eventsite', $data['eventsite']);
		if(isset($data['eventtype']))	$this->db->set('eventtype', $data['eventtype']);
		if(isset($data['event_sdate']))	$this->db->set('event_sdate', $data['event_sdate']);
		if(isset($data['event_edate']))	$this->db->set('event_edate', $data['event_edate']);
		if(isset($data['entry_sdate']))	$this->db->set('entry_sdate', $data['entry_sdate']);
		if(isset($data['entry_edate']))	$this->db->set('entry_edate', $data['entry_edate']);
		if(isset($data['token']))	$this->db->set('token', $data['token']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('eventid', $key);
		$this->db->where('expired', '0');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function readAll()
	{
		$this->db->where('expired', '0');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($key, $data, $token='')
	{
		$this->db->where('eventid', $data['eventid']);
		if(isset($data['eventname']))	$this->db->set('eventname', $data['eventname']);
		if(isset($data['eventabbr']))	$this->db->set('eventabbr', $data['eventabbr']);
		if(isset($data['eventsite']))	$this->db->set('eventsite', $data['eventsite']);
		if(isset($data['eventtype']))	$this->db->set('eventtype', $data['eventtype']);
		if(isset($data['event_sdate']))	$this->db->set('event_sdate', $data['event_sdate']);
		if(isset($data['event_edate']))	$this->db->set('event_edate', $data['event_edate']);
		if(isset($data['entry_sdate']))	$this->db->set('entry_sdate', $data['entry_sdate']);
		if(isset($data['entry_edate']))	$this->db->set('entry_edate', $data['entry_edate']);
		$this->db->set('token', $this->create_token());
		$this->db->where('token', $token);
		$this->db->where('expired', '0');

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('eventid', $key);
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
		$this->db->order_by('eventid DESC');
		$query = $this->db->get($this->table_name);
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['eventid']] = $row['eventname'];
		}
		return $res;
	}
}

// End of file events_model.php
// Location: ./application/models/events_model.php
// vim:ts=4
