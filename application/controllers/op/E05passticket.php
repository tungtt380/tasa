<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E05passticket extends ExhOP_Controller {

	protected $form_appno	 = 5;
	protected $form_prefix	 = 'e05passticket';	// フォーム名
	protected $table_name	 = 'v_exapply_05';	// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'appid'   => 'trim',
		'exhboothid'   => 'trim',
		'appno'   => 'trim',
		'seqno'   => 'trim',
		'billid'  => 'trim',
	);

	function __construct() {
		parent::__construct();
		$this->load->model('e05passticket_model');
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
	}

	protected function setup_form_ex(&$data)
	{
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->get_exhid()) {
		// 	$exhid = $this->member->get_exhid();
		if ($this->member_lib->get_exhid()) {
			$exhid = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		} else {
			$exhid = $data['foreign']['exhid'];
		}
        // 出展者情報の表示
        $this->load->model('exhibitors_model');
        $data['exhibitor'] = $this->exhibitors_model->read($exhid);
        // 請求先情報の表示
        $this->load->model('billing_model');
        $data['lists'] = $this->billing_model->readExhibitors($exhid);
	}

	function get_record(&$data, $uid)
	{
		parent::get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			return;
		}

		$this->db->select('vd.*');
		$this->db->select('eb.exhid');
		$this->db->from('v_exapply_05_detail vd');
		$this->db->join('exhibitor_booth eb', 'eb.exhboothid = vd.exhboothid');
		$this->db->where('vd.exhboothid', $data['foreign']['exhboothid']);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$lists = $query->result_array();
			foreach($lists as $record) {
				$data['foreign']['quantity_'.$record['itemcode']]=$record['quantity'];
				$data['foreign']['addquantity_'.$record['itemcode']]=$record['addquantity'];
			}
			$data['foreign']['exhid'] = $record['exhid'];
		}
	}

	function create_record(&$foreign)
	{
		if ($foreign['quantity_1'] &&
			$foreign['quantity_2'] &&
			$foreign['quantity_3'] &&
			$foreign['quantity_4'] &&
			$foreign['quantity_5']) {
			return $this->e05passticket_model->update($foreign);
		} else {
			return $this->e05passticket_model->create($foreign);
		}
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function update_record($foreign) {
	function update_record($foreign = Array()) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		return $this->e05passticket_model->update($foreign);
	}

	function delete_record($foreign) {
		return $this->e05passticket_model->delete($foreign);
	}

	// チェックロジック
	// 05は全項目空欄の場合及び数値以外のものが混じっていた場合エラーとする。
	protected function check_logic(&$data)
	{
		$foreign = $data['foreign'];

		// validation rule
		if($foreign['exhboothid']){
			$validation = array(		// 入力チェック用に使用するカラムとパターン
				'addquantity_1'   => 'trim|xss_clean|integer',
				'addquantity_2'   => 'trim|xss_clean|integer',
				'addquantity_3'   => 'trim|xss_clean|integer',
				'addquantity_4'   => 'trim|xss_clean|integer',
				'addquantity_5'   => 'trim|xss_clean|integer',
			);

			foreach($foreign as $foreign_key=>$foreign_data){
				if(strstr($foreign_key,'addquantity')){
					if($foreign_data!=NULL){
						$addq_array=explode("_",$foreign_key);

						if($foreign['quantity_'.$addq_array[1]]+$foreign_data<0){
							$data['message']['__all'] = "<p>これまでの追加枚数より多く減らそうとしています。</p>";
							$data['message'][$foreign_key] = "<p>これまでの追加枚数より多く減らそうとしています。</p>";
						}
					}
				}
			}

		}else{
			$validation = array(	// 入力チェック用に使用するカラムとパターン
				'addquantity_1'   => 'trim|xss_clean|is_natural',
				'addquantity_2'   => 'trim|xss_clean|is_natural',
				'addquantity_3'   => 'trim|xss_clean|is_natural',
				'addquantity_4'   => 'trim|xss_clean|is_natural',
				'addquantity_5'   => 'trim|xss_clean|is_natural',
			);
		}

		if ($foreign['addquantity_1']==NULL &&
			$foreign['addquantity_2']==NULL &&
			$foreign['addquantity_3']==NULL &&
			$foreign['addquantity_4']==NULL &&
			$foreign['addquantity_5']==NULL) {
			$data['message']['__all'] = "<p>追加枚数を入力して下さい。</p>";
		}

		foreach($validation as $validation_key=>$validation_name){
			// Upgrade CI3 - Remove undefined variable - Start by TTM
			// $this->form_validation->set_rules($validation_key,$this->lang->language[$validation_key].$i,$validation_name);
			$this->form_validation->set_rules($validation_key,$this->lang->language[$validation_key],$validation_name);
			// Upgrade CI3 - Remove undefined variable - End by TTM
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

		// 検索条件がある場合
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
					$this->db->or_collate_like('s.spaceabbr', $keyword);
					$this->db->or_collate_like('ec.email', $keyword);
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
				$this->db->or_collate_like('s.spaceabbr', $keyword);
				$this->db->or_collate_like('ec.email', $keyword);
				// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
				// $this->db->grouplike_end();
				$this->db->group_end();
				// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
			}
		} else {
			$data['q'] = '';
		}

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
		$this->db->select("e.corpname, e.corpkana, e.brandname, ec.email, s.spaceabbr");
		$this->db->select("v.appid, v.sentdate, v.sent, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_contact ec', 'e.exhid = ec.exhid');
		$this->db->join('v_exapply_05 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$lists = $query->result_array();
			$data['lists'] = array();
			foreach($lists as $record) {
				$key = 'x' . $record['exhboothid'];
				$data['lists'][$key] = $record;
			}
		} else {
			$data['lists'] = array();
		}

		// チケットの枚数を拾う
		$tickets = $this->e05passticket_model->read_detail();
		foreach($tickets as $record) {
			$key = 'x'.$record['exhboothid'];
			if (isset($data['lists'][$key])) {
				if (!isset($data['lists'][$key]['itemnames'])) {
					$data['lists'][$key]['itemnames'] = array();
					$data['lists'][$key]['quantities'] = array();
					$data['lists'][$key]['ordered'] = '';
				}
				$data['lists'][$key]['itemnames'][]  = $record['itemname'];
				$data['lists'][$key]['quantities'][] = $record['quantity'];
				if ($data['lists'][$key]['ordered'] < $record['updated']) {
					$data['lists'][$key]['ordered'] = $record['updated'];
				}
			}
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function sendlist()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$this->setup_form($data);

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
		$this->db->select("e.corpname, e.corpkana, e.brandname, b.boothname, s.spaceabbr, b.boothcount");
		$this->db->select("ed.corpname d_corpname, ed.corpkana d_corpkana");
		$this->db->select("ed.zip d_zip, ed.countrycode d_countrycode, ed.prefecture d_prefecture");
		$this->db->select("ed.address1 d_address1, ed.address2 d_address2");
		$this->db->select("ed.division d_division, ed.position d_position");
		$this->db->select("ed.fullname d_fullname, ed.fullkana d_fullkana");
		$this->db->select("ed.phone d_phone, ed.fax d_fax");
		$this->db->select("e.corpname, e.corpkana, e.brandname, s.spaceabbr");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_dist ed', 'e.exhid = ed.exhid');
		$this->db->join('v_exapply_05 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$lists = $query->result_array();
			$data['lists'] = array();
			foreach($lists as $record) {
				$key = 'x' . $record['exhboothid'];
				$data['lists'][$key] = $record;
				// デフォルトのチケット数を定義
				$boothspace = $record['spaceabbr'];
				$boothcount = $record['boothcount'];
				if ($boothspace == 'A' || $boothspace == 'C' || $boothspace == 'NAPAC-C') {
					$data['lists'][$key]['default1'] = ($boothcount > 3) ? 12: 7;
					$data['lists'][$key]['default2'] = ($boothcount > 3) ? 10: 5;
					$data['lists'][$key]['default3'] = ($boothcount > 3) ? 12: 7;
					$data['lists'][$key]['default4'] = (($boothcount > 3) ? 10:10) * $boothcount;
					$data['lists'][$key]['default5'] = (($boothcount > 3) ? 10:10) * $boothcount;
				} else
				if ($boothspace == 'E' || $boothspace == 'NAPAC-E' || $boothspace == 'JASMA-E') {
					$data['lists'][$key]['default1'] =	5 * $boothcount;
					$data['lists'][$key]['default2'] =	3 * $boothcount;
					$data['lists'][$key]['default3'] =	5 * $boothcount;
					$data['lists'][$key]['default4'] = 10 * $boothcount;
					$data['lists'][$key]['default5'] = 10 * $boothcount;
				} else
				if ($boothspace == 'B' || $boothspace == 'F') {
					$data['lists'][$key]['default1'] =	5 * $boothcount;
					$data['lists'][$key]['default2'] =	2 * $boothcount;
					$data['lists'][$key]['default3'] =	5 * $boothcount;
					$data['lists'][$key]['default4'] = 10 * $boothcount;
					$data['lists'][$key]['default5'] = 10 * $boothcount;
				} else
				if ($boothspace == 'D' || $boothspace == 'NAPAC-D' || $boothspace == 'RH9-D') {
					$data['lists'][$key]['default1'] = ($boothcount > 9) ? 35:25;
					$data['lists'][$key]['default2'] = ($boothcount > 9) ? 20:15;
					$data['lists'][$key]['default3'] = ($boothcount > 9) ? 35:25;
					$data['lists'][$key]['default4'] = (($boothcount > 9) ? 10:10) * $boothcount;
					$data['lists'][$key]['default5'] = (($boothcount > 9) ? 10:10) * $boothcount;
				} else
				if ($boothspace{0} == 'S') {
					$boothcount = intval(substr($boothspace,1))/10;
					$record['boothcount'] = $boothcount;
					$data['lists'][$key]['boothcount'] = $boothcount;
					$data['lists'][$key]['default1'] = ($boothcount >= 45) ? 100:60;
					$data['lists'][$key]['default2'] = 50;
					$data['lists'][$key]['default3'] = ($boothcount >= 45) ? 100:60;
					$data['lists'][$key]['default4'] = 10 * $boothcount;
					$data['lists'][$key]['default5'] = 10 * $boothcount;
				}
			}
		} else {
			$data['lists'] = array();
		}

		// チケットの枚数を拾う
		$tickets = $this->e05passticket_model->read_detail();
		foreach($tickets as $record) {
			$key = 'x'.$record['exhboothid'];
			if (isset($data['lists'][$key])) {
//$itemarray = array( 0, 3, 1, 2, 4, 5);
//$itemcode = $itemarray[$record['itemcode']];
				$itemcode = $record['itemcode'];
				$data['lists'][$key]['quantity'.$itemcode] = $record['quantity'];
			}
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【詳細画面】
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function detail()
	public function detail($uid = '')
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		// 出展者から見た場合は詳細を表示
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $exhid = $this->member->get_exhid();
		// $exhboothid = $this->member->get_exhboothid();
		$exhid = $this->member_lib->get_exhid();
		$exhboothid = $this->member_lib->get_exhboothid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM

		$this->load->model('exapply_model');
		$uid = $this->exapply_model->get_appid($exhboothid, $this->form_appno);

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);

		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
		}

		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->setup_form_ex($data, $this->member->get_exhid());
		$this->setup_form_ex($data, $this->member_lib->get_exhid());
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	
	function close()
	{
		$data = $this->setup_data();
		$this->parser->parse('e05passticket_close', $data);
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

	public function regist()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');

		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
			exit;
		}

		$this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_05_detail');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$lists = $query->result_array();
			foreach($lists as $lists_data){
				$quant_name="quantity_".$lists_data['itemcode'];
				$addq_name="addquantity_".$lists_data['itemcode'];
				$data['foreign'][$quant_name]=$lists_data['quantity'];
			}
		}
		$this->session->set_flashdata('foreign', $data['foreign']);
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function change($uid='')
	{
		// データ呼び出し
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['message'] = $this->session->flashdata('message');
		if (isset($data['message']) && !empty($data['message'])) {
			$data['foreign'] = $this->session->flashdata('foreign');
			$this->session->keep_flashdata('foreign');
		} else {
			$this->get_record($data, $uid);
			unset($data['foreign']['addquantity_1']);
			unset($data['foreign']['addquantity_2']);
			unset($data['foreign']['addquantity_3']);
			unset($data['foreign']['addquantity_4']);
			unset($data['foreign']['addquantity_5']);
			$this->session->set_flashdata('foreign', $data['foreign']);
		}
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			$this->setup_form_ex($data);
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	public function change_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
/*
		$this->db->where("exhboothid",$data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_05_detail');
		if ($query->num_rows() > 0) {
			$lists = $query->result_array();
			foreach($lists as $lists_data){
				$quant_name = 'quantity_' . $lists_data['itemcode'];

				$data['foreign'][$quant_name] = $lists_data['quantity'];
			}
		}
		$this->session->set_flashdata('foreign', $data['foreign']);
*/
		$this->session->keep_flashdata('foreign');
//		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function delete($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);

		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix . '_nodata');
		} else {
			$this->session->set_flashdata('foreign', $data['foreign']);
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	// Upgrade PHP7 - Silence “Too few arguments to function” error in PHP 7 - Start by TTM
	// function history($exhid, $exhboothid)
	function history($exhid = null, $exhboothid = null)
	// Upgrade PHP7 - Silence “Too few arguments to function” error in PHP 7 - Start by TTM
	{
		$data = $this->setup_data();
		$this->session->keep_flashdata('foreign');

		// 申し込み者連絡先表示
		$this->db->where("exhid", $exhid);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$data['contact'] = $query->row_array();
		}

		$this->db->select("e02 AS itemname, e06 AS quantity, e08 AS addquantity, e29 AS sent, updated");
		$this->db->where("exhboothid", $exhboothid);
		$this->db->where("appno", 5);
		$this->db->where("seqno != ", 0);

		$query = $this->db->get('audit_exhibitor_apply');
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
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
			if ($data['rolename'] != 'exhibitor' && $data['rolename'] != 'promotion') {
				$mailto = array($mailfrom);
			}
		} else {
			$bcc = $this->config->item(strtolower(substr(__CLASS__,0,3)));
			$mailfrom = 'miko@tokyoautosalon.jp';
			$namefrom = 'TOKYO AUTO SALON(TEST MAIL)';
			if ($data['rolename'] != 'exhibitor' && $data['rolename'] != 'promotion') {
				$mailto = array($mailfrom);
			}
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
		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhid, eb.exhboothno, s.spaceabbr,");
		$this->db->select("e.corpname, e.brandname, ec.email");
		$this->db->select("vd.itemname, vd.quantity, vd.created, vd.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_contact ec', 'e.exhid = ec.exhid');
		$this->db->join('v_exapply_05 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_05_detail vd', 'vd.exhboothid = v.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
	}

	public function sendlist_download($mode='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		if (uri_folder_string() == '/ex') {
			die('Prohibited');
		}
		$head = '手続番号,出展者コード,コマ番号,出展者名,出展者名カナ,表示名,小間,スペース,小間数,'
			  . '(送付先)会社名,(送付先)所属,(送付先)役職,(送付先)担当者,'
			  . '(送付先)国,(送付先)〒,(送付先)住所,,,(送付先)TEL,(送付先)FAX,更新日時,'
			  . 'ポスター,告知チラシ,駐車券,'
			  . '出展者パス(既定),搬入出リボン(既定),搬入車両証(既定),特別招待券(既定),一般招待券(既定),'
			  . '出展者パス(追加),搬入出リボン(追加),搬入車両証(追加),特別招待券(追加),一般招待券(追加),'
			  . '出展者パス(合計),搬入出リボン(合計),搬入車両証(合計),特別招待券(合計),一般招待券(合計),'
			  . "\n";

		if ($mode == 'csv') {
			$datestr = date('YmdHi');
			$filename = strtolower(get_class($this)).'-sendlist-'.$datestr.'.csv';
			$data = $this->sendlist_download_build();
			$data = deco_csv_from_array($data);
			$data = $head . $data;
			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
			$this->load->helper('download');
			force_download($filename, $data);
//		} else if ($mode == 'xlsx') {
//			$data = $this->download_xlsx();
		}
	}
	protected function sendlist_download_csv()
	{
		$this->load->dbutil();

		$this->sendlist_download_build();
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		return deco_csv_from_result($query);
	}
/*
	protected function sendlist_download_xlsx()
	{
		$this->load->dbutil();

		$this->sendlist_download_build();
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		return $this->download_xls_from_result($query, strtolower(get_class($this)).'-sendlist');
	}
*/
	protected function sendlist_download_build()
	{
		ini_set('memory_limit', '512M');

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, v.appid, eb.exhid, eb.exhboothno");
		$this->db->select("e.corpname, e.corpkana, e.brandname, b.boothname, s.spaceabbr, b.boothcount");
		$this->db->select("ed.corpname d_corpname");
		$this->db->select("ed.division d_division, ed.position d_position");
		$this->db->select("ed.fullname d_fullname");
		$this->db->select("ed.countrycode d_countrycode, ed.zip d_zip, ed.prefecture d_prefecture");
		$this->db->select("ed.address1 d_address1, ed.address2 d_address2");
		$this->db->select("ed.phone d_phone, ed.fax d_fax");
		$this->db->select("v.updated");
		$this->db->select("b.boothcount*5 AS 'ポスター', b.boothcount*20 AS '告知チラシ', 1 AS '駐車券'", FALSE);
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_dist ed', 'e.exhid = ed.exhid');
		$this->db->join('v_exapply_05 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$lists = $query->result_array();
			$data['lists'] = array();
			foreach($lists as $record) {
				$key = 'x' . $record['exhboothid'];
unset($record['exhboothid']);
				$data['lists'][$key] = $record;
				// デフォルトのチケット数を定義
				$boothspace = $record['spaceabbr'];
				$boothcount = $record['boothcount'];
				if ($boothspace == 'A' || $boothspace == 'C' || $boothspace == 'NAPAC-C') {
					$data['lists'][$key]['default3'] = ($boothcount > 3) ? 12: 7;
					$data['lists'][$key]['default1'] = ($boothcount > 3) ? 12: 7;
					$data['lists'][$key]['default2'] = ($boothcount > 3) ? 10: 5;
					$data['lists'][$key]['default4'] = (($boothcount > 3) ? 10:10) * $boothcount;
					$data['lists'][$key]['default5'] = (($boothcount > 3) ? 10:10) * $boothcount;
				} else
				if ($boothspace == 'E' || substr($boothspace,0,7) == 'NAPAC-E' || $boothspace == 'JASMA-E') {
					$data['lists'][$key]['default3'] =	5 * $boothcount;
					$data['lists'][$key]['default1'] =	5 * $boothcount;
					$data['lists'][$key]['default2'] =	3 * $boothcount;
					$data['lists'][$key]['default4'] = 10 * $boothcount;
					$data['lists'][$key]['default5'] = 10 * $boothcount;
				} else
				if ($boothspace == 'B' || $boothspace == 'F') {
					$data['lists'][$key]['default3'] =	5 * $boothcount;
					$data['lists'][$key]['default1'] =	5 * $boothcount;
					$data['lists'][$key]['default2'] =	2 * $boothcount;
					$data['lists'][$key]['default4'] = 10 * $boothcount;
					$data['lists'][$key]['default5'] = 10 * $boothcount;
				} else
				if ($boothspace == 'D' || $boothspace == 'NAPAC-D' || $boothspace == 'RH9-D') {
					$data['lists'][$key]['default3'] = ($boothcount > 9) ? 35:25;
					$data['lists'][$key]['default1'] = ($boothcount > 9) ? 35:25;
					$data['lists'][$key]['default2'] = ($boothcount > 9) ? 20:15;
					$data['lists'][$key]['default4'] = (($boothcount > 9) ? 10:10) * $boothcount;
					$data['lists'][$key]['default5'] = (($boothcount > 9) ? 10:10) * $boothcount;
				} else
				if ($boothspace{0} == 'S') {
					$boothcount = intval(substr($boothspace,1))/10;
					$record['boothcount'] = $boothcount;
//					$data['lists'][$key]['boothcount'] = $boothcount;
					$data['lists'][$key]['default3'] = ($boothcount >= 45) ? 100:60;
					$data['lists'][$key]['default1'] = ($boothcount >= 45) ? 100:60;
					$data['lists'][$key]['default2'] = 50;
					$data['lists'][$key]['default4'] = 10 * $boothcount;
					$data['lists'][$key]['default5'] = 10 * $boothcount;
					$data['lists'][$key]['ポスター'] = 5 * $boothcount;
					$data['lists'][$key]['告知チラシ'] = 20 * $boothcount;
				}
if ( ! isset($data['lists'][$key]['default3'])) {
	log_message('error', $boothspace . $key);
}
				$data['lists'][$key]['quantity3'] = '';
				$data['lists'][$key]['quantity1'] = '';
				$data['lists'][$key]['quantity2'] = '';
				$data['lists'][$key]['quantity4'] = '';
				$data['lists'][$key]['quantity5'] = '';
				$data['lists'][$key]['total3'] = $data['lists'][$key]['default3'];
				$data['lists'][$key]['total1'] = $data['lists'][$key]['default1'];
				$data['lists'][$key]['total2'] = $data['lists'][$key]['default2'];
				$data['lists'][$key]['total4'] = $data['lists'][$key]['default4'];
				$data['lists'][$key]['total5'] = $data['lists'][$key]['default5'];
			}
		} else {
			$data['lists'] = array();
		}

		// チケットの枚数を拾う
		$tickets = $this->e05passticket_model->read_detail();
		foreach($tickets as $record) {
			$key = 'x'.$record['exhboothid'];
			if (isset($data['lists'][$key])) {
				$itemcode = $record['itemcode'];
				$data['lists'][$key]['quantity'.$itemcode] = $record['quantity'];
				$data['lists'][$key]['total'.$itemcode] = $data['lists'][$key]['default'.$itemcode] + $record['quantity'];
			}
		}

		return $data['lists'];
	}

	//【詳細画面】
	public function sent($uid)
	{
		// 出展者から見た場合は詳細を表示
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function sent_in()
	{
		$this->check_action();
		$data = $this->setup_data();
		$data['foreign'] = $this->input->post();

		// 発送日を更新する
		$this->e05passticket_model->sentupdate($data['foreign']);

		// 一覧画面にリダイレクト
		redirect(uri_class_string());
	}
}

// vim:ts=4
