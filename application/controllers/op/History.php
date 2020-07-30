<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class History extends RecOP_Controller {

	protected $form_prefix   = 'history';	// フォーム名
	protected $table_name    = 'histories';	// テーブル名
	protected $table_prefix  = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'historyid';	// テーブルの主キー名
	protected $foreign_token = FALSE;		// ２重更新・削除防止のための項目
	protected $foreign_value = array();		// 入力チェック用に使用するカラムとパターン
	protected $foreign_query = array();		// 全文検索用で使用するカラム
	protected $pp = 25;

	public function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();

		$keyword = $this->input->get('q');
		$start = $this->input->get('start');
		$this->db->start_cache();
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$combined_query = array_fill_keys($this->foreign_query, $keyword);
			$this->db->or_like($combined_query);
		} else {
			$data['q'] = '';
		}
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->db->where('memberid', $this->member->get_userid());
		$this->db->where('memberid', $this->member_lib->get_userid());
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->db->order_by('created', 'desc');
		$this->db->stop_cache();
		$this->db->from($this->table_name);
		$data['count'] = $this->db->count_all_results();
		$data['pp'] = $this->pp;

		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($start !== FALSE && is_numeric($start)) {
		if ($start !== NULL && is_numeric($start)) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$this->db->limit($this->pp, $start);
			$data['page'] = ($start/$this->pp)+1;
		} else {
			$this->db->limit($this->pp);
			$data['page'] = 1;
		}

		$this->load->library('pagination');
		$pn['base_url'] = site_url(uri_folder_string().'/history/') . '/?q=' . urlencode($keyword);
		$pn['total_rows'] = $data['count'];
		$pn['page_query_string'] = TRUE;
		$pn['query_string_segment'] = 'start';
		$pn['per_page'] = $this->pp;
		$pn['num_links'] = 3;
		$this->pagination->initialize($pn);
		$data['pagenation'] = $this->pagination->create_links();

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
}

/* End of file history.php */
/* Location: ./application/controllers/(:any)/history.php */
