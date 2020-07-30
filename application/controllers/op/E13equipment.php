<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E13equipment extends ExhOP_Controller {

	protected $form_appno	 = 13;
	protected $form_prefix	 = 'e13equipment';	// フォーム名
	protected $table_name	 = 'v_exapply_13';	// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'appid'   => 'trim',
		'exhboothid'   => 'trim',
		'appno'   => 'trim',
		'seqno'   => 'trim',
		'billid'   => 'trim|required',
	);
	protected $unitcode = array(				// unitcodeとquantityの関係
		"GR-615"	=>"quantity_1",
		"BC-38-A"	=>"quantity_2",
		"BC-38-B"	=>"quantity_3",
		"BC-38-C"	=>"quantity_4",
		"BC-38-D"	=>"quantity_5",
		"A4"		=>"quantity_6",
		"B4"		=>"quantity_7",
	);

	function __construct()
	{
		parent::__construct();
		$this->load->model('e13equipment_model');
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
			// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
			// $exhid = $data['foreign']['exhboothid'];
			// $exhid = $data['foreign']['exhid'];
			$exhid = empty($data['foreign']['exhboothid'])?NULL:$data['foreign']['exhboothid'];
			$exhid = empty($data['foreign']['exhid'])?NULL:$data['foreign']['exhid'];
			// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
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

		// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
		// $this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$this->db->where('exhboothid', empty($data['foreign']['exhboothid'])?NULL:$data['foreign']['exhboothid']);
		// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
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
		$foreign['appno']=13;
		$foreign['seqno']=0;
		return $this->e13equipment_model->create($foreign);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function update_record($foreign)
	function update_record($foreign = Array())
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		return $this->e13equipment_model->update($foreign);
	}

	function delete_record($foreign) {
		return $this->e13equipment_model->delete($foreign);
	}

	// チェックロジック
	// 13は全項目空欄の場合及び数値以外のものが混じっていた場合エラーとする。
	protected function check_logic(&$data)
	{
		$foreign = $data['foreign'];

		// validation rule
		$validation = array(	// 入力チェック用に使用するカラムとパターン
			'quantity_1'   => 'trim|xss_clean|is_natural',
			'quantity_2'   => 'trim|xss_clean|is_natural',
			'quantity_3'   => 'trim|xss_clean|is_natural',
			'quantity_4'   => 'trim|xss_clean|is_natural',
			'quantity_5'   => 'trim|xss_clean|is_natural',
			'quantity_6'   => 'trim|xss_clean|is_natural',
			'quantity_7'   => 'trim|xss_clean|is_natural',
		);

		if ($foreign['quantity_1']==NULL &&
			$foreign['quantity_2']==NULL &&
			$foreign['quantity_3']==NULL &&
			$foreign['quantity_4']==NULL &&
			$foreign['quantity_5']==NULL &&
			$foreign['quantity_6']==NULL &&
			$foreign['quantity_7']==NULL) {
			$data['message']['__all'] = "<p>数量を入力して下さい。</p>";
		}

		if($foreign['quantity_1'] && ($foreign['quantity_2'] || $foreign['quantity_3'] || $foreign['quantity_5'])){
			$data['message']['__all'] = "<p>インフォメーションカウンターとセットでご使用する場合は「Cタイプ」をお選びください。</p>";
		}

		foreach($validation as $validation_key=>$validation_name) {
			// Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
			// $this->form_validation->set_rules($validation_key, $this->lang->language[$validation_key].$i,$validation_name);
			$this->form_validation->set_rules($validation_key, $this->lang->language[$validation_key],$validation_name);
			// Upgrade PHP7 - Fix bug "Undefined variable" - End by TTM			
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
		// 出展者から見た場合はdetailを表示
		if (uri_folder_string() == '/ex') {
			$this->load->model('exapply_model');
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
			// $exhid = $this->member->get_exhid();
			// $exhboothid = $this->member->get_exhboothid();
			$exhid = $this->member_lib->get_exhid();
			$exhboothid = $this->member_lib->get_exhboothid();
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
			$uid = $this->exapply_model->get_appid($exhboothid, $this->form_appno);
			$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
            $this->db->from('exhibitor_booth eb');
            $this->db->join('booths b', 'b.boothid = eb.boothid');
            $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
            $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
            $this->db->join('v_exapply_13 v', 'v.exhboothid = eb.exhboothid', 'left');
            $this->db->join('v_exapply_13_detail vd', 'vd.exhboothid = eb.exhboothid', 'left');
            $this->db->join('exhibitor_bill bb', 'bb.billid = v.billid', 'left');
            $this->db->where('eb.expired', '0');
            $this->db->where('e.expired', '0');
            $this->db->where('eb.exhid', $exhid);
            $this->db->where_in('e.statusno', array('500','401','400'));
            $this->db->where_in('s.spaceabbr', array('B','E','F'));

            $query = $this->db->get();
            if ($query === FALSE || $query->num_rows() === 0) {
                redirect(uri_redirect_string() . '/denied');
            }			
		}

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
		}

		$this->db->where('exhboothid',$data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_13_detail');
		if ($query->num_rows() > 0) {
			$detail = $query->result_array();
		}
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

		if (uri_folder_string() =='/ex') {
			// 出展者から見た場合は詳細を表示
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
			// if ($this->member->get_exhid() != '') {
			if ($this->member_lib->get_exhid() != '') {
			// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
				redirect(uri_class_string() . '/detail');
			}
			exit;
		}

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
		$this->db->select("e.corpname, e.brandname, s.spaceabbr");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_13 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where_in('s.spaceabbr', array('B','E','F'));

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->db->select('exhboothid, quantity, unitcode');
		$query = $this->db->get('v_exapply_13_detail');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$detail_lists = $query->result_array();

			// 詳細レコードから抜き出して一覧に詰めていく
			$before_exhboothid = '';
			foreach($detail_lists as $detail_data) {
				foreach($data['lists'] as $lists_key => $lists_data){
//					if ($before_exhboothid == $lists_data['exhboothid']) {
//						unset($data['lists'][$lists_key]);
//					} else {
						if ($lists_data['exhboothid'] == $detail_data['exhboothid']) {
							$unitcode = str_replace('-', '_', $detail_data['unitcode']);
							$data['lists'][$lists_key][$unitcode] = $detail_data['quantity'];
						}
						$before_exhboothid = $lists_data['exhboothid'];
						break;
//					}
				}
			}
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	
	function denied()
	{
		$data = $this->setup_data();
		$this->parser->parse('e13equipment_denied', $data);
	}


	// 請求先の取得
	function regist()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
		}
		$this->session->keep_flashdata('foreign');
/*
		$this->db->where('exhid', $data['foreign']['exhid']);
		$this->db->order_by('seqno', 'asc');
		$query = $this->db->get('exhibitor_bill');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
*/
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

	function regist_confirm(){
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->db->where('billid',$data['foreign']['billid']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
			$this->session->set_flashdata('lists', $data['lists']);
		}

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}


	function registed(){
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->db->where('billid',$data['foreign']['billid']);

		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

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

			$this->db->where('exhboothid',$data['foreign']['exhboothid']);
			$query = $this->db->get('v_exapply_13_detail');
			if ($query->num_rows() > 0) {
				$detail = $query->result_array();
			}
			foreach($detail as $detail_data){
				$data['foreign'][$this->unitcode[$detail_data['unitcode']]]=$detail_data['quantity'];
			}
		}
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

		$this->db->where('exhboothid',$data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_13_detail');
		if ($query->num_rows() > 0) {
			$detail = $query->result_array();
		}
		foreach($detail as $detail_data){
			$data['foreign'][$this->unitcode[$detail_data['unitcode']]]=$detail_data['quantity'];
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	
	function deleted()
	{
		$data = $this->setup_data();

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
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

		// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
		// $mailto = array($data['exhibitor']['c_email']);
		$mailto = empty($data['exhibitor']['c_email'])?Array():array($data['exhibitor']['c_email']);
		// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
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
		$this->db->join('v_exapply_13 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_13_detail vd', 'vd.exhboothid = eb.exhboothid', 'left');
		$this->db->join('exhibitor_bill bb', 'bb.billid = v.billid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where_in('s.spaceabbr', array('B','E','F'));
	}
}
// vim:ts=4
// End of file 
