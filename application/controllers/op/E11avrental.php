<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E11avrental extends ExhOP_Controller {

	protected $form_prefix	 = 'e11avrental';		// フォーム名
	protected $table_name	 = 'v_exapply_11';	// テーブル名
	protected $table_prefix  = 'AS';		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';	// テーブルの主キー名
	protected $foreign_token = 'token';		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'appid'   => 'trim',
		'exhboothid'   => 'trim',
		'appno'   => 'trim',
		'seqno'   => 'trim',
		'contact'		=> 'trim|required',
		'zip'			=> 'trim|xss_clean|valid_zip',
		'billid'		=> 'trim',
		'billid_c'		=> 'trim',
		'c_corpname'	=> 'trim|xss_clean',
		'c_corpkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'c_countrycode' => 'trim|xss_clean',
		'c_zip'			=> 'trim|xss_clean|valid_zip',
		'c_prefecture'	=> 'trim|xss_clean',
		'c_address1'	=> 'trim|xss_clean',
		'c_address2'	=> 'trim|xss_clean',
		'c_division'	=> 'trim|xss_clean',
		'c_position'	=> 'trim|xss_clean',
		'c_fullname'	=> 'trim|xss_clean',
		'c_fullkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'c_phone'		=> 'trim|xss_clean|valid_phone',
		'c_fax'			=> 'trim|xss_clean|valid_phone',
		'c_mobile'		=> 'trim|xss_clean|valid_phone',
		'c_email'		=> 'trim|xss_clean|valid_email',
		'token'   => 'trim|xss_clean',
	);
	protected $unitcode = array(
			'PDP40'   => 'quantity_1',
			'PDP30'   => 'quantity_2',
			'PDP20'   => 'quantity_3',
			'PASET-A' => 'quantity_4',
			'PASET-B' => 'quantity_5',
	);


	function __construct() {
		parent::__construct();
		$this->load->model('e11avrental_model');
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('prefecture_model');
		$data['prefecture'] = $this->prefecture_model->get_dropdown(TRUE);
	}

	protected function setup_form_ex(&$data)
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->get_exhid()) {
		// 	$exhid = $this->member->get_exhboothid();
		// 	$exhid = $this->member->get_exhid();
		if ($this->member_lib->get_exhid()) {
			$exhid = $this->member_lib->get_exhboothid();
			$exhid = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		} else {
			$exhid = $data['foreign']['exhboothid'];
			$exhid = $data['foreign']['exhid'];
		}
		// 出展者情報の表示
		$this->load->model('exhibitors_model');
		$data['exhibitor'] = $this->exhibitors_model->read($exhid);
		// 請求先情報の表示
		$this->load->model('billing_model');
		$data['lists'] = $this->billing_model->readExhibitors($exhid);
	}

	protected function get_record(&$data, $uid)
	{
		parent::get_record($data, $uid);

		// 小間IDから出展者番号を取得
		$this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_booth');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$data['foreign']['exhid'] = $row['exhid'];
		}
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function create_record($foreign)
	function create_record(&$foreign)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$foreign['appno'] = 11;
		$foreign['seqno'] = 0;
		return $this->e11avrental_model->create($foreign, 200, 'F');
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function update_record($foreign)
	function update_record($foreign = Array())
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		return $this->e11avrental_model->update($foreign);
	}

	function delete_record($foreign)
	{
		return $this->e11avrental_model->delete($foreign);
	}

	// チェックロジック
	// 13は全項目空欄の場合及び数値以外のものが混じっていた場合エラーとする。
	protected function check_logic(&$data)
	{
		$foreign = $data['foreign'];

		// validation rule
		$validation = array(		// 入力チェック用に使用するカラムとパターン
			'quantity_1'   => 'trim|xss_clean|is_natural',
			'quantity_2'   => 'trim|xss_clean|is_natural',
			'quantity_3'   => 'trim|xss_clean|is_natural',
			'quantity_4'   => 'trim|xss_clean|is_natural',
			'quantity_5'   => 'trim|xss_clean|is_natural',

		);

		if($foreign['quantity_1']==NULL && $foreign['quantity_2']==NULL && $foreign['quantity_3']==NULL && $foreign['quantity_4']==NULL && $foreign['quantity_5']==NULL){
			$data['message']['__all'] = "<p>数量を入力して下さい。</p>";
		}

		foreach($validation as $validation_key=>$validation_name){
			// Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
			// $this->form_validation->set_rules($validation_key,$this->lang->language[$validation_key].$i,$validation_name);
			$this->form_validation->set_rules($validation_key,$this->lang->language[$validation_key],$validation_name);
			// Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
		}

		if ($this->form_validation->run() == FALSE) {
			$msgall = validation_errors();
			$msgarr = explode("\n", $msgall);
			if (count($msgarr) > 5) {
				$msgarr = array_slice($msgarr, 0, 4);
				$msgarr[] = "<p>この他にも入力不備があります。<p>";
			}
			$data['message']['__all'] = implode("\n", $msgarr);
			foreach($this->foreign_value as $key=>$val) {
				$data['message'][$key] = strip_tags(form_error($key));
			}
		}

	}
	
	function detail($uid = '')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
		}

		$this->db->where('exhboothid',$data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_11_detail');
		if ($query->num_rows() > 0) {
			$detail = $query->result_array();
		}
				// unitcodeとquantityの関係
					// unitcodeとquantityの関係

		foreach($detail as $detail_data){
			$data['foreign'][$this->unitcode[$detail_data['unitcode']]]=$detail_data['quantity'];
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}


	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
		$this->db->select("e.corpname, e.corpkana, e.brandname, s.spaceabbr");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_11 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE) { 
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
			}
		} else {
			$data['lists'] = $query->array();
		}

		// 詳細レコードから抜き出して一覧に詰めていく
		$this->db->select('exhboothid, quantity, unitcode');
		$query = $this->db->get('v_exapply_11_detail');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$detail_lists = $query->result_array();

			$before_exhboothid = "";
			foreach($detail_lists as $detail_data){
				foreach($data['lists'] as $lists_key=>$lists_data){
					if($before_exhboothid==$lists_data['exhboothid']){
						unset($data['lists'][$lists_key]);
					}else{
						if($lists_data['exhboothid']==$detail_data['exhboothid']){
							$unitcode=str_replace("-","_",$detail_data['unitcode']);
							$data['lists'][$lists_key][$unitcode]=$detail_data['quantity'];
						}
						$before_exhboothid=$lists_data['exhboothid'];
					}
				}
			}
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function create($exhid, $boothid)
	public function create($exhid = null, $boothid = null)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$data['foreign']['exhid'] = $exhid;
		$data['foreign']['exhboothid'] = $boothid;
		$this->session->set_flashdata('foreign', $data['foreign']);
		redirect(uri_redirect_string() . '/../../regist');
	}

	function regist()
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		
		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
		}

		// unitcodeとquantityの関係
		$unitcode=array(
			'i.40_PDP' => 'quantity_1',
			'i.30_PDP' => 'quantity_2',
			'i.20_PDP' => 'quantity_3',
			'i.PA_A'   => 'quantity_4',
			'i.PA_B'   => 'quantity_5',
		);

		// 請求先表示
		$this->db->where('exhid',$data['foreign']['exhid']);
		$this->db->order_by('seqno','asc');

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		// 申し込み者連絡先表示
		$this->db->where('exhid', $data['foreign']['exhid']);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$contact = $query->result_array();
		}
		$data['contact']=$contact[0];
		$this->session->set_flashdata('contact', $data['contact']);

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function regist_in()
	{
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$data['lists'] = $this->session->flashdata('lists');	
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		$this->session->keep_flashdata('lists');

		parent::regist_in();
	}
	
	function regist_confirm()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		$this->session->keep_flashdata('lists');

		// 請求先表示用リスト
		$this->db->where('exhid',$data['foreign']['exhid']);
		$this->db->order_by('seqno','asc');
		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		// 連絡先表示用リスト
		$this->db->where('exhid', $data['foreign']['exhid']);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$contact = $query->result_array();
		}
		$data['contact'] = $contact[0];

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	// 更新処理では住所データを整理する。
	public function regist_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		$this->session->keep_flashdata('lists');

		// データ整理
		$foreign2 = $data['foreign'];

		// 業者名・担当者名・電話番号・FAX番号の設定
		if($foreign2['contact']==0) {
			// 出展者の場合は出展者
			$foreign2['c_corpname']=$data['contact']['corpname'];
			$foreign2['c_zip']=$data['contact']['zip'];
			$foreign2['c_prefecture']=$data['contact']['prefecture'];
			$foreign2['c_address1']=$data['contact']['address1'];
			$foreign2['c_address2']=$data['contact']['address2'];
			$foreign2['c_fullname']=$data['contact']['fullname'];
			$foreign2['c_phone']=$data['contact']['phone'];
			$foreign2['c_fax']=$data['contact']['fax'];
		} else {
			// 他の連絡先の場合は入力値
			$foreign2['c_corpname']=$foreign['corpname'];
			$foreign2['c_zip']=$foreign['zip'];
			$foreign2['c_prefecture']=$foreign['prefecture'];
			$foreign2['c_address1']=$foreign['address1'];
			$foreign2['c_address2']=$foreign['address2'];
			$foreign2['c_fullname']=$foreign['fullname'];
			$foreign2['c_phone']=$foreign['phone'];
			$foreign2['c_fax']=$foreign['fax'];
		}

		// データベースに登録
		$result = $this->create_record($foreign2);
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



	function registed()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		$this->session->keep_flashdata('lists');

		// 請求先表示用リスト
		$this->db->where('exhid',$data['foreign']['exhid']);
		$this->db->order_by('seqno','asc');
		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		// 連絡先表示用リスト
		$this->db->where('exhid', $data['foreign']['exhid']);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$contact = $query->result_array();
		}
		$data['contact'] = $contact[0];

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function change($uid='')
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		// unitcodeとquantityの関係
		$unitcode = array(
			'PDP40'   => 'quantity_1',
			'PDP30'   => 'quantity_2',
			'PDP20'   => 'quantity_3',
			'PASET-A' => 'quantity_4',
			'PASET-B' => 'quantity_5',
		);

		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->keep_flashdata('foreign');
			$from_record = 0;
		} else {
			$this->get_record($data, $uid);
			$from_record = 1;

			$this->db->where('exhboothid',$data['foreign']['exhboothid']);
			$query = $this->db->get('v_exapply_11_detail');
			if ($query->num_rows() > 0) {
				$detail = $query->result_array();
				foreach($detail as $detail_data){
					$data['foreign'][$unitcode[$detail_data['unitcode']]]=$detail_data['quantity'];
				}
			}
		}

		$this->db->where('exhid',$data['foreign']['exhid']);
		$this->db->order_by('seqno','asc');

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
			if($data['foreign']['contact'] == 1 && $from_record == 1){
				foreach($data['lists'] as $lists_data){
					if($lists_data['corpname']==$data['foreign']['c_corpname'] && $lists_data['fullname']==$data['foreign']['c_fullname'] && $lists_data['phone']==$data['foreign']['c_phone']){
						$data['foreign']['billid_c']=$lists_data['billid'];
					}
				}
			}
		}

		$this->db->where('exhid',$data['foreign']['exhid']);
		$this->db->order_by('seqno','asc');

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		
		// 申し込み者連絡先表示
		$this->db->where('exhid',$data['foreign']['exhid']);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$contact = $query->result_array();
		}
		$data['contact']=$contact[0];
		$this->session->set_flashdata('contact', $data['contact']);

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function change_in()
	{
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		$this->session->keep_flashdata('lists');

		parent::change_in();
	}

	function change_confirm()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');

		$this->db->where('billid',$data['foreign']['billid']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
			$this->session->set_flashdata('lists', $data['lists']);
		}

		$this->db->where('billid',$data['foreign']['billid_c']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists2'] = $query->result_array();
			$this->session->set_flashdata('lists2', $data['lists2']);
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}
	
		// 更新処理では住所データを整理する。
	public function change_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');
		$this->session->keep_flashdata('lists');

		// データ整理
		$foreign2 = $data['foreign'];

		// 業者名・担当者名・電話番号・FAX番号の設定
		if($foreign2['contact']==0) {
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者の場合は出展者
			$foreign2['c_corpname']=$data['contact']['corpname'];
			$foreign2['c_zip']=$data['contact']['zip'];
			$foreign2['c_prefecture']=$data['contact']['prefecture'];
			$foreign2['c_address1']=$data['contact']['address1'];
			$foreign2['c_address2']=$data['contact']['address2'];

			$foreign2['c_fullname']=$data['contact']['fullname'];
			$foreign2['c_phone']=$data['contact']['phone'];
			$foreign2['c_fax']=$data['contact']['fax'];
		} else {
			// 工事業者を記載した場合
			$foreign2['c_corpname']=$data['lists2'][0]['corpname'];
			$foreign2['c_zip']=$data['lists2'][0]['zip'];
			$foreign2['c_prefecture']=$data['lists2'][0]['prefecture'];
			$foreign2['c_address1']=$data['lists2'][0]['address1'];
			$foreign2['c_address2']=$data['lists2'][0]['address2'];

			$foreign2['c_fullname']=$data['lists2'][0]['fullname'];
			$foreign2['c_phone']=$data['lists2'][0]['phone'];
			$foreign2['c_fax']=$data['lists2'][0]['fax'];
		}

		// データベースを更新
		$result = $this->update_record($foreign2);
		$line = $this->lang->line($result !== FALSE ? 'M2002':'N4002');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result === FALSE ? '0':'1'));

		if ($result !== FALSE) {
			$this->log_history('編集', $result);
			$this->after_change($data);
		}

		// 登録完了画面へ
		redirect(uri_redirect_string() . '/changed');

	}

	function changed()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['contact'] = $this->session->flashdata('contact');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('contact');

		$this->db->where('billid',$data['foreign']['billid']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->db->where('billid',$data['foreign']['billid_c']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists2'] = $query->result_array();
			$this->session->set_flashdata('lists2', $data['lists2']);
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}

	function delete($uid='')
	{
		$data = $this->setup_data();
		
		$this->setup_form($data);
		$this->get_record($data, $uid);

		// unitcodeとquantityの関係
		$unitcode = array(
			'PDP40'   => 'quantity_1',
			'PDP30'   => 'quantity_2',
			'PDP20'   => 'quantity_3',
			'PASET-A' => 'quantity_4',
			'PASET-B' => 'quantity_5',
		);

		$this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_11_detail');
		if ($query->num_rows() > 0) {
			$detail = $query->result_array();
		}

		foreach($detail as $detail_data){
			$data['foreign'][$unitcode[$detail_data['unitcode']]]=$detail_data['quantity'];
		}

		$this->session->set_flashdata('foreign', $data['foreign']);

//		$this->db->where('billid',$data['foreign']['billid']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		// 連絡先表示
		$this->db->where('exhid', $data['foreign']['exhid']);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$contact = $query->result_array();
		}
		$data['contact']=$contact[0];
		$this->session->set_flashdata('contact', $data['contact']);

		// 請求先
		$this->db->where('billid',$data['foreign']['billid']);
		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}
/*ゴミ？
	function history()
	{
		// 申し込み者連絡先表示
		$this->db->where("exhid",$_POST['exhid']);

		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$contact = $query->result_array();
		}
		$data['contact']=$contact[0];

		$this->db->where("exhid",$_POST['exhid']);

		$query = $this->db->get('v_exapply_11_detail');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}
*/
	public function after_regist(&$data)
	{
		$this->after_notify($data, 'regist');
	}

	public function after_change(&$data)
	{
		$this->after_notify($data, 'change');
	}

	protected function after_notify(&$data, $action='thanks')
	{
		// 更新日はデータベース日付なので、もう一度取り直す.
		$uid = $data['foreign'][$this->foreign_keyid];
		$this->get_record($data, $uid);
		$this->setup_form_ex($data);

		$this->load->library('email');
		$this->config->load('bcc', FALSE, TRUE);

		$mailto = array($data['exhibitor']['c_email']);
		if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
			$bcc = $this->config->item(strtolower(substr(__CLASS__,0,3)));
			$mailfrom = 'info@tokyoautosalon.jp';
			$namefrom = 'TOKYO AUTO SALON';
		} else {
			$bcc = $this->config->item(strtolower(substr(__CLASS__,0,3)));
			$mailfrom = 'miko@tokyoautosalon.jp';
			$namefrom = 'TOKYO AUTO SALON(TEST MAIL)';
		}

		$text = $this->parser->parse('mail/'.strtolower(__CLASS__).'_'.$action.'.txt', $data, TRUE);
		if (strpos($text, "\n") !== FALSE) {
			list($subject, $message) = explode("\n", $text, 2);
		} else {
			$subject = 'TOKYO AUTO SALON 2020';
			$message = $text;
		}

		$this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
		$this->email->to($mailto);
		$this->email->bcc($bcc);
		$this->email->reply_to($mailfrom);
		$this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
		$this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
		$this->email->send();
	}

	protected function download_build()
	{
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
		$this->db->select("e.corpname, e.brandname, s.spaceabbr");
		$this->db->select("vd.unitcode, vd.unitname, vd.unitprice, vd.quantity");
		$this->db->select("v.billid");
		$this->db->select("bb.zip b_zip, bb.countrycode b_countrycode, bb.prefecture b_prefecture");
		$this->db->select("bb.address1 b_address1, bb.address2 b_address2");
		$this->db->select("bb.division b_division, bb.position b_position");
		$this->db->select("bb.fullname b_fullname, bb.fullkana b_fullkana");
		$this->db->select("bb.phone b_phone, bb.fax b_fax");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_11 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_11_detail vd', 'vd.exhboothid = eb.exhboothid', 'left');
		$this->db->join('exhibitor_bill bb', 'bb.billid = v.billid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where_in('s.spaceabbr', array('B','E','F'));
	}
}
