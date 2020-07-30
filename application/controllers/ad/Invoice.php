<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//
// 請求のマージ
//
class Invoice extends ExhOP_Controller
{
	protected $form_prefix	 = 'invoice';		// フォーム名
	protected $table_name	 = 'invoice';		// テーブル名
	protected $table_prefix  = 'V';				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'invoiceid';		// テーブルの主キー名
	protected $foreign_token = FALSE;			// ２重更新・削除防止のための項目

	function __construct()
	{
		parent::__construct();
		$this->load->model('invoice_model');
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('space_model');
		$data['space'] = $this->space_model->get_dropdown();
	}

	public function search()
	{
		$keyword = $this->input->post('q');
		$spaces = $this->input->post('s');
		$types = $this->input->post('t');
		$promo = $this->input->post('p');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
		}
		if ($spaces != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 's=' . rawurlencode($spaces);
		}
		if ($promo != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'p=' . rawurlencode($promo);
		}
		if ($types != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 't=' . rawurlencode($types);
		}
		if ($querystring != '') {
			redirect('/' . dirname(uri_string()) . '/' . $querystring);
		}
		redirect('/' . dirname(uri_string()) . '/./');
	}

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$this->setup_form($data);

		$keyword = $this->input->get('q');
		$spaces = $this->input->get('s');
		$promo = $this->input->get('p');
		$types = $this->input->get('t');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$marches = '';
			if (preg_match('/^([^:]+):([^:]+)$/', $keyword, $matches)) {
				switch($matches[1]) {
				case 'promotion':
					$this->db->like('promotion', $matches[2]);
					break;
				default:
					$this->db->collate_like('scantext', $keyword);
					break;
				}
			} else {
				$this->db->collate_like('scantext', $keyword);
			}
		} else {
			$data['q'] = '';
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($spaces !== FALSE) {
		if ($spaces !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['s'] = $spaces;
			$this->db->like('scanboothid', '@'.$spaces.'@');
		} else {
			$data['s'] = '';
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($promo !== FALSE) {
		if ($promo !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['p'] = $promo;
			$this->db->like('promotion', $promo);
		} else {
			$data['p'] = '';
		}

		if ($this->foreign_order != '') {
			$this->db->order_by($this->foreign_order);
		}

		// 管理者からの一覧は、全て見えるようにする
		$this->db->select('iv.*, ip.suminvoiceamount, ip.sumpaymentamount, ip.sumprice, ip.paymentdate');
		$this->db->select('bb.b_corpname AS corpname, bb.corpkana AS corpkana, bb.scanbooth AS scanbooth');
		$this->db->from($this->table_name . ' iv');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = iv.billid');
		$this->db->join('v_invoice_payment ip', 'ip.invoiceid = iv.invoiceid', 'left');
		$this->db->where('iv.expired', 0);

		$data['lists'] = array();

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result_array();
			foreach($result as $record) {
				$data['lists'][$record['invoiceid']] = $record;
			}
		}
		// 詳細から出展料などの値を掴む(本来はSQLで上記をやるほうが望ましい)
		$this->db->select('id.invoiceid, id.itemtype');
		$this->db->from('invoice_detail id');
		$this->db->join('invoice iv', 'id.invoiceid = iv.invoiceid AND iv.expired = 0');
		$this->db->where('id.expired', 0);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$detail = $query->result_array();
			foreach($detail as $record) {
				if (isset($data['lists'][$record['invoiceid']])) {
					$data['lists'][$record['invoiceid']]['itemtype'] = $record['itemtype'];
				}
			}
		}

		$this->setup_calc($data);
		$this->parser->parse('ad/'.$this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function setup_calc(&$data)
	{
		foreach($data['lists'] as $key=>$rec) {
			if (isset($rec['sumpaymentamount']) && $rec['sumpaymentamount'] != '') {
				if (intval($rec['sumpaymentamount']) >= $rec['subtotal']-$rec['discount']) {
					$data['lists'][$key]['paid'] = 1;
				}
			}
		}
	}

	function get_record(&$data, $uno)
	{
		$data['foreign'] = $this->invoice_model->read(FALSE, $uno);
	}

	function regist() {
	}
	function create() {
	}
	function delete() {
	}

	function merge()
	{
		$keyword = $this->input->get('q');
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function merge_in()
	{
	}

	function merge_confirm()
	{
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function merge_confirm_in()
	{
	}

	function merged()
	{
		$this->completed($this->form_prefix.'_'.__FUNCTION__);
	}

}
// vim:ts=4
// End of file controllers/ad/invoice.php
