<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends RecOP_Controller {

	protected $form_prefix   = 'customer';		// フォーム名
	protected $table_name    = 'customers';		// テーブル名
	protected $table_prefix  = 'C';				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'customerid';	// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'corpname'    => 'trim|required|xss_clean',
		'corpkana'    => 'trim|required|xss_clean|prep_kana|valid_kana',
		'countrycode' => 'trim|xss_clean',
		'zip'         => 'trim|xss_clean|valid_zip',
		'prefecture'  => 'trim|xss_clean',
		'address1'    => 'trim|xss_clean',
		'address2'    => 'trim|xss_clean',
		'position'    => 'trim|xss_clean',
		'fullname'    => 'trim|required|xss_clean',
		'fullkana'    => 'trim|required|xss_clean|prep_kana|valid_kana',
		'phone'       => 'trim|xss_clean|valid_phone',
		'fax'         => 'trim|xss_clean|valid_phone',
		'url'         => 'trim|xss_clean|valid_hostname',
		'tas'         => 'trim|xss_clean',
		'napac'       => 'trim|xss_clean',
		'comment'     => 'trim|xss_clean',
	);
	protected $foreign_query = array(		// 全文検索用で使用するカラム
		'customerid', 'countrycode', 'zip', 'prefecture', 'address1', 'address2',
		'corpname', 'corpkana', 'fullname', 'fullkana', 'phone', 'url', 'comment',
	);

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$ppage = 200;

		$keyword = $this->input->get('q');
		$start = $this->input->get('start');
		$this->db->start_cache();
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$combined_query = array_fill_keys($this->foreign_query, $keyword);
			$this->db->where('expired', 0, FALSE);
			// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
			// $this->db->grouplike_start();
			$this->db->group_start();
			// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
			$this->db->or_like($combined_query);
			// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
			// $this->db->grouplike_end();
			$this->db->group_end();
			// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
		} else {
			$data['q'] = '';
			$this->db->where('expired', 0, FALSE);
		}
		$this->db->stop_cache();
		$this->db->from($this->table_name);
		$data['count'] = $this->db->count_all_results();
/*
		if ($start !== FALSE && is_numeric($start)) {
			$this->db->limit($ppage, $start);
			$data['page'] = ($start/$ppage)+1;
			$data['ppage'] = $ppage;
		} else {
			$this->db->limit($ppage);
			$data['page'] = 1;
			$data['ppage'] = $ppage;
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url(uri_folder_string().'/customer/') . '/?q=' . urlencode($keyword);
		$config['total_rows'] = $data['count'];
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'start';
		$config['per_page'] = $ppage;
		$config['num_links'] = 3;
		$this->pagination->initialize($config);
		$data['pagenation'] = $this->pagination->create_links();
*/
		$query = $this->db->get($this->table_name);
		$this->db->flush_cache();
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('country_model');
		$data['countrycode'] = $this->country_model->get_dropdown();
		$this->load->model('prefecture_model');
		$data['prefecture'] = $this->prefecture_model->get_dropdown();

		// LOOP COUNTER for SMARTY
		$data['years'] = array();
		for($i=date('Y')+1; $i>=1983; $i--) {
			$data['years'][] = $i;
		}
	}

	protected function get_record(&$data, $uid)
	{
		parent::get_record($data, $uid);

		// Additional HISTORY
		$this->db
			->select("customer_history.eventid, eventtype, DATE_FORMAT(event_sdate, '%Y') AS eventyear, promotion", FALSE)
			->from('customer_history')
			->join('events', 'events.eventid = customer_history.eventid')
			->where($this->foreign_keyid, $uid)
			->where('customer_history.expired', 0);

		$query = $this->db->get();
		if ($query->num_rows() >= 1) {
			foreach ($query->result_array() as $row) {
				if (isset($row['promotion']) && $row['promotion'] != '') {
					// 媒体系はカウント(=正会員カウント)に含めない
					$data['history'][$row['eventyear']][$row['eventtype'].'P'] = $row['promotion'];
				} else {
					$data['history'][$row['eventyear']][$row['eventtype']] = strtoupper(substr($row['eventtype'],0,1));
					// 出展回数のカウント
					if (!isset($data['history'][$row['eventtype']])) {
						$data['history'][$row['eventtype']] = 0;
					}
					$data['history'][$row['eventtype']]++;
				}
			}
		} else {
			$data['history'] = array();
		}
	}
}

/* End of file customer.php */
/* Location: ./application/controllers/(:any)/customer.php */
