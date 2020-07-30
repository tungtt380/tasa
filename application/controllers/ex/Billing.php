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
		'corpname'	  => 'trim|xss_clean',
		'corpkana'	  => 'trim|xss_clean|prep_kana|valid_kana',
		'countrycode' => 'trim|xss_clean',
		'zip'		  => 'trim|xss_clean|valid_zip',
		'prefecture'  => 'trim|xss_clean',
		'address1'	  => 'trim|xss_clean',
		'address2'	  => 'trim|xss_clean',
		'division'	  => 'trim|xss_clean',
		'position'	  => 'trim|xss_clean',
		'fullname'	  => 'trim|xss_clean',
		'fullkana'	  => 'trim|xss_clean|prep_kana|valid_kana',
		'phone'		  => 'trim|xss_clean|valid_phone',
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

	protected function create_record(&$foreign)
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $foreign['exhid'] = $this->member->get_exhid();
		$foreign['exhid'] = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
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

		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $data['foreign']['exhid'] = $this->member->get_exhid();
		$data['foreign']['exhid'] = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM

		// 登録できる請求先は制限があるため、特に検索はしない
		if ($this->foreign_order != '') {
			$this->db->order_by($this->foreign_order);
		}

		// 一覧は、特定のユーザのみ見えるようにする
		$this->db->where('exhid', $data['foreign']['exhid']);

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		$this->setup_calc($data);
		$this->parser->parse('ex/'.$this->form_prefix.'_'.__FUNCTION__, $data);
	}
}
