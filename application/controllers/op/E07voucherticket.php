<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E07voucherticket extends ExhOP_Controller {

	protected $form_appno    = 7;
	protected $form_prefix   = 'e07voucherticket';	// フォーム名
	protected $table_name    = 'v_exapply_07';		// テーブル名
	protected $table_prefix  = FALSE;				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;				// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';  		 	// テーブルの主キー名
	protected $foreign_token = 'token';				// ２重更新・削除防止のための項目
	protected $foreign_value = array(				// 入力チェック用に使用するカラムとパターン
		'appid'       => 'trim',
		'exhboothid'  => 'trim',
		'appno'       => 'trim',
		'seqno'       => 'trim',
		'billid'      => 'trim|required',
		'quantity'    => 'trim',
		'addquantity' => 'trim|xss_clean|required|is_natural',
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
		if ($this->member_lib->get_exhid()) {
			$exhid = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
        } else {
			// Upgrade CI3 - Check empty data - Start by TTM
			if(empty($data['foreign']['exhid'])) $data['foreign']['exhid'] = NULL;
			// Upgrade CI3 - Check empty data - End by TTM
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
        // 小間から出展者IDを取るようにする.
        parent::get_record($data, $uid);

		// Upgrade CI3 - Check empty data - Start by TTM
		if(empty($data['foreign']['exhboothid'])) $data['foreign']['exhboothid'] = NULL;
		// Upgrade CI3 - Check empty data - End by TTM
        $this->db->where('exhboothid', $data['foreign']['exhboothid']);
        $this->db->where('expired', 0);
        $query = $this->db->get('exhibitor_booth');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['foreign']['exhid'] = $row['exhid'];
        }
    }

	function create_record(&$foreign)
	{
		$foreign['appno']=7;
		$foreign['seqno']=0;

		if($foreign['flag']==1) {
			return parent::update_record($foreign);
		} else {
			return parent::create_record($foreign);
		}
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function detail()
	function detail($uid = '')
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
    {
        $uri = $this->input->server('REQUEST_URI');
        if (uri_folder_string() == '/pa') {
            if (preg_match('/\/([0-9]+)\/?$/', $uri, $matches)) {
                $uid = $matches[1];
            }
        } else {
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
            if (preg_match('/e07voucherticket\/?$/', uri_redirect_string(), $matches)) {
                redirect(uri_redirect_string() . '/create/'.$exhid.'/'.$exhboothid, 'location', 302);
            } else {
                redirect(uri_redirect_string() . '/../create/'.$exhid.'/'.$exhboothid, 'location', 302);
            }
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

		// 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
		$this->db->select("eb.exhboothid, eb.exhid, eb.exhboothno, e.corpname, e.corpkana, e.brandname, s.spaceabbr");
		$this->db->select("v.quantity");
		$this->db->select("v.appid, v.created, v.updated");
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('v_exapply_07 v', 'v.exhboothid = eb.exhboothid', 'left');
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
		$data = $this->setup_data();
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

		if (!isset($data['foreign']['exhid']) || !isset($data['foreign']['exhboothid'])) {
			redirect(uri_redirect_string() . '/');
		}

		// 元の値は、quantに保持しておく
		$this->db->select('quantity');
		$this->db->where('exhboothid', $data['foreign']['exhboothid']);
		$this->db->from('v_exapply_07');
		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$record = $query->row_array();
			$data['foreign']['nowquantity'] = $record['quantity'];
			$this->session->set_flashdata('foreign', $data['foreign']);
		} else {
			$this->session->keep_flashdata('foreign');
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

		if(!isset($data['foreign']['nowquantity']) || $data['foreign']['nowquantity'] == 0) {
			$data['foreign']['quantity']=$data['foreign']['addquantity'];
			$data['foreign']['flag']=0;
			$this->session->set_flashdata('foreign', $data['foreign']);
		} else {
			$this->db->select('appid, token');
			$this->db->where('exhid',$data['foreign']['exhid']);
			$query = $this->db->get('v_exapply_07');
			if ($query->num_rows() > 0) {
				$appid = $query->row_array();
				$data['foreign']['appid']=$appid['appid'];
				$data['foreign']['token']=$appid['token'];
			}
			$data['foreign']['quantity']=$data['foreign']['nowquantity']+$data['foreign']['addquantity'];
			$data['foreign']['flag']=1;
			$this->session->set_flashdata('foreign', $data['foreign']);
		}

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
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
			$data['foreign']['nowquantity'] = $data['foreign']['quantity'];
			$data['foreign']['addquantity'] = 0;
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

		$data['foreign']['quantity']=$data['foreign']['nowquantity']+$data['foreign']['addquantity'];
		$this->session->set_flashdata('foreign', $data['foreign']);

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
		die('Prohibited');
	}

	function history($uid=FALSE)
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->session->keep_flashdata('foreign');

		// 申し込み者連絡先表示
		$exhboothid = $this->input->post('exhboothid');
		if ($exhboothid == FALSE && $uid != FALSE) {
			$exhboothid = intval($uid);
		}
		$this->db->select('eb.*, e.corpname, e.brandname, ec.fullname AS c_fullname');
		$this->db->where('eb.exhboothid', $exhboothid);
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
		$this->db->join('exhibitor_contact ec', 'ec.exhid = eb.exhid');
		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['exhibitor'] = $query->row_array();
		}

		$this->db->select('e06 AS quantity, e08 AS addquantity, updated');
		$this->db->where('exhboothid', $exhboothid);
		$this->db->where('appno', $this->form_appno);
		$query = $this->db->get('audit_exhibitor_apply');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function history_download($mode='')
	{
		$data = $this->setup_data();
        $this->setup_form($data);

		if (uri_folder_string() == '/ex') {
            die('Prohibited');
        }

		$head = '出展者コード,出展者名,出展名,申込日時,追加申込数,申込数合計,'
              . '(送付先)会社名,(送付先)所属,(送付先)役職,(送付先)担当者,'
              . '(送付先)〒,(送付先)国,(送付先)住所,,,(送付先)TEL,(送付先)FAX,'
              . '請求先コード,'
              . '(請求先)会社名,(請求先)所属,(請求先)役職,(請求先)担当者,'
              . '(請求先)〒,(請求先)国,(請求先)住所,,,(請求先)TEL,(請求先)FAX,';
		$head .= "\n";

		if ($mode == 'csv') {
            $datestr = date('YmdHi');
            $filename = strtolower(get_class($this)).'-history-'.$datestr.'.csv';
			$data = $this->history_download_build();
//die(var_export($data));
            $data = deco_csv_from_array($data);
            $data = $head . $data;
            $data = mb_convert_encoding($data,'SJIS-win','UTF-8');
            $this->load->helper('download');
            force_download($filename, $data);
//		} else if ($mode == 'xlsx') {
//			$data = $this->history_download_xlsx();
		}
	}

	protected function history_download_build()
	{
		$this->db->select('eb.exhid, e.corpname, e.brandname');
		$this->db->select('ea.updated, ea.e08 AS addquantity, ea.e06 AS quantity');
        $this->db->select("ed.corpname d_corpname, ed.corpkana d_corpkana");
		$this->db->select("ed.position d_position");
		$this->db->select("ed.fullname d_fullname");
        $this->db->select("ed.zip d_zip, ed.countrycode d_countrycode, ed.prefecture d_prefecture");
		$this->db->select("ed.address1 d_address1, ed.address2 d_address2");
		$this->db->select("ed.phone d_phone, ed.fax d_fax");
		$this->db->select("v.billid '請求先'");
        $this->db->select("bb.corpname b_corpname, bb.corpkana b_corpkana");
		$this->db->select("bb.position b_position");
		$this->db->select("bb.fullname b_fullname");
        $this->db->select("bb.zip b_zip, bb.countrycode b_countrycode, bb.prefecture b_prefecture");
		$this->db->select("bb.address1 b_address1, bb.address2 b_address2");
		$this->db->select("bb.phone b_phone, bb.fax b_fax");
		$this->db->where('ea.appno', $this->form_appno);
//		$this->db->where('eb.exhboothid', $exhboothid);
		$this->db->from('exhibitor_booth eb');
		$this->db->join('booths b', 'b.boothid = eb.boothid');
		$this->db->join('v_spaces s', 's.spaceid = b.spaceid');
		$this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_07 v', 'v.exhboothid = eb.exhboothid', 'left');
		$this->db->join('audit_exhibitor_apply ea', 'ea.exhboothid = eb.exhboothid');
        $this->db->join('exhibitor_dist ed', 'e.exhid = ed.exhid AND v.quantity IS NOT NULL', 'left');
        $this->db->join('exhibitor_bill bb', 'v.billid = bb.billid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));
		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data = $query->result_array();
		}
		return $data;
	}

    protected function download_build()
    {
        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select("eb.exhid, eb.exhboothno, e.corpname, e.brandname, s.spaceabbr");
        $this->db->select("v.quantity");
        $this->db->select("ed.corpname d_corpname, ed.corpkana d_corpkana");
        $this->db->select("ed.zip d_zip, ed.countrycode d_countrycode, ed.prefecture d_prefecture");
		$this->db->select("ed.address1 d_address1, ed.address2 d_address2");
		$this->db->select("ed.division d_division, ed.position d_position");
		$this->db->select("ed.fullname d_fullname, ed.fullkana d_fullkana");
		$this->db->select("ed.phone d_phone, ed.fax d_fax");
		$this->db->select("v.billid '請求先'");
        $this->db->select("bb.corpname b_corpname, bb.corpkana b_corpkana");
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
        $this->db->join('v_exapply_07 v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->join('exhibitor_dist ed', 'e.exhid = ed.exhid AND v.quantity IS NOT NULL', 'left');
        $this->db->join('exhibitor_bill bb', 'v.billid = bb.billid', 'left');
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

		// Upgrade CI3 - Check empty data - Start by TTM
		if(empty($data['exhibitor']['c_email'])) $data['exhibitor']['c_email'] = NULL;
		// Upgrade CI3 - Check empty data - End by TTM
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
