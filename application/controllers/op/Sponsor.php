<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sponsor extends MemOP_Controller {

	protected $form_prefix   = 'sponsor';		// フォーム名
	protected $table_name    = 'sponsors';		// テーブル名
	protected $table_prefix  = 'V';				// テーブルの払出キー名(システムで一意)
	protected $foreign_keyid = 'spoid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'corpname'     => 'trim|required|xss_clean',
		'corpkana'     => 'trim|required|xss_clean|prep_kana|valid_kana',
		'zip'          => 'trim|required',
		'prefecture'   => 'trim|required',
		'address1'     => 'trim|required',
		'address2'     => 'trim|required',
		'position'     => 'trim|required',
		'fullname'     => 'trim|required|xss_clean',
		'fullkana'     => 'trim|required|xss_clean|prep_kana|valid_kana',
		'phone'        => 'trim|required',
		'fax'          => 'trim|required',
		'b_corpname'   => 'trim|required|xss_clean',
		'b_corpkana'   => 'trim|required|xss_clean|prep_kana|valid_kana',
		'b_zip'        => 'trim|required',
		'b_prefecture' => 'trim|required',
		'b_address1'   => 'trim|required',
		'b_address2'   => 'trim|required',
		'b_division'   => 'trim|required',
		'b_position'   => 'trim|required',
		'b_fullname'   => 'trim|required|xss_clean',
		'b_fullkana'   => 'trim|required|xss_clean|prep_kana|valid_kana',
		'b_phone'      => 'trim|required',
		'b_fax'        => 'trim|required',
//		'b_email'      => 'trim',
		'c_corpname'   => 'trim|required|xss_clean',
		'c_corpkana'   => 'trim|required|xss_clean|prep_kana|valid_kana',
		'c_zip'        => 'trim|required',
		'c_prefecture' => 'trim|required',
		'c_address1'   => 'trim|required',
		'c_address2'   => 'trim|required',
		'c_division'   => 'trim|required',
		'c_position'   => 'trim|required',
		'c_fullname'   => 'trim|required|xss_clean',
		'c_fullkana'   => 'trim|required|xss_clean|prep_kana|valid_kana',
		'c_phone'      => 'trim|required',
		'c_fax'        => 'trim|required',
		'c_email'      => 'trim',
		'promotion'    => 'trim',
		'comment'      => 'trim',
	);
	protected $foreign_query = array(		// 全文検索用で使用するカラム
		'spoid', 'corpname', 'corpkana', 'fullname', 'fullkana', 'phone',
		'b_corname', 'b_corpkana', 'c_corname', 'c_corpkana', 'comment',
	);

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();

		$keyword = $this->input->get('q');
//		$status = $this->input->get('s');
		$start = $this->input->get('start');
		$this->db->start_cache();
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$combined_query = array_fill_keys($this->foreign_query, $keyword);
			$this->db->where('expired', 0, FALSE);
			$this->db->or_like($combined_query);
		} else {
			$data['q'] = '';
			$this->db->where('expired', 0, FALSE);
		}
//		if ($status !== FALSE) {
//			switch($status) {
//			case 100:
//				$this->db->where_in('statusno', array(100,200));
//				break;
//			default:
//				$this->db->where('statusno', $status);
//				break;
//			}
//		}
		$this->db->stop_cache();
		$this->db->from($this->table_name);
		$data['count'] = $this->db->count_all_results();

		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($start !== FALSE && is_numeric($start)) {
		if ($start !== NULL && is_numeric($start)) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$this->db->limit(100, $start);
			$data['page'] = ($start/100)+1;
		} else {
			$this->db->limit(100);
			$data['page'] = 1;
		}

		$querystring = '';
		if ($keyword != '') {
			$querystring = ($querystring == '' ? '?':'&') . 'q=' . urlencode($keyword);
		}
//		if ($status != '') {
//			$querystring = ($querystring == '' ? '?':'&') . 's=' . urlencode($status);
//		}
		$this->load->library('pagination');
		$config['base_url'] = site_url('/op/sponcer/') . '/' . $querystring;
		$config['total_rows'] = $data['count'];
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'start';
		$config['per_page'] = '100';
		$config['num_links'] = 3;
		$this->pagination->initialize($config);
		$data['pagenation'] = $this->pagination->create_links();

		$query = $this->db->get($this->table_name);
		$this->db->flush_cache();
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->load->model('status_model');
		$data['status'] = $this->status_model->get_dropdown();
		$this->parser->parse($this->form_prefix . '_index.html', $data);
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('prefecture_model');
		$data['prefecture'] = $this->prefecture_model->get_dropdown();
	}
}

/* End of file sponsor.php */
/* Location: ./application/controllers/office/sponsor.php */
