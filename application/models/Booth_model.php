<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Booth_model extends CI_Model
{
	private $table_name = 'booths';
	private $table_record = FALSE;

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['eventid']))	$this->db->set('eventid', $data['eventid']);
		if(isset($data['boothno']))	$this->db->set('boothno', $data['boothno']);
		if(isset($data['spaceid']))	$this->db->set('spaceid', $data['spaceid']);
		if(isset($data['boothname']))	$this->db->set('boothname', $data['boothname']);
		if(isset($data['boothabbr']))	$this->db->set('boothabbr', $data['boothabbr']);
		if(isset($data['boothcount']))	$this->db->set('boothcount', $data['boothcount']);
		if(isset($data['token']))	$this->db->set('token', $data['token']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('boothid', $key);
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
		$this->db->where('boothid', $data['boothid']);
		if(isset($data['eventid']))	$this->db->set('eventid', $data['eventid']);
		if(isset($data['boothno']))	$this->db->set('boothno', $data['boothno']);
		if(isset($data['spaceid']))	$this->db->set('spaceid', $data['spaceid']);
		if(isset($data['boothname']))	$this->db->set('boothname', $data['boothname']);
		if(isset($data['boothabbr']))	$this->db->set('boothabbr', $data['boothabbr']);
		if(isset($data['boothcount']))	$this->db->set('boothcount', $data['boothcount']);
		$this->db->set('token', $this->create_token());
		$this->db->where('token', $token);
		$this->db->where('expired', '0');

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('boothid', $key);
		$this->db->set('token', $this->create_token());
		$this->db->where('token', $token);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function get_abbrlist()
	{
		$this->db->select('boothid, boothname');
		$this->db->from($this->table_name);
		$this->db->order_by('booths.boothno');
		$query = $this->db->get();
		$res = array();
		foreach($query->result_array() as $row) {
			$res[$row['boothid']] = $row['boothname'];
		}
		return $res;
	}

	function get_dropdown($withgroup=FALSE,$admin=FALSE,$filter=FALSE,$lang='ja')
	{
		$this->db->select('boothid, spacename, boothcount, boothabbr, booths.allow AS allow');
		$this->db->from($this->table_name);
		$this->db->join('spaces', 'spaces.spaceid = ' . $this->table_name . '.spaceid');
		$this->db->order_by('booths.boothno');
		if ($admin === FALSE) {
			$this->db->where('booths.allow != ', '0');
			$this->db->where('spaces.inventory != ', '9');
		}
		if ($filter !== FALSE) {
			if (is_array($filter)) {
				$this->db->where_in('spaces.spaceabbr', $filter);
			} else {
				$this->db->where('spaces.spaceabbr = ', $filter);
			}
		}
		$query = $this->db->get();

		if ($lang == 'en') {
			$res = array(''=>'===Please select booth or space===');
		} else {
			$res = array(''=>'===選択してください===');
		}
		if ($withgroup) {
			// オプショングループ付き
			foreach($query->result_array() as $row) {
				if (strpos($row['spacename'], '㎡') !== FALSE) {
					$res['Sスペース'][$row['boothid']] = $row['spacename'];
				} else if (strpos($row['boothabbr'], 'x') !== FALSE) {
					if ($lang == 'en') { $spacename = str_replace('スペース',' Space',$row['spacename']); } else { $spacename = $row['spacename']; }
					$res[$spacename][$row['boothid']] = $spacename . ' ' . $row['boothcount'] . ($lang=='en' ? ' booth(s)(':'小間(') . $row['boothabbr'] . ')';
				} else {
					if ($lang == 'en') { $spacename = str_replace('スペース',' Space',$row['spacename']); } else { $spacename = $row['spacename']; }
					$res[$spacename][$row['boothid']] = $spacename . ' ' . $row['boothcount'] . ($lang=='en' ? ' space(s)':'スペース');
				}
			}
		} else {
			// オプショングループなし
			foreach($query->result_array() as $row) {
				if (strpos($row['spacename'], '㎡') !== FALSE) {
					$res[$row['boothid']] = $row['spacename'];
				} else if (strpos($row['boothabbr'], 'x') !== FALSE) {
					if ($lang == 'en') { $spacename = str_replace('スペース',' Space',$row['spacename']); } else { $spacename = $row['spacename']; }
					$res[$row['boothid']] = $spacename . ' ' . $row['boothcount'] . ($lang=='en' ? ' booth(s)(':'小間(') . $row['boothabbr'] . ')';
				} else {
					if ($lang == 'en') { $spacename = str_replace('スペース',' Space',$row['spacename']); } else { $spacename = $row['spacename']; }
					$res[$row['boothid']] = $spacename . ' ' . $row['boothcount'] . ($lang=='en' ? ' Space(s)':'スペース');
				}
			}
		}
		return $res;
	}

	function get_boothspace($id)
	{
		if ($this->table_record === FALSE) {
			$this->db->select('boothid, spaceabbr, boothcount, boothabbr, inventory, booths.allow AS allow');
			$this->db->from($this->table_name);
			$this->db->join('spaces', 'spaces.spaceid = ' . $this->table_name . '.spaceid');
			$query = $this->db->get();
			$this->table_record = array();
			foreach($query->result_array() as $row) {
				$this->table_record[$row['boothid']] = $row;
			}
		}
		if (!isset($this->table_record[$id])) {
			return FALSE;
		}
		return $this->table_record[$id];
	}

	/*
	function get_spaceid($id)
	{
		if ($this->table_record === FALSE) {
			$this->db->select('boothid, spaceid, boothcount');
			$this->db->from($this->table_name);
			$query = $this->db->get();
			$this->table_record = array();
			foreach($query->result_array() as $row) {
				$this->table_record[$row['boothid']] = $row['spaceid'];
			}
		}
		if (!isset($this->table_record[$id])) {
			return FALSE;
		}
		return $this->table_record[$id];
	}
	*/
}

// End of file booth_model.php
// Location: ./application/models/booth_model.php
