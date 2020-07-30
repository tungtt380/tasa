<?php

class Payment extends ExhOP_Controller
{
	protected $form_prefix	 = 'payment';		// フォーム名
	protected $table_name	 = 'payment';		// テーブル名
	protected $table_prefix  = 'P';				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'paymentid';		// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_order = 'paymentno ASC';
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'paymentid'   => 'trim|xss_clean',
		'paymentno'   => 'trim|xss_clean',
		'paymentdate' => 'trim|required|xss_clean',
		'paymentcode' => 'trim|required|xss_clean',
		'payeeid'	  => 'trim|required|xss_clean',
		'corpname'	  => 'trim|required|xss_clean',
		'deposit'	  => 'trim|required|prep_nocomma|is_natural',
		'comment'	  => 'trim|xss_clean',
/*
		'division'	  => 'trim|xss_clean',
		'position'	  => 'trim|xss_clean',
		'fullkana'	  => 'trim|required|xss_clean|prep_kana|valid_kana',
		'phone'		  => 'trim|required|xss_clean|valid_phone',
*/
	);

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('paymenttype_model');
		$data['paymenttype'] = $this->paymenttype_model->get_dropdown();
		$this->load->model('payee_model');
		$data['payee'] = $this->payee_model->get_dropdown();
	}

	protected function create_record(&$foreign)
	{
		$foreign['paymentno'] = date('YmdHis');
		return parent::create_record($foreign);
	}

	protected function get_record(&$data, $uid)
	{
		$this->load->model('payment_model');
		$data['foreign'] = $this->payment_model->read(FALSE, $uid);
	}

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$this->setup_form($data);

		if ($this->foreign_order != '') {
			$this->db->order_by($this->foreign_order);
		}

		$keyword = $this->input->get('q');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$this->db->collate_like('scantext', $keyword);
		} else {
			$data['q'] = '';
		}
		$isdate = $this->input->get('isdate');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($isdate !== FALSE && strtotime($isdate) ) {
		if ($isdate !== NULL && strtotime($isdate) ) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['isdate'] = $isdate;
			$this->db->where('pa.created >=',$isdate);
		}
		$iedate = $this->input->get('iedate');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($iedate !== FALSE && strtotime($iedate) ) {
		if ($iedate !== NULL && strtotime($iedate) ) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['iedate'] = $iedate;
			$this->db->where('pa.created <=', $iedate . ' 23:59:59');
		}
		$dsdate = $this->input->get('dsdate');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($dsdate !== FALSE && strtotime($dsdate) ) {
		if ($dsdate !== NULL && strtotime($dsdate) ) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['dsdate'] = $dsdate;
			$this->db->where('pa.paymentdate >=', $dsdate);
		}
		$dedate = $this->input->get('dedate');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($dedate !== FALSE && strtotime($dedate) ) {
		if ($dedate !== NULL && strtotime($dedate) ) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['dedate'] = $dedate;
			$this->db->where('pa.paymentdate <=', $dedate . ' 23:59:59');
		}

		// 管理者からの一覧は、全て見えるようにする
		$this->db->select('pa.*');
		$this->db->select('bb.b_corpname, bb.b_corpkana, bb.brandname');
		$this->db->from('payment pa');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = pa.billid', 'left');
		$this->db->where('pa.expired', 0);

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		$this->setup_calc($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function search()
	{
		$keyword = $this->input->post('q');
		$isdate = $this->input->post('isdate');
		$iedate = $this->input->post('iedate');
		$dsdate = $this->input->post('dsdate');
		$dedate = $this->input->post('dedate');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
		}
		if ($isdate != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'isdate=' . rawurlencode($isdate);
		}
		if ($iedate != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'iedate=' . rawurlencode($iedate);
		}
		if ($dsdate != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'dsdate=' . rawurlencode($dsdate);
		}
		if ($dedate != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'dedate=' . rawurlencode($dedate);
		}
		if ($querystring != '') {
			redirect('/' . dirname(uri_string()) . '/' . $querystring);
		}
		redirect('/' . dirname(uri_string()) . '/./');
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function create()
	public function create($uid = '')
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$data['foreign']['paymentdate'] = date('Y-m-d');
		$this->session->set_flashdata('foreign', $data['foreign']);
		redirect(uri_redirect_string() . '/regist');
	}

	public function regist_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');

		// データベースに登録
		$result = $this->create_record($data['foreign']);
		$this->session->set_flashdata('foreign', $data['foreign']);
		$line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result !== FALSE) ? '1':'0');

		if ($result !== FALSE) {
			$this->log_history('登録', $result);
			$this->after_regist($data);
		}
		// 登録完了画面へ
		redirect(uri_redirect_string() . '/registed');
	}

	public function detail($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/', 'location', 302);
		}

		// 本来は、ここでは引かない(後のリファクタリング対象)
		$this->db->select('pa.*');
		$this->db->select('bb.b_corpname, bb.b_corpkana, bb.brandname');
		$this->db->from('payment pa');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = pa.billid', 'left');
		$this->db->where('pa.expired', 0);
		$this->db->where('pa.paymentno', $uid);
		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$row = $query->row_array();
			$data['foreign']['b_corpname'] = $row['b_corpname'];
			$data['foreign']['b_corpkana'] = $row['b_corpkana'];
			$data['foreign']['brandname'] = $row['brandname'];
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function download_build()
	{
		$this->db->select('pa.paymentid, pa.paymentno, pa.created, pa.paymentdate, pa.billid');
		$this->db->select('bb.b_corpname, bb.b_corpkana, bb.brandname');
		$this->db->select('pt.paymentname, pe.bankname, pa.deposit, pa.charge, pa.amount, pa.comment');
		$this->db->from('payment pa');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = pa.billid', 'left');
		$this->db->join('payment_type pt', 'pt.paymentcode = pa.paymentcode', 'left');
		$this->db->join('payee pe', 'pe.payeeid = pa.payeeid', 'left');
		$this->db->where('pa.expired', 0);
	}
}
// vim:ts=4
// End of file op/payment.php
