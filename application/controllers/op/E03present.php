<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E03present extends ExhOP_Controller {

	protected $form_appno    = 3;
	protected $form_prefix   = 'e03present';		// フォーム名
	protected $table_name    = 'v_exapply_03';	// テーブル名
	protected $table_prefix  = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';	// テーブルの主キー名
	protected $foreign_token = 'token';		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'appid'   => 'trim',
		'exhboothid'   => 'trim',
		'appno'   => 'trim',
		'seqno'   => 'trim',
		'corpname'   => 'trim|xss_clean|required',
		'zip'           => 'trim|xss_clean|valid_zip',
		'prefecture'   => 'trim|xss_clean',
		'address1'   => 'trim|xss_clean',
		'address2'   => 'trim|xss_clean',
		'phone'   => 'trim|xss_clean|valid_phone',
		'fax'   => 'trim|xss_clean|valid_phone',
	);

	function __construct()
	{
		parent::__construct();
		$this->load->model('e03present_model');
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
			// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
			// $exhid = $data['foreign']['exhid'];
			$exhid = empty($data['foreign']['exhid'])?NULL:$data['foreign']['exhid'];
			// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
        }
        // 出展者情報の表示
        $this->load->model('exhibitors_model');
        $data['exhibitor'] = $this->exhibitors_model->read($exhid);
    }

	protected function get_detail(&$data)
	{
		$this->db->where("exhboothid",$data['foreign']['exhboothid']);
		$query = $this->db->get('v_exapply_03_detail');
		if ($query->num_rows() > 0) {
			$lists = $query->result_array();
			foreach($lists as $lists_data){
				$itemname_name="itemname".$lists_data['seqno'];
				$itemprice_name="itemprice".$lists_data['seqno'];
				$quantity_name="quantity".$lists_data['seqno'];
				$itemphoto_name="itemphoto".$lists_data['seqno'];

				$data['foreign'][$itemname_name]=$lists_data['itemname'];
				$data['foreign'][$itemprice_name]=$lists_data['itemprice'];
				$data['foreign'][$quantity_name]=$lists_data['quantity'];
				$data['foreign'][$itemphoto_name]=$lists_data['itemphoto'];
			}
		}
	}

	// チェックロジック
	// 03は複数の商品を一気にアップする必要があるので、チェックその他は独自のものを使用する必要がある。
	protected function check_logic(&$data)
	{
	    $foreign = $data['foreign'];

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

		// validation rule
		$validation = array(		// 入力チェック用に使用するカラムとパターン
			'itemname'   => 'trim|xss_clean|required',
			'itemprice'  => 'trim|xss_clean|is_natural|required',
			'quantity'   => 'trim|xss_clean|is_natural|required',
		);

		for($i=1;$i<=6;$i++){
			if($foreign['itemname'.$i]==NULL && $foreign['itemprice'.$i]==NULL && $foreign['quantity'.$i]==NULL && $i!=1){
			// 項目全てが入力されていないものはスルー。但し、「1」に関しては項目全てが入力されていなくてもチェックを行う。
			}else{
				foreach($validation as $validation_key=>$validation_name){
					// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
					// $this->form_validation->set_rules($validation_key.$i,$this->lang->language[$validation_key].$i,$validation_name);
					$this->form_validation->set_rules($validation_key.$i,(empty($this->lang->language[$validation_key])?'':$this->lang->language[$validation_key]).$i,$validation_name);
					// Upgrade PHP7 - Fix “Undefined index” error - End by TTM
				}
			}
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

        log_message('notice', 'UPLOAD:'.var_export($_FILES,TRUE));
		$this->load->library('upload');

		for($i=1;$i<=6;$i++){
			if($_FILES["itemphoto".$i]['size']>0){
				$config = array();
				$config['file_name']=$foreign['exhid']."-".mb_convert_roma($_FILES["itemphoto".$i]['name']);
				$config['upload_path'] = APPPATH.'/photos/present/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']	= '1024';
				$this->upload->initialize($config);

				if(!$this->upload->do_upload("itemphoto".$i)){
					$data['message']['__all'] = $this->upload->display_errors();
					$result = FALSE;
				}
				$file_data = $this->upload->data();
                log_message('notice', 'UPLOAD:'.var_export($file_data,TRUE));
				$data['pic'][$i]=$file_data['file_name'];
				$data['foreign']['itemphoto'.$i]=$file_data['file_name'];
			// Upgrade PHP7 - Fix “Undefined index” error - Start by TTM
			// }else if($data['foreign']['itemphoto'.$i]){			
			// 	$data['pic'][$i]=$data['foreign']['itemphoto'.$i];
			// }
			}else if(empty($data['foreign']['itemphoto'.$i])){			
				$data['pic'][$i]=empty($data['foreign']['itemphoto'.$i])?NULL:$data['foreign']['itemphoto'.$i];
			}
			// Upgrade PHP7 - Fix “Undefined index” error - End by TTM
		}

		$this->session->set_flashdata('pic', $data['pic']);

		// 入力値はフィルタするため、実際のデータはここで格納する
		foreach($this->foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力不備の場合は、元の画面に戻る
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
		}

	}

	function create_record(&$foreign) {
		return $this->e03present_model->create($foreign);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function update_record($foreign) {
	function update_record($foreign = Array()) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		return $this->e03present_model->update($foreign);
	}

	function delete_record($foreign) {
		return $this->e03present_model->delete($foreign);
	}

	public function images($filename = '')
	{
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

		$this->load->helper('file');
		$path = APPPATH.'/photos/present/';

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
        $this->get_detail($data, $data['foreign']['exhboothid']);
        if (!isset($data['foreign'][$this->foreign_keyid])) {
            redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
        }

        $param = array(
            'itemname',
            'itemprice',
            'quantity',
			'itemphoto',
        );
        $param_count=count($param)-1;
        $a=1;
        foreach($data['foreign'] as $foreign_key=>$foreign_data){
            foreach($param as $param_key=>$param_data){
                if($foreign_key==$param_data.$a){
                    if($foreign_data){
                        $data['foreign2'][$a][$param_data]=$foreign_data;
                    }
                    if($param_key==$param_count){
                        $a++;
                    }
                    break;
                }
            }
        }

        $this->setup_form_ex($data);
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();

        if (uri_folder_string() == '/ex') {
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
		$this->db->join('v_exapply_03 v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		} else {
			$data['lists'] = array();
		}

		$before_exhboothid='';
		foreach($data['lists'] as $lists_key=>$lists_data){
			if($before_exhboothid==$lists_data['exhboothid']){
				unset($data['lists'][$lists_key]);
			}else{
				$this->db->select('itemname');
				$this->db->where("exhboothid",$lists_data['exhboothid']);

				$query = $this->db->get('v_exapply_03_detail');

				if ($query->num_rows() > 0) {
					$data['lists2'] = $query->result_array();
				}else{
					$data['lists2'] = array();
				}

				foreach($data['lists2'] as $lists2_key=>$lists2_data){
					$data['lists'][$lists_key]['itemnames'][$lists2_key]=$lists2_data['itemname'];
				}

				$before_exhboothid=$lists_data['exhboothid'];
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
		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
		}
	    $this->session->keep_flashdata('foreign');

        $this->setup_form_ex($data);
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function regist_confirm(){
		// 変数をforeach文に最適化されるように修正
		$data = $this->setup_data();

		$param = array(
			'itemname',
			'itemprice',
			'quantity',
		);
		$param_count=count($param)-1;

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['pic'] = $this->session->flashdata('pic');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');

		$a=1;
		foreach($data['foreign'] as $foreign_key=>$foreign_data){
			foreach($param as $param_key=>$param_data){
				if($foreign_key==$param_data.$a){
					if($foreign_data){
						$data['foreign2'][$a][$param_data]=$foreign_data;
					}
					if($param_key==$param_count){
						$a++;
					}
					break;
				}
			}
		}
		$this->session->set_flashdata('foreign2', $data['foreign2']);

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function regist_confirm_in()
	{
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

		// Upgrade PHP7 - Fix "Undefined variable error" - Start by TTM
		// return parent::regist_confirm_in($foreign);
		return parent::regist_confirm_in(NULL);
		// Upgrade PHP7 - Fix "Undefined variable error" - End by TTM
	}

	function registed()
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		$data['foreign'] = $this->session->flashdata('foreign');
		$data['post'] = $this->input->post();
		$data['pic'] = $this->session->flashdata('pic');
		$data['foreign2'] = $this->session->flashdata('foreign2');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

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
			$this->get_detail($data, $data['foreign']['exhboothid']);

			$this->session->set_flashdata('foreign', $data['foreign']);
		}

		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	function change_confirm(){
		// 変数をforeach文に最適化されるように修正
		$data = $this->setup_data();

		$param = array(
			'itemname',
			'itemprice',
			'quantity',
		);
		$param_count=count($param)-1;

		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['pic'] = $this->session->flashdata('pic');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');

		$a=1;
		foreach($data['foreign'] as $foreign_key=>$foreign_data){
			foreach($param as $param_key=>$param_data){
				if($foreign_key==$param_data.$a){
					if($foreign_data){
						$data['foreign2'][$a][$param_data]=$foreign_data;
					}
					if($param_key==$param_count){
						$a++;
					}
					break;
				}
/*
				if($param_key==$param_count){
					$a++;
				}
*/
			}
		}

		$this->session->set_flashdata('foreign2', $data['foreign2']);

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function change_confirm_in()
	{
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

		return parent::change_confirm_in($foreign);
	}

	function changed(){
		$data = $this->setup_data();
		$this->setup_form($data);

		$data['foreign'] = $this->session->flashdata('foreign');
		$data['post'] = $this->input->post();
		$data['pic'] = $this->session->flashdata('pic');
		$data['foreign2'] = $this->session->flashdata('foreign2');
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}

	function delete($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		$this->get_record($data, $uid);

		$this->db->where("exhid",$data['foreign']['exhid']);

		$query = $this->db->get('v_exapply_03_detail');
		if ($query->num_rows() > 0) {
			$lists = $query->result_array();
			foreach($lists as $lists_data){
				$data['foreign2'][$lists_data['seqno']]['itemname']=$lists_data['itemname'];
				$data['foreign2'][$lists_data['seqno']]['itemprice']=$lists_data['itemprice'];
				$data['foreign2'][$lists_data['seqno']]['quantity']=$lists_data['quantity'];
				$data['pic'][$lists_data['seqno']]=$lists_data['itemphoto'];
			}
		}

		$this->session->set_flashdata('foreign', $data['foreign']);
		$this->session->set_flashdata('foreign2', $data['foreign2']);
		$this->session->set_flashdata('pic', $data['pic']);

		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	public function delete_in()
	{
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');

		return parent::delete_in($foreign);
	}

	public function after_regist(&$data)
	{
		$this->after_notify($data, 'regist');
	}
	public function after_change(&$data)
	{
		$this->after_notify($data, 'change');
	}
	public function after_delete(&$data)
	{
		$this->after_notify($data, 'delete');
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
            $mailfrom = 'info@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON';
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
        $this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
        $this->db->select("e.corpname, e.corpkana, e.brandname, e.brandkana, s.spaceabbr");
        $this->db->select("v.corpname '協賛企業名', v.zip '郵便番号', v.prefecture '都道府県', v.address1 '住所1', v.address2 '住所2'");
        $this->db->select("v.phone 'TEL', v.fax 'FAX'");
        $this->db->select("vd.itemname, vd.itemprice, vd.quantity, vd.itemphoto");
        $this->db->select("v.appid '手続番号', v.created , v.updated '更新日'");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_03 v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->join('v_exapply_03_detail vd', 'vd.exhboothid = v.exhboothid', 'left');
        $this->db->where('vd.expired', '0');
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

        $this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno");
        $this->db->select("e.corpname, e.corpkana, e.brandname, e.brandkana, s.spaceabbr");
        $this->db->select("vd.itemname, vd.itemprice, vd.quantity, vd.itemphoto");
        $this->db->select("v.appid, v.created, v.updated");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_03 v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->join('v_exapply_03_detail vd', 'vd.exhboothid = v.exhboothid', 'left');
        $this->db->where('vd.expired', '0');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));
        $query = $this->db->get();
        if ($query === FALSE) {
            show_error('ダウンロードできません.');
        }
        foreach ($query->result_array() as $row) {
			if (isset($row['itemphoto']) && $row['itemphoto'] != '') {
				$this->zip->read_file(APPPATH.'/photos/present/'.$row['itemphoto']);
			}
		}
		$this->zip->download('e03present_photo.zip');
	}
}

