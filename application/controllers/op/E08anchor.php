<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E08anchor extends ExhOP_Controller {

	protected $form_prefix   = 'e08anchor';		// フォーム名
	protected $form_appno    = 8;
	protected $table_name    = 'v_exapply_08';	// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'appid'      => 'trim',
		'exhboothid' => 'trim',
		'appno'      => 'trim',
		'seqno'      => 'trim',
		'outsourcing'=> 'trim|required',
		'corpname'   => 'trim',
		'corpkana'   => 'trim',
		'zip'        => 'trim',
		'prefecture' => 'trim|xss_clean',
		'address1'   => 'trim|xss_clean',
		'address2'   => 'trim|xss_clean',
		'division'   => 'trim|xss_clean',
		'position'   => 'trim|xss_clean',
		'fullname'   => 'trim',
		'fullkana'   => 'trim',
		'phone'      => 'trim',
		'fax'        => 'trim',
		'mobile'     => 'trim',
		'email'      => 'trim',
		'billid'     => 'trim|required',
		'anchorbolt' => 'trim|is_natural|required',
		'token'      => 'trim|xss_clean',
	);

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
		// 	$exhid = $this->member->get_exhid();
		// 	$exhboothid = $this->member->get_exhboothid();
		if ($this->member_lib->get_exhid()) {
			$exhid = $this->member_lib->get_exhid();
			$exhboothid = $this->member_lib->get_exhboothid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		} else {
			// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
			if(empty($data['foreign']['exhid'])) $data['foreign']['exhid'] = NULL;
			// Upgrade PHP7 - Fix “Undefined index” error - End by TTM
			$exhid = $data['foreign']['exhid'];
			$exhboothid = $data['foreign']['exhboothid'];
		}

		// 出展者情報の表示
		$this->load->model('exhibitors_model');
		$data['exhibitor'] = $this->exhibitors_model->read($exhid);
		// 請求先情報の表示
		$this->load->model('billing_model');
		$data['lists'] = $this->billing_model->readExhibitors($exhid);
		// 施工業者登録データ表示
		$this->db->where('exhboothid', $exhboothid);
		$query = $this->db->get('v_exapply_04');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['constructor'] = $query->row_array();
		} else {
			$data['constructor'] = array();
		}
	}

	protected function get_record(&$data, $uid)
	{
		// 小間から出展者IDを取るようにする.
		parent::get_record($data, $uid);

		// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
		if(empty($data['foreign']['exhboothid'])) $data['foreign']['exhboothid'] = NULL;
		// Upgrade PHP7 - Fix “Undefined index” error - End by TTM
		$this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_booth');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$data['foreign']['exhid'] = $row['exhid'];
		}
	}
				
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// protected function create_record($foreign)
	protected function create_record(&$foreign)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$foreign['appno'] = $this->form_appno;
		$foreign['seqno'] = 0;
		return parent::create_record($foreign);
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

	// 請求先の取得
	function regist()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function regist_confirm()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	// 更新処理では住所データを整理する。
	public function regist_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');
		$this->setup_form_ex($data);

		// データ整理
		$foreign2 = $data['foreign'];

		// 業者名・担当者名・電話番号・FAX番号の設定
		// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
		// if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==0){
		if(!empty($foreign2['outsourcing']) && $foreign2['outsourcing']==0 && !empty($data['constructor']['outsourcing']) && $data['constructor']['outsourcing']==0){
		// Upgrade PHP7 - Fix “Undefined index” error - End by TTM
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者の場合は出展者
			$foreign2['corpname']=$data['exhibitor']['corpname'];
			$foreign2['fullname']=$data['exhibitor']['fullname'];
			$foreign2['phone']=$data['exhibitor']['phone'];
			$foreign2['fax']=$data['exhibitor']['fax'];
		// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
		// }else if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==1){
		}else if(!empty($foreign2['outsourcing']) && $foreign2['outsourcing']==0 && !empty($data['constructor']['outsourcing']) && $data['constructor']['outsourcing']==1){
		// Upgrade PHP7 - Fix “Undefined index” error - End by TTM
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者以外の場合は施工業者
			$foreign2['corpname']=$data['constructor']['corpname'];
			$foreign2['fullname']=$data['constructor']['fullname'];
			$foreign2['phone']=$data['constructor']['phone'];
			$foreign2['fax']=$data['constructor']['fax'];
		}else{
			// 工事業者を記載した場合はそのまま記載する
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
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function change($uid='')
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->keep_flashdata('foreign');
		} else {
			$this->get_record($data, $uid);
			$flg = 1;
		}
/*
		$this->db->where('exhid',$data['foreign']['exhid']);
		$this->db->order_by('seqno','asc');
		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
			if($data['foreign']['outsourcing']==1 && $flg==1){
				foreach($data['lists'] as $lists_data){
					if($lists_data['corpname']==$data['foreign']['corpname'] && $lists_data['fullname']==$data['foreign']['fullname'] && $lists_data['phone']==$data['foreign']['phone']){
						$data['foreign']['billid']=$lists_data['billid'];
					}
				}
			}
		}
*/
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	
	function change_confirm()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	// 更新処理では住所データを整理する。
	public function change_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('lists');

		// ここで行うのは、基本データが欲しいため
		$this->setup_form_ex();

		// データ整理
		$foreign2 = $data['foreign'];
		unset($foreign2['billid']);

		// 業者名・担当者名・電話番号・FAX番号の設定
		if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==0){
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者の場合は出展者
			$foreign2['corpname']=$data['exhibitor']['c_corpname'];
			$foreign2['fullname']=$data['exhibitor']['c_fullname'];
			$foreign2['phone']=$data['exhibitor']['c_phone'];
			$foreign2['fax']=$data['exhibitor']['c_fax'];
		}else if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==1){
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者以外の場合は施工業者
			$foreign2['corpname']=$data['constructor']['corpname'];
			$foreign2['fullname']=$data['constructor']['fullname'];
			$foreign2['phone']=$data['constructor']['phone'];
			$foreign2['fax']=$data['constructor']['fax'];
		}else{
			// 工事業者を記載した場合はそのまま記載する
		}

		// データベースを更新
		$result = $this->update_record($foreign2);
		$line = $this->lang->line($result !== FALSE ? 'M2002':'N4002');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result === FALSE ? '0':'1'));

		if ($result !== FALSE) {
			$this->log_history('変更', $result);
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
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	
	function delete($uid='')
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$this->get_record($data, $uid);

		$this->session->set_flashdata('foreign', $data['foreign']);

		// 申し込み者連絡先表示
//		$this->db->where('exhid',$data['foreign']['exhid']);

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【詳細画面】
	public function detail($uid='')
	{
		// 出展者から見た場合はdetailを表示
		if (uri_folder_string() == '/ex') {
			$this->load->model('exapply_model');
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
			// $exhid = $this->member->get_exhid();
			// $exhboothid = $this->member->get_exhboothid();\
			$exhid = $this->member_lib->get_exhid();
			$exhboothid = $this->member_lib->get_exhboothid();
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$uid = $this->exapply_model->get_appid($exhboothid, $this->form_appno);
		}

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
		}
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【一覧画面】
	function index()
	{
		// 出展者から見た場合はdetailを表示
		if (uri_folder_string() == '/ex') {
			redirect(uri_class_string() . '/detail');
			exit;
		}

		// 管理者から見た場合は一覧を表示
		$this->slash_complete();
		$data = $this->setup_data();

		// 検索の場合はキーを付加
		$keyword = $this->input->get('q');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$marches = '';
			if (preg_match('/^([^:]+):([^:]+)$/', $keyword, $matches)) {
				switch($matches[1]) {
				default:
					// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
					// $this->db->grouplike_start();
					$this->db->group_start();
					// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
					$this->db->collate_like('e.corpname', $keyword);
					$this->db->or_collate_like('e.corpkana', $keyword);
					$this->db->or_collate_like('e.brandname', $keyword);
					$this->db->or_collate_like('e.brandkana', $keyword);
					// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
					// $this->db->grouplike_end();
					$this->db->group_end();
					// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
					break;
				}
			} else {
				// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
				// $this->db->grouplike_start();
				$this->db->group_start();
				// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
				$this->db->collate_like('e.corpname', $keyword);
				$this->db->or_collate_like('e.corpkana', $keyword);
				$this->db->or_collate_like('e.brandname', $keyword);
				$this->db->or_collate_like('e.brandkana', $keyword);
				// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
				// $this->db->grouplike_end();
				$this->db->group_end();
				// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
			}
		} else {
			$data['q'] = '';
		}

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno, e.corpname, e.corpkana, e.brandname, s.spaceabbr");
		$this->db->select("IFNULL(v4.corpname,v.corpname) v_corpname, IF(v.outsourcing>0,'','*') v_default, v.anchorbolt", FALSE);
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_08 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_04 v4', 'v4.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE) { 
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
			}
		} else {
			$data['lists'] = array();
		}

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function download_build()
	{
		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothno '小間番号', eb.exhid '出展者コード'");
		$this->db->select("e.corpname '出展者名', e.brandname '表示名', s.spaceabbr 'スペース'");
		$this->db->select("v.outsourcing 'アンカー業者登録'");
		$this->db->select("IF(v.outsourcing>0,v.zip,IF(v.outsourcing=0,v4.zip,'')) '(アンカー)郵便番号'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.corpname,IF(v.outsourcing=0,v4.corpname,'')) '(アンカー)業者名'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.prefecture,IF(v.outsourcing=0,IF(v4.corpname is null,'',v4.prefecture),'')) '(アンカー)都道府県'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.address1,IF(v.outsourcing=0,v4.address1,'')) '(アンカー)住所1'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.address2,IF(v.outsourcing=0,v4.address2,'')) '(アンカー)住所2'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.division,IF(v.outsourcing=0,v4.division,'')) '(アンカー)所属'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.position,IF(v.outsourcing=0,v4.position,'')) '(アンカー)役職'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.fullname,IF(v.outsourcing=0,v4.fullname,'')) '(アンカー)担当者'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.email,IF(v.outsourcing=0,v4.email,'')) '(アンカー)メールアドレス'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.mobile,IF(v.outsourcing=0,v4.mobile,'')) '(アンカー)携帯番号'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.phone,IF(v.outsourcing=0,v4.phone,'')) '(アンカー)TEL'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.fax,IF(v.outsourcing=0,v4.fax,'')) '(アンカー)FAX'", FALSE);
		$this->db->select("v.anchorbolt '申込本数'");
		$this->db->select("v.billid '請求先コード'");
		$this->db->select("bb.corpname '請求先', bb.zip '郵便番号', bb.countrycode '国', bb.prefecture '都道府県'");
		$this->db->select("bb.address1 '住所1', bb.address2 '住所2'");
		$this->db->select("bb.division '所属', bb.position '役職'");
		$this->db->select("bb.fullname '担当者', bb.fullkana '担当者カナ'");
		$this->db->select("bb.phone 'TEL', bb.fax 'FAX'");
		$this->db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$this->db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$this->db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$this->db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");
		$this->db->select("v.created '登録日時', v.updated '更新日時'");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('v_exapply_08 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_04 v4', 'v4.exhboothid = eb.exhboothid', 'left');
		$this->db->join('exhibitor_bill bb', 'bb.billid = v.billid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
	}

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

		// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
		if(empty($data['exhibitor']['c_email'])) $data['exhibitor']['c_email'] = NULL;
		// Upgrade PHP7 - Fix “Undefined index” error - End by TTM

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
}
// vim:ts=4
