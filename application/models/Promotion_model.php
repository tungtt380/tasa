<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Promotion_model extends CI_Model
{
	private $table_name = 'promotion';

	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->table_name = $ci->config->item('dbprefix') . $this->table_name;
	}

	function create($data)
	{
/*
		if(isset($data['countrycode']))	$this->db->set('countrycode', $data['countrycode']);
		if(isset($data['countryabbr']))	$this->db->set('countryabbr', $data['countryabbr']);
		if(isset($data['countryname']))	$this->db->set('countryname', $data['countryname']);
		if(isset($data['countrytext']))	$this->db->set('countrytext', $data['countrytext']);
		$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);

		$this->db->insert($this->table_name);

		return $this->db->affected_rows();
*/
	}

	function read($key)
	{
/*
		$this->db->where('countrycode', $key);

		$query = $this->db->get($this->table_name);

		return $query;
*/
	}

	function readAll()
	{
/*
		$query = $this->db->get($this->table_name);

		return $query;a
*/
	}

	function update($key, $data, $token='')
	{
/*
		$this->db->where('countrycode', $data['countrycode']);
		if(isset($data['countryabbr']))	$this->db->set('countryabbr', $data['countryabbr']);
		if(isset($data['countryname']))	$this->db->set('countryname', $data['countryname']);
		if(isset($data['countrytext']))	$this->db->set('countrytext', $data['countrytext']);

		$this->db->update($this->table_name);

		return $this->db->affected_rows();
*/
	}

	function delete($key, $token='')
	{
/*
		$this->db->where('countrycode', $key);
		$this->db->delete($this->table_name);

		return $this->db->affected_rows();
*/
	}

    function get_dropdown()
    {
		$res = array(
			'OPT'      => 'OPTION',
			'OPTION'   => 'OPTION',
			'OPT2'     => 'OPTION2',
			'DORI'     => 'ドリフト天国',
			'GWORKS'   => 'G-ワークス',
			'GQ'       => 'GENROQ',
			'SPE'      => 'Special Cars',
			'STYLE'    => 'STYLE WAGON',
			'REV'      => 'REV SPEED',
			'JLUG'     => 'J-LUG',
			'GOOUT'    => 'GO OUT',
			'clicccar' => 'クリッカー',
			'BEST' => 'BEST CAR',
			'86BRZ' => '86&BRZ',
			'HYPER' => 'HYPER REV',
			'CARXS' => 'カーXS',
			'LEGEND' => 'LEGEND',
			'TUNINGCAR' => 'チューニングカーギャラリー',
		);
        return $res;
    }
}

// End of file country_model.php
// Location: ./application/models/country_model.php
// vim:ts=4
