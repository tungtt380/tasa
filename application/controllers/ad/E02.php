<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E02 extends ExhOP_Controller {

	protected $form_appno    = 2;
	protected $form_prefix   = 'ad/e02';		// フォーム名
	protected $table_name    = 'v_exapply_02';	// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'appid'   => 'trim',
		'exhboothid'   => 'trim',
		'appno'   => 'trim',
		'seqno'   => 'trim',
		'zip'           => 'trim|xss_clean|valid_zip',
		'prefecture'   => 'trim|xss_clean',
		'address1'   => 'trim|xss_clean',
		'address2'   => 'trim|xss_clean',
		'phone'   => 'trim|xss_clean|valid_phone',
		'fax'   => 'trim|xss_clean|valid_phone',
		'email'         => 'trim|xss_clean|valid_email',
		'url'         => 'trim|xss_clean',
		'prcomment'   => 'trim|xss_clean',
		'publicaddress'   => 'trim|required',
		'publicphone'   => 'trim|required',
		'publicfax'   => 'trim|required',
		'publicurl'   => 'trim|required',
		'publicemail'   => 'trim|required',
	);

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
		$this->db->select("e.corpname, e.corpkana, e.brandname, e.brandkana, s.spaceabbr");
		$this->db->select("vd.seqno, vd.photo");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_02 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_02_detail vd', 'vd.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query === FALSE) {
			die($this->db->last_query());
		}
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}


		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
}
