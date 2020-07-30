<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E02publicinfo extends ExhOP_Controller {

	protected $form_appno	 = 2;
	protected $form_prefix	 = 'e02publicinfo';	// フォーム名
	protected $table_name	 = 'v_exapply_02';	// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'appid'   => 'trim',
		'exhboothid'   => 'trim',
		'appno'   => 'trim',
		'seqno'   => 'trim',
        'brandnameen'       => 'trim|xss_clean|required',   // 2015 added
		'zip'			=> 'trim|xss_clean|valid_zip',
		'prefecture'   => 'trim|xss_clean',
		'address1'	 => 'trim|xss_clean',
		'address2'	 => 'trim|xss_clean',
		'phone'   => 'trim|xss_clean|valid_phone',
		'fax'	=> 'trim|xss_clean|valid_phone',
		'email'			=> 'trim|xss_clean|valid_email',
		'url'		  => 'trim|xss_clean',
		'prcomment'   => 'trim|xss_clean',
		'publicaddress'   => 'trim|required',
		'publicphone'	=> 'trim|required',
		'publicfax'   => 'trim|required',
		'publicurl'   => 'trim|required',
		'publicemail'	=> 'trim|required',
	);

	function __construct()
	{
		parent::__construct();
		$this->load->model('exhibitors_model');
		$this->load->model('e02publicinfo_model');
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
		// 	$exhid = $this->member->get_exhid();
		if ($this->member_lib->get_exhid()) {
			$exhid = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		} else {
			// Upgrade CI3 - Fix "Undefined index" - Start by TTM
			// $exhid = $data['foreign']['exhid'];
			$exhid = empty($data['foreign']['exhid'])?NULL:$data['foreign']['exhid'];
			// Upgrade CI3 - Fix "Undefined index" - End by TTM
		}
		// 出展者情報の表示
		$this->load->model('exhibitors_model');
		$data['exhibitor'] = $this->exhibitors_model->read($exhid);
	}

	protected function get_record(&$data, $uid)
	{
		// 小間から出展者IDを取るようにする.
		parent::get_record($data, $uid);

		// Upgrade CI3 - Fix "Undefined index" - Start by TTM
		// $this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$this->db->where('exhboothid', empty($data['foreign']['exhboothid'])?NULL:$data['foreign']['exhboothid']);
		// Upgrade CI3 - Fix "Undefined index" - End by TTM
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_booth');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$data['foreign']['exhid'] = $row['exhid'];
		}
	}

	protected function check_logic(&$data)
	{
		$foreign = $data['foreign'];
		$result = TRUE;

		// 無理パッチ
		if (!isset($foreign['exhid']) || $foreign['exhid'] == '') {
			$this->db->where('exhboothid', $data['foreign']['exhboothid']);
			$this->db->where('expired', 0);
			$query = $this->db->get('exhibitor_booth');
			if ($query->num_rows() > 0) {
				$row = $query->row_array();
				$data['foreign']['exhid'] = $row['exhid'];
				$foreign['exhid'] = $row['exhid'];
			} else {
				die();
			}
		}

		// URLチェック
		if (isset($foreign['url']) && !preg_match('/^([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $foreign['url'])){
			$data['message']['url'] = "<p>URLを正しく入力して下さい。</p>";
			$result = FALSE;
		}

		// 文字数チェック
		if(mb_strlen($foreign['prcomment'])>100){
			$data['message']['prcomment'] = "<p>PRコメントは100文字以内で入力して下さい。</p>";
			$result = FALSE;
		}

		// 公開する場合の項目チェック
		if($foreign['publicaddress']==1 && ($foreign['zip']==NULL)){
			$data['message']['zip'] = "<p>郵便番号に不備があります。</p>";
			$result = FALSE;
		}
//		if($foreign['publicaddress']==1 && ($foreign['prefecture']==NULL)){
//			$data['message']['prefecture'] = "<p>都道府県に不備があります。</p>";
//			$result = FALSE;
//		}
		if($foreign['publicaddress']==1 && ($foreign['address1']==NULL)){
			$data['message']['address1'] = "<p>住所に不備があります。</p>";
			$result = FALSE;
		}
		if($foreign['publicphone']==1 && $foreign['phone']==NULL){
			$data['message']['phone'] = "<p>TELが入力されていません。</p>";
			$result = FALSE;
		}
		if($foreign['publicfax']==1 && $foreign['fax']==NULL){
			$data['message']['fax'] = "<p>FAXが入力されていません。</p>";
			$result = FALSE;
		}
		if($foreign['publicurl']==1 && $foreign['url']==NULL){
			$data['message']['url'] = "<p>URLが入力されていません。</p>";
			$result = FALSE;
		}
		if($foreign['publicemail']==1 && $foreign['email']==NULL){
			$data['message']['email'] = "<p>メールアドレスが入力されていません。</p>";
			$result = FALSE;
		}
		if ($result === FALSE) {
			$str = '';
			foreach($data['message'] as $key=>$val) {
				$str .= $val . "\n";
			}
			$data['message']['__all'] = $str;
			return FALSE;
		}

		// 画像アップロード処理とエラー処理
		// Upgrade CI3 - Fix "Undefined variable" - Start by TTM
		// $this->load->library('upload', $config);
		$this->load->library('upload');
		// Upgrade CI3 - Fix "Undefined variable" - Start by TTM
		if($_FILES["photo"]['size']>0){
			$config = array();
			$config['upload_path'] = APPPATH . '/photos/publicinfo/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_width']  = '1600';
			$config['max_height']  = '1200';
			$config['file_name'] = $foreign['exhid']."-".mb_convert_roma($_FILES["photo"]['name']);
			$this->upload->initialize($config);

			if(!$this->upload->do_upload("photo")) {
				$data['message']['__all'] = $this->upload->display_errors();
				$result = FALSE;
			}

			$file_data = $this->upload->data();
			$data['pic']=$file_data['file_name'];
		// Upgrade CI3 - Fix "Undefined index" - Start by TTM
		// }else if($data['foreign']['photo']){
		// 	$data['pic']=$data['foreign']['photo'];
		// }

		// $this->session->set_flashdata('pic', $data['pic']);
		}else if(!empty($data['foreign']['photo'])){
			$data['pic']=$data['foreign']['photo'];
			
		}
		if($_FILES["photo"]['size']>0) $this->session->set_flashdata('pic', $data['pic']);
		// Upgrade CI3 - Fix "Undefined index" - End by TTM

		if ($result === FALSE) {
			log_message('notice', $data['message']['__all']);
		}
	}

	function create_record(&$foreign) {
		return $this->e02publicinfo_model->create($foreign);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function update_record($foreign) {
	function update_record($foreign = Array()) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		return $this->e02publicinfo_model->update($foreign);
	}

	function delete_record($foreign) {
		return $this->e02publicinfo_model->delete($foreign);
	}

	public function images($filename = '')
	{
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

		$this->load->helper('file');
		$path = APPPATH.'/photos/publicinfo/';

		// TODO: filename パラメーターは XSS フィルタリングを必ずすること
		$file = $path . basename($filename);

		// TODO: ファイルの拡張子などは、アップロード時にチェックしている前提
		header('Content-Type: ' . get_mime_by_extension($filename));
		readfile($file);
		exit;
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function detail()
	function detail($uid = '')
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$this->load->model('exapply_model');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $exhid = $this->member->get_exhid();
		// $exhboothid = $this->member->get_exhboothid();
		$exhid = $this->member_lib->get_exhid();
		$exhboothid = $this->member_lib->get_exhboothid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$uid = $this->exapply_model->get_appid($exhboothid, $this->form_appno);

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
		}

		$this->db->where("exhboothid", $data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_02_detail');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$detail = $query->row_array();
			$data['foreign']['photo'] = $detail['photo'];
			$data['pic'] = $detail['photo'];
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->setup_form_ex($data, $this->member->get_exhid());
		$this->setup_form_ex($data, $this->member_lib->get_exhid());
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
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
		$this->db->select("e.corpname, e.corpkana, e.brandname, e.brandkana, s.spaceabbr");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_02 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE) { 
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
			}
		}

		if (isset($data['lists'])) {
			$before_exhboothid="";
			foreach($data['lists'] as $lists_key=>$lists_data){
				if($before_exhboothid==$lists_data['exhboothid']){
					unset($data['lists'][$lists_key]);
				}else{
					$this->db->select('photo');
					$this->db->where('exhboothid',$lists_data['exhboothid']);
	
					$query = $this->db->get('v_exapply_02_detail');
					if ($query->num_rows() > 0) {
						$data['lists2'] = $query->result_array();
					}else{
						$data['lists2'] = array();
					}
					if(!empty($data['lists2'][0]['photo'])){
						$data['lists'][$lists_key]['photo']=$data['lists2'][0]['photo'];
					}
					$before_exhboothid=$lists_data['exhboothid'];
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

	public function regist()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');
		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
		}

		// 出展者情報出力
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function regist_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');

		$data['pic'] = $this->session->flashdata('pic');
		$data['foreign']['photo'] = $data['pic'];

		$this->session->set_flashdata('foreign', $data['foreign']);
		$this->session->keep_flashdata('pic');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function regist_confirm_in()
	{
		$this->session->keep_flashdata('pic');
		return parent::regist_confirm_in();
	}

	function registed()
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		$data['foreign'] = $this->session->flashdata('foreign');
		$data['pic'] = $this->session->flashdata('pic');

		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function change($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['message'] = $this->session->flashdata('message');
		$data['foreign'] = $this->session->flashdata('foreign');

		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->keep_flashdata('foreign');
		} else {
			$this->get_record($data, $uid);

			$this->db->where("exhboothid",$data['foreign']['exhboothid']);
			$query = $this->db->get('v_exapply_02_detail');
			if ($query->num_rows() > 0) {
				$lists = $query->result_array();
				$data['foreign']['photo']=$lists[0]['photo'];
			}
			$this->session->set_flashdata('foreign', $data['foreign']);
		}

		$this->setup_form_ex($data);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata', $data);
		} else {
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	function change_confirm()
	{
		// 変数をforeach文に最適化されるように修正
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');

		$data['pic'] = $this->session->flashdata('pic');
		$data['foreign']['photo']=$data['pic'];
		$this->session->keep_flashdata('pic');
		$this->session->set_flashdata('foreign', $data['foreign']);

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function change_confirm_in()
	{
		$this->session->keep_flashdata('pic');
		return parent::change_confirm_in();
	}

	function changed()
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		$data['foreign'] = $this->session->flashdata('foreign');
		$data['post'] = $this->input->post();
		$data['pic'] = $this->session->flashdata('pic');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}

	function delete($uid='')
	{
		// データ呼び出し
		$data = $this->setup_data();
		$this->setup_form($data);

		$this->get_record($data, $uid);

		$this->db->where("exhboothid",$data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_02_detail');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$lists = $query->row_array();
			$data['pic']=$lists['photo'];
		} else {
			$data['pic']='';
		}
		$this->session->set_flashdata('foreign', $data['foreign']);
		$this->session->set_flashdata('pic', $data['pic']);
	
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
		$this->db->select("eb.exhboothno, eb.exhid");
		$this->db->select("e.corpname, e.brandname, v.brandnameen, s.spaceabbr");
		$this->db->select("v.publicaddress, v.zip, v.prefecture");
		$this->db->select("v.address1, v.address2");
		$this->db->select("v.publicphone, v.phone, v.publicfax, v.fax");
		$this->db->select("v.publicemail, v.email, v.publicurl, v.url");
		$this->db->select("v.prcomment");
		$this->db->select("vd.photo");
		$this->db->select('v.created, v.updated');
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_02 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_02_detail vd', 'vd.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
	}

	function archive()
	{
		if (uri_folder_string() == '/ex') {
			echo $this->parser->parse('prohibited');
			exit;
		}

		$this->load->library('zip');
		ini_set('memory_limit', '1024M');

		$this->download_build();
		$query = $this->db->get();
		if ($query === FALSE) {
			show_error('ダウンロードできません.');
		}

		foreach ($query->result_array() as $row) {
			if ($row['photo'] != '') {
				$this->zip->read_file(APPPATH.'/photos/publicinfo/'.$row['photo']);
			}
		}
		$this->zip->download('e02publicinfo_photo.zip');
	}
}
// vim:ts=4
