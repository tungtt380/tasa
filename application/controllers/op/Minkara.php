<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Minkara extends ExhOP_Controller {

	protected $form_appno    = 91;
	protected $form_prefix   = 'minkara';		// フォーム名
	protected $table_name    = 'v_exapply_91';  // テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'appid'        => 'trim',
		'exhboothid'   => 'trim',
	 	'appno'        => 'trim',
		'seqno'        => 'trim',
	);

	function __construct()
	{
		parent::__construct();
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
	}

	protected function setup_form_ex(&$data)
	{
        // Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// if ($this->member->get_exhid()) {
        //     $exhid = $this->member->get_exhid();
        if ($this->member_lib->get_exhid()) {
            $exhid = $this->member_lib->get_exhid();
        // Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		} else {
			$exhid = $data['foreign']['exhid'];
		}
		// 出展者情報の表示
		$this->load->model('exhibitors_model');
		$data['exhibitor'] = $this->exhibitors_model->read($exhid);
	}

    protected function get_record(&$data, $uid)
    {
        // 小間から出展者IDを取るようにする.
        parent::get_record($data, $uid);
    }

    function create_record(&$foreign)
    {
        $foreign['appno'] = $this->form_appno;
        $foreign['seqno'] = 0;

        if ($foreign['flag']==1) {
            return parent::update_record($foreign);
        } else {
            $appid = parent::create_record($foreign);
			$foreign['appid'] = $appid;
        }
    }

    function index()
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
        $this->db->select("e.corpname, e.corpkana, e.brandname");
		$this->db->select("ec.countrycode c_countrycode, ec.zip c_zip, ec.address1 c_address1, ec.address2 c_address2");
		$this->db->select("ec.fullname c_fullname, ec.phone c_phone, s.spaceabbr");
        $this->db->select("v.apply");
        $this->db->select("v.appid, v.created, v.updated");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('exhibitor_contact ec', 'e.exhid = ec.exhid');
        $this->db->join('v_exapply_91 v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

    function detail()
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

        $this->setup_form_ex($data);
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

    public function create($exhid, $boothid)
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

        $this->db->select('appid, token');
        $this->db->where('exhboothid', $data['foreign']['exhboothid']);
        $query = $this->db->get('v_exapply_91');
        if ($query->num_rows() > 0) {
            $appid = $query->row_array();
            $data['foreign']['appid']=$appid['appid'];
            $data['foreign']['token']=$appid['token'];
            $data['foreign']['flag']=1;
        } else {
            $data['foreign']['flag']=0;
        }
        $this->session->set_flashdata('foreign', $data['foreign']);

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
        $this->session->set_flashdata('foreign', $data['foreign']);

        $this->setup_form_ex($data);
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

    protected function after_regist(&$data)
    {
        // 更新日はデータベース日付なので、もう一度取り直す.
        $uid = $data['foreign'][$this->foreign_keyid];
        $this->get_record($data, $uid);
        $this->setup_form_ex($data);

        $this->load->library('email');
//		$mailto = array($data['foreign']['c_email']);
        if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
			$mailto = array('m_kanno@san-eishobo.co.jp');
            $mailfrom = 'info@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON';
        } else {
			$mailto = array('miko2u@gmail.com');
            $mailfrom = 'miko@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON(TEST MAIL)';
        }

        $text = $this->parser->parse('mail/minkara_confirm.txt', $data, TRUE);
        if (strpos($text, "\n") !== FALSE) {
            list($subject, $message) = explode("\n", $text, 2);
        } else {
            $subject = 'TOKYO AUTO SALON 2020(みんカラ)';
            $message = $text;
        }

        $this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
        $this->email->to($mailto);
        $this->email->bcc($mailfrom);
        $this->email->reply_to($mailfrom);
        $this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
        $this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
        $this->email->send();
    }
}
