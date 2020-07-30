<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Agreement extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('parser');
	}

	public function index()
	{
//		if (date('Y-m-d H:i:s') <= '2018-07-23 10:00:00' && $_SERVER['REMOTE_ADDR'] != '133.149.213.82') {
//			if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp' || $_SERVER['HTTP_HOST'] == 'cus2019.tokyoautosalon.jp') {
//				redirect('/');
//				return;
//			}
//		}
        if (date('Y-m-d H:i:s') <= '2019-07-22 10:00:00') {
            $this->CI = get_instance();
            $this->load->library('parser');
            $this->parser->parse('ja/comingsoon.html');
            return;
        }

		$data = array();

		$lists = $waiting = $soldout = array();
		$this->db->select('spaceid, spacename, spaceabbr, maxspaces, inventory');
		$this->db->order_by('seqno');
		$query = $this->db->get('v_spaces');
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				if (strlen($row['spaceabbr']) == 1) {
					$lists[$row['spaceid']] = $row;
					$lists[$row['spaceid']]['count'] = 0;
					if ($row['inventory'] == 1) {
						$waiting[$row['spaceid']] = $row['spaceabbr'] . 'スペース';
					}
					if ($row['inventory'] == 9) {
						$soldout[$row['spaceid']] = $row['spaceabbr'] . 'スペース';
					}
				}
			}
		}
		$data['spaces'] = $lists;
		$data['waiting'] = implode('、', $waiting);
		$data['soldout'] = implode('、', $soldout);

		$this->parser->parse('agreement.html', $data);
	}

	public function test()
	{
		$data = array();

		$lists = $waiting = $soldout = array();
		$this->db->select('spaceid, spacename, spaceabbr, maxspaces, inventory');
		$this->db->order_by('seqno');
		$query = $this->db->get('v_spaces');
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				if (strlen($row['spaceabbr']) == 1) {
					$lists[$row['spaceid']] = $row;
					$lists[$row['spaceid']]['count'] = 0;
					if ($row['inventory'] == 1) {
						$waiting[$row['spaceid']] = $row['spaceabbr'] . 'スペース';
					}
					if ($row['inventory'] == 9) {
						$soldout[$row['spaceid']] = $row['spaceabbr'] . 'スペース';
					}
				}
			}
		}
		$data['spaces'] = $lists;
		$data['waiting'] = implode('、', $waiting);
		$data['soldout'] = implode('、', $soldout);

		$this->parser->parse('agreement_test.html', $data);
	}
}

// vim:set ts=4:
// End of file ./application/controllers/agreement.php
