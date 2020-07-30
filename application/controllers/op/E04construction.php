<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E04construction extends ExhOP_Controller {

	protected $form_appno	 = 4;
	protected $form_prefix	 = 'e04construction';	// フォーム名
	protected $table_name	 = 'v_exapply_04';		// テーブル名
	protected $table_prefix  = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';		// テーブルの主キー名
	protected $foreign_token = 'token';		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'appid'			=> 'trim',
		'exhboothid'	=> 'trim',
		'appno'			=> 'trim',
		'seqno'			=> 'trim',
		'outsourcing'	=> 'trim|required',
		'corpname'		=> 'trim|xss_clean',
		'zip'			=> 'trim|xss_clean|valid_zip',
		'prefecture'	=> 'trim|xss_clean',
		'address1'		=> 'trim|xss_clean',
		'address2'		=> 'trim|xss_clean',
		'division'		=> 'trim|xss_clean',
		'fullname'		=> 'trim|xss_clean',
		'email'			=> 'trim|xss_clean|valid_email',
		'mobile'		=> 'trim|xss_clean|valid_phone',
		'phone'			=> 'trim|xss_clean|valid_phone',
		'fax'			=> 'trim|xss_clean|valid_phone',
		'billid'		=> 'trim|required',
		'parapet'		=> 'trim|xss_clean|numeric',
		'corplogo'		=> 'trim|xss_clean|numeric',
		'wallpaper'		=> 'trim|xss_clean|numeric',
		'wallcolor'		=> 'trim|xss_clean',
		'punchcarpet'	=> 'trim|xss_clean|numeric',
		'punchcolor'	=> 'trim|xss_clean',
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
			$exhid = $data['foreign']['exhid'];
			$exhboothid = $data['foreign']['exhboothid'];
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
		// 小間から出展者IDを取るようにする.
		parent::get_record($data, $uid);

		$this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_booth');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$data['foreign']['exhid'] = $row['exhid'];
		}
	}

	// 「出展者以外(施工業者)に連絡」の場合のチェックロジック
	protected function check_logic(&$data)
	{
		$foreign = $data['foreign'];

		if ($foreign['outsourcing']==1) {
			$required = array(
				'corpname',
				'zip',
				'prefecture',
				'address1',
				'division',
				'fullname',
				'email',
			);

			foreach($foreign as $foreign_key=>$foreign_data){
				if(in_array($foreign_key,$required) && $foreign_data==NULL){
					$data['message']['__all'] .= $this->lang->language[$foreign_key].'が入力されていません。<br />';
					$data['message'][$foreign_key] .= '入力して下さい。';
					log_message('notice', $data['message'][$foreign_key]);
					$result = FALSE;
				}
			}

		}
		if ($result === FALSE) {
			log_message('notice', $data['message']['__all']);
		}
	}

	function create_record(&$foreign)
	{
		$foreign['appno'] = 4;
		$foreign['seqno'] = 0;
		$appid = parent::create_record($foreign);
		if ($appid !== FALSE) {
			$foreign['appid'] = $appid;
		}
		return $appid;
	}

	//【詳細画面】
	public function detail($uid='')
	{
		if (uri_folder_string() == '/ex') {
			$this->load->model('exapply_model');
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
			// $exhid = $this->member->get_exhid();
			// $exhboothid = $this->member->get_exhboothid();
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

	public function search()
	{
		$keyword = $this->input->post('q');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
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

		// 出展者から見た場合は詳細を表示
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->get_exhid() != '') {
		if ($this->member_lib->get_exhid() != '') {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			redirect(uri_class_string() . '/detail');
		}

		$keyword = $this->input->get('q');
//		$this->db->start_cache();

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
		$this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno, e.corpname, e.brandname, s.spaceabbr');
		$this->db->select('v.parapet, v.corplogo, v.wallpaper, v.wallcolor, v.punchcarpet, v.punchcolor');
		$this->db->select('v.appid, v.created, v.updated');
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_04 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where_not_in('s.spaceabbr', array('B','F'));

		$query = $this->db->get();
		if ($query !== FALSE) {
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
			}
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
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
		$this->db->select("eb.exhboothno '小間番号', eb.exhid '出展者コード'");
		$this->db->select("e.corpname '出展者名', e.brandname '表示名', s.spaceabbr 'スペース'");
		$this->db->select("v.outsourcing '連絡窓口(出展者以外)'");
		$this->db->select("IF(v.outsourcing>0,v.corpname,'') '(施工)業者名'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.zip,'') '(施工)郵便番号'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.prefecture,'') '(施工)都道府県'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.address1,'') '(施工)住所1'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.address2,'') '(施工)住所2'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.division,'') '(施工)所属'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.position,'') '(施工)役職'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.fullname,'') '(施工)担当者'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.email,'') '(施工)メールアドレス'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.mobile,'') '(施工)携帯番号'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.phone,'') '(施工)TEL'", FALSE);
		$this->db->select("IF(v.outsourcing>0,v.fax,'') '(施工)FAX'", FALSE);
		$this->db->select("v.parapet 'パラペット', v.corplogo '社名ロゴ'");
		$this->db->select("v.wallpaper '壁面カラー', v.wallcolor '壁面カラー色'");
		$this->db->select("v.punchcarpet 'パンチカーペット', v.punchcolor 'パンチカーペット色'");
		$this->db->select("v.billid '請求先コード'");
		$this->db->select("bb.corpname '請求会社名', bb.zip '郵便番号', bb.countrycode '国', bb.prefecture '都道府県'");
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
		$this->db->join('v_exapply_04 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('exhibitor_bill bb', 'v.billid = bb.billid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where_not_in('s.spaceabbr', array('B','F'));
	}
}
// vim:ts=4
// End of file e04construction.php
