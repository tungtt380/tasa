<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E09floor extends ExhOP_Controller {

	protected $form_appno    = 9;
	protected $form_prefix   = 'e09floor';		// フォーム名
	protected $table_name    = 'v_exapply_09';	// テーブル名
	protected $table_prefix  = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';	// テーブルの主キー名
	protected $foreign_token = 'token';		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'appid'         => 'trim',
		'exhboothid'    => 'trim',
		'appno'         => 'trim',
		'seqno'         => 'trim',
		'outsourcing'   => 'trim|required',
		'corpname'      => 'trim|xss_clean',
		'zip'           => 'trim|xss_clean|valid_zip',
		'prefecture'    => 'trim|xss_clean',
		'address1'      => 'trim|xss_clean',
		'address2'      => 'trim|xss_clean',
		'division'      => 'trim|xss_clean',
		'fullname'      => 'trim|xss_clean',
		'email'         => 'trim|xss_clean|valid_email',
		'mobile'        => 'trim|xss_clean|valid_phone',
		'phone'         => 'trim|xss_clean|valid_phone',
		'fax'           => 'trim|xss_clean|valid_phone',
		'ceiling'       => 'trim|required',
		'ceilingsize'   => 'trim|xss_clean',
		'ceilingarea'   => 'trim|xss_clean',
		'ceilingreason' => 'trim|xss_clean',
		'floor'         => 'trim|required',
		'floorsize'     => 'trim|xss_clean',
		'floorarea'     => 'trim|xss_clean',
		'purpose2f'     => 'trim',
		'reason2f'      => 'trim|xss_clean',
		'purpose1f'     => 'trim',
		'reason1f'      => 'trim|xss_clean',
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
			// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
			// $exhid = $data['foreign']['exhid'];
			// $exhboothid = $data['foreign']['exhboothid'];
			$exhid = empty($data['foreign']['exhid'])?NULL:$data['foreign']['exhid'];
			$exhboothid = empty($data['foreign']['exhboothid'])?NULL:$data['foreign']['exhboothid'];
			// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
		}
		// 出展者情報の表示
		$this->load->model('exhibitors_model');
		$data['exhibitor'] = $this->exhibitors_model->read($exhid);

		// 施工業者登録データ表示
		$this->db->where('exhboothid', $exhboothid);
		$query = $this->db->get('v_exapply_04');
		if ($query->num_rows() > 0) {
			$data['constructor'] = $query->row_array();
		} else {
			$data['constructor'] = array();
		}
	}

	protected function get_record(&$data, $uid)
	{
		// 小間から出展者IDを取るようにする.
		parent::get_record($data, $uid);

		// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
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
		$foreign['appno'] = $this->form_appno;
		$foreign['seqno'] = 0;
		return parent::create_record($foreign);
	}

	// 天井工事を行う場合のエラーチェック
	protected function check_logic(&$data)
	{
		$foreign = $data['foreign'];
		$result = TRUE;

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

			foreach($foreign as $foreign_key => $foreign_data) {
				if( in_array($foreign_key,$required) && $foreign_data==NULL) {
					$data['message']['__all'] .= $this->lang->language[$foreign_key].'が入力されていません。<br />';
					$data['message'][$foreign_key] .= '入力して下さい。';
					log_message('notice', $data['message'][$foreign_key]);
					$result = FALSE;
				}
			}
		}

		// Upgrade PHP7 - Fix “Undefined variable error - Start by TTM
		$validation = Array();
		// Upgrade PHP7 - Fix “Undefined variable error - End by TTM
		if ($result !== FALSE) {
			// 天井工事を行う
			if($foreign['ceiling']==1){
				$validation['ceilingsize']= 'trim|required|is_natural';
				$validation['ceilingarea']= 'trim|required|is_natural';
				$validation['ceilingreason']= 'trim|required';
			}

			// 二階工事を行う
			if($foreign['floor']==1){
				$validation['floorsize']= 'trim|required|is_natural';
				$validation['floorarea']= 'trim|required|is_natural';
				$validation['purpose2f']= 'trim|required';
				$validation['purpose1f']= 'trim|required';
			}

			foreach($validation as $validation_key=>$validation_name){
				$this->form_validation->set_rules($validation_key,$this->lang->language[$validation_key].$i,$validation_name);
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
	}

	function detail($uid='')
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

		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->setup_form_ex($data, $this->member->get_exhid());
		$this->setup_form_ex($data, $this->member_lib->get_exhid());
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
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
		$this->db->select("IFNULL(v4.corpname,v.corpname) v_corpname, IF(v.outsourcing>0,'','*') v_default", FALSE);
		$this->db->select("v.ceiling,v.floor");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_09 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_04 v4', 'v4.exhboothid = eb.exhboothid', 'left');
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE) { 
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
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
		$data['lists'] = $this->session->flashdata('lists');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('lists');

		// データ整理
		$foreign2 = $data['foreign'];

		// 業者名・担当者名・電話番号・FAX番号の設定
		// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
		// if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==0){
		if((empty($foreign2['outsourcing']) || $foreign2['outsourcing']==0) && (empty($data['constructor']['outsourcing']) || $data['constructor']['outsourcing']==0)){
		// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者の場合は出展者
			// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
			// $foreign2['corpname']=$data['contact']['corpname'];
			// $foreign2['fullname']=$data['contact']['fullname'];
			// $foreign2['phone']=$data['contact']['phone'];
			// $foreign2['fax']=$data['contact']['fax'];
			$foreign2['corpname']=empty($data['contact']['corpname'])?'':$data['contact']['corpname'];
			$foreign2['fullname']=empty($data['contact']['fullname'])?'':$data['contact']['fullname'];
			$foreign2['phone']=empty($data['contact']['phone'])?'':$data['contact']['phone'];
			$foreign2['fax']=empty($data['contact']['fax'])?'':$data['contact']['fax'];
			// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
		}else if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==1){
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者以外の場合は施工業者
			$foreign2['corpname']=$data['constructor']['corpname'];
			$foreign2['fullname']=$data['constructor']['fullname'];
			$foreign2['phone']=$data['constructor']['phone'];
			$foreign2['fax']=$data['constructor']['fax'];
		}else{
			// 工事業者を記載した場合
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
			$flg=1;
		}

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

		// データ整理
		$foreign2 = $data['foreign'];
		unset($foreign2['billid']);

		// 業者名・担当者名・電話番号・FAX番号の設定
		if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==0){
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者の場合は出展者
			$foreign2['corpname']=$data['exhibitor']['corpname'];
			$foreign2['fullname']=$data['exhibitor']['fullname'];
			$foreign2['phone']=$data['exhibitor']['phone'];
			$foreign2['fax']=$data['exhibitor']['fax'];
		}else if($foreign2['outsourcing']==0 && $data['constructor']['outsourcing']==1){
			// 「上記登録の施工業者」かつ「ブース装飾」が出展者以外の場合は施工業者
			$foreign2['corpname']=$data['constructor']['corpname'];
			$foreign2['fullname']=$data['constructor']['fullname'];
			$foreign2['phone']=$data['constructor']['phone'];
			$foreign2['fax']=$data['constructor']['fax'];
		}else{
			// 工事業者を記載した場合
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

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function download_build()
	{
		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothno '小間番号', eb.exhid '出展者コード'");
		$this->db->select("e.corpname '出展者名', e.brandname '表示名', s.spaceabbr 'スペース'");
		$this->db->select("v.outsourcing '２階建業者登録'");
//		$this->db->select("IF(v.outsourcing>0,v.corpname,'') '(２階建)工事業者名'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.corpkana,'') '(２階建)工事業者名カナ'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.zip,'') '(２階建)郵便番号'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.prefecture,'') '(２階建)都道府県'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.address1,'') '(２階建)住所1'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.address2,'') '(２階建)住所2'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.division,'') '(２階建)所属'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.position,'') '(２階建)役職'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.fullname,'') '(２階建)担当者'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.email,'') '(２階建)メールアドレス'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.mobile,'') '(２階建)携帯番号'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.phone,'') '(２階建)TEL'", FALSE);
//		$this->db->select("IF(v.outsourcing>0,v.fax,'') '(２階建)FAX'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.corpname,IF(v.outsourcing=0,v4.corpname,'')) '(２階建)工事業者名'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.corpkana,IF(v.outsourcing=0,v4.corpkana,'')) '(２階建)工事業者名カナ'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.zip,IF(v.outsourcing=0,v4.zip,'')) '(２階建)郵便番号'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.prefecture,IF(v.outsourcing=0,IF(v4.corpname is null,'',v4.prefecture),'')) '(２階建)都道府県'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.address1,IF(v.outsourcing=0,v4.address1,'')) '(２階建)住所1'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.address2,IF(v.outsourcing=0,v4.address2,'')) '(２階建)住所2'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.division,IF(v.outsourcing=0,v4.division,'')) '(２階建)所属'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.position,IF(v.outsourcing=0,v4.position,'')) '(２階建)役職'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.fullname,IF(v.outsourcing=0,v4.fullname,'')) '(２階建)担当者'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.email,IF(v.outsourcing=0,v4.email,'')) '(２階建)メールアドレス'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.mobile,IF(v.outsourcing=0,v4.mobile,'')) '(２階建)携帯番号'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.phone,IF(v.outsourcing=0,v4.phone,'')) '(２階建)TEL'", FALSE);
        $this->db->select("IF(v.outsourcing>0,v.fax,IF(v.outsourcing=0,v4.fax,'')) '(２階建)FAX'", FALSE);
		$this->db->select("v.ceiling '天井工事'");
		$this->db->select("v.ceilingsize '天井面積'");
		$this->db->select("v.ceilingarea '小間面積'");
		$this->db->select("v.ceilingreason '理由'");
		$this->db->select("v.floor '２階建工事'");
		$this->db->select("v.floorsize '２階床面積', v.floorarea '小間面積'");
		$this->db->select("v.purpose2f '２階使用目的', v.reason2f '２階その他'");
		$this->db->select("v.purpose1f '１階使用目的', v.reason1f '１階その他'");
		$this->db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$this->db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$this->db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$this->db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");
		$this->db->select("v.created '登録日時', v.updated '更新日時'");

//      $this->db->select("eb.exhid, eb.exhboothno, e.corpname, e.brandname");
//      $this->db->select("v.outsourcing, v.corpname v_corpname, v.corpkana");
//      $this->db->select("v.zip, v.prefecture, v.address1, v.address2, v.division, v.position");
//      $this->db->select("v.fullname, v.fullkana, v.fax, v.mobile");
//      $this->db->select("v.ceiling, v.ceilingsize, v.ceilingarea, v.ceilingreason");
//      $this->db->select("v.floor, v.floorsize, v.floorarea");
//      $this->db->select("v.purpose2f, v.reason2f, v.purpose1f, v.reason1f");
//      $this->db->select("bb.seqno, bb.corpname, bb.corpkana, bb.zip, bb.prefecture, bb.address1, bb.address2");
//      $this->db->select("bb.division, bb.position, bb.fullname, bb.fullkana, bb.phone, bb.fax");
//      $this->db->select("v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('v_exapply_09 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('v_exapply_04 v4', 'v4.exhboothid = eb.exhboothid', 'left');
//      $this->db->join('exhibitor_bill bb', 'bb.billid = v.billid', 'left');
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
}

// vim:ts=4
