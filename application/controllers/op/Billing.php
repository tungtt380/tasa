<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 請求先登録
 * デフォルト以外に追加請求先を登録する
 */
class Billing extends MemOP_Controller {

	protected $form_prefix	 = 'billing';			// フォーム名
	protected $table_name	 = 'exhibitor_bill';	// テーブル名
	protected $table_prefix  = 'B';					// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;				// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'billid';			// テーブルの主キー名
	protected $foreign_token = 'token';				// ２重更新・削除防止のための項目
	protected $foreign_order = 'seqno ASC';
	protected $foreign_value = array(				// 入力チェック用に使用するカラムとパターン
		'exhid'		  => 'trim|xss_clean',
		'corpname'	  => 'trim|required|xss_clean',
		'corpkana'	  => 'trim|required|xss_clean|prep_kana|valid_kana',
		'countrycode' => 'trim|required|xss_clean',
		'zip'		  => 'trim|required|xss_clean|valid_zip',
		'prefecture'  => 'trim|xss_clean',
		'address1'	  => 'trim|required|xss_clean',
		'address2'	  => 'trim|xss_clean',
		'division'	  => 'trim|xss_clean',
		'position'	  => 'trim|xss_clean',
		'fullname'	  => 'trim|required|xss_clean',
		'fullkana'	  => 'trim|required|xss_clean|prep_kana|valid_kana',
		'phone'		  => 'trim|required|xss_clean|valid_phone',
		'fax'		  => 'trim|xss_clean|valid_phone',
	);

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('country_model');
		$data['countrycode'] = $this->country_model->get_dropdown();
		$this->load->model('prefecture_model');
		$data['prefecture'] = $this->prefecture_model->get_dropdown(TRUE);
		$this->load->model('category_model');
		$data['category'] = $this->category_model->get_dropdown();
		$this->load->model('section_model');
		$data['section'] = $this->section_model->get_dropdown();
		$this->load->model('booth_model');
		$data['booth'] = $this->booth_model->get_dropdown(FALSE,TRUE);
		$data['boothgroup'] = $this->booth_model->get_dropdown(TRUE,TRUE);
	}

	protected function create_record(&$foreign) {
//		$foreign['exhid'] = $this->member->get_exhid();		
		parent::create_record($foreign);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function create()
	public function create($uid = '')
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $data['foreign']['exhid'] = $this->member->get_exhid();
		$data['foreign']['exhid'] = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->session->set_flashdata('foreign', $data['foreign']);
		redirect(uri_redirect_string() . '/regist');
	}

	//【一覧画面】
	public function index()
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

		// 管理者からの一覧は、全て見えるようにする
//		$this->db->where('exhid', $this->member->get_exhid());
		$this->db->select('eb.*, e.brandname, e.brandkana');
		$this->db->from($this->table_name . ' eb');
		$this->db->join('v_exhibitors_search e', "e.exhid = eb.exhid AND e.expired = 0", 'left');
		$this->db->where('eb.expired', 0);
		$this->db->where("(`e`.`statusno` IN ('400','401','500','900') OR `eb`.`exhid` IS NULL)", NULL, FALSE);

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		$this->setup_calc($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function combine_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->input->post();
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
		if (!isset($data['message']) || empty($data['message'])) {
			$this->check_logic($data);
		}

		// 入力不備ならリダイレクト
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/');
		}

		// 確認画面にリダイレクト
		redirect(uri_redirect_string() . '/combine_confirm');
	}

	function combine_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');

		$this->db->select('eb.*, e.brandname, e.brandkana');
		$this->db->from($this->table_name . ' eb');
		$this->db->join('v_exhibitors_search e', "e.exhid = eb.exhid AND e.expired = 0", 'left');
		$this->db->where('eb.expired', 0);
		$this->db->where("(`e`.`statusno` IN ('400','401','500','900') OR `eb`.`exhid` IS NULL)", NULL, FALSE);
		$this->db->where_in("billid", $data['foreign']['billid']);

		$query = $this->db->get();
		if ($query !== FALSE) {
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
			}
		}

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function combine_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['foreign']['parentid'] = $this->input->post('parentid');
		$this->session->keep_flashdata('foreign');

		log_message('debug', __FUNCTION__);
		log_message('debug', var_export($data['foreign'],TRUE));
//die(var_export($data['foreign'],TRUE));

		// データベースに登録
		$result = $this->combine_record($data['foreign']);

		$line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);

		if ($result !== FALSE) {
			$this->log_history('名寄せ登録', $result);
			$this->after_regist($data);
		}

		// 登録完了画面へ
		redirect(uri_redirect_string() . '/combined');
	}

	protected function combine_record(&$foreign)
	{
		$maxcount = count($foreign['billid']);
		$result = TRUE;

		// トランザクションの開始
		$this->db->trans_start();

		for ($i = 0; $i < $maxcount; $i++) {
			if ($result === TRUE) {
				if ( $foreign['parentid'] == $foreign['billid'][$i]) {
					$this->db->set('parentbillid', 'NULL', FALSE);
				} else {
					$this->db->set('parentbillid',$foreign['parentid']);
				}
				$this->db->where('billid',$foreign['billid'][$i]);
				if (!$this->db->update('exhibitor_bill')) {
					log_message('notice', $this->db->last_query());
					$result = FALSE;
				}
			}
		}
		// すべてうまくいったならコミットする
		if ($result === FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		return $result;
	}

	function combined()
	{
		$data = $this->setup_data();

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
}
