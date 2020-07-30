<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E03 extends RecOP_Controller {

    protected $form_prefix   = 'ad/e03';				// フォーム名
    protected $table_name    = 'v_members_exhibitor';	// テーブル名

    function __construct()
	{
        parent::__construct();
    }

	function index()
	{
		$data = $this->setup_data();
		$this->setup_form($data);

        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
        $this->db->select("e.corpname, e.corpkana, e.brandname, e.brandkana, s.spaceabbr");
        $this->db->select("vd.seqno, vd.itemphoto");
        $this->db->select("v.appid, v.created, v.updated");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_03 v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->join('v_exapply_03_detail vd', 'vd.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));
        $this->db->order_by('v.exhboothid ASC, vd.seqno ASC');

        $query = $this->db->get();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $data['lists'] = $query->result_array();
        } else {
            $data['lists'] = array();
        }

        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
}
