<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Exapply_model extends CI_Model
{
	private $table_name = 'exhibitor_apply';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
		if(isset($data['exhid']))	$this->db->set('exhid', $data['exhid']);
		if(isset($data['appno']))	$this->db->set('appno', $data['appno']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['e01']))		$this->db->set('u01', $data['e01']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
	}

	function read($key)
	{
		$this->db->where('appid', $key);
		$query = $this->db->get($this->table_name);
		return $query;
	}

	function update($key, $data, $token='')
	{
		$this->db->where('appid', $data['appid']);
		if(isset($data['exhid']))	$this->db->set('exhid', $data['exhid']);
		if(isset($data['appno']))	$this->db->set('appno', $data['appno']);
		if(isset($data['seqno']))	$this->db->set('seqno', $data['seqno']);
		if(isset($data['e01']))		$this->db->set('e01', $data['e01']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		$this->db->where('appid', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
	}

	function get_appid($exhboothid, $appno, $seqno=0)
	{
		$this->db->select('appid');
		$this->db->where('exhboothid', $exhboothid);
		$this->db->where('appno', $appno);
		$this->db->where('seqno', $seqno);
		$this->db->where('expired', 0);
		$query = $this->db->get($this->table_name);
        if ($query !== FALSE && $query->num_rows() >= 1) {
            $record = $query->row_array();
			$res = isset($record['appid']) ? $record['appid']:FALSE;
        } else {
			$res = FALSE;
		}
		return $res;
	}
}

/* End of file histories_model.php */
/* Location: ./application/models/histories_model.php */
