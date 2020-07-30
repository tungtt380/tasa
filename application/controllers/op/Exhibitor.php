<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exhibitor extends MemOP_Controller {

	protected $form_prefix	 = 'exhibitor';		// フォーム名
	protected $table_name	 = 'exhibitors';	// テーブル名
	protected $table_prefix  = 'S';				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'exhid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'corpname'		=> 'trim|required|xss_clean',
		'corpkana'		=> 'trim|required|xss_clean|prep_kana|valid_kana',
		'countrycode'	=> 'trim|xss_clean',
		'zip'			=> 'trim|xss_clean|valid_zip',
		'prefecture'	=> 'trim|xss_clean',
		'address1'		=> 'trim|xss_clean',
		'address2'		=> 'trim|xss_clean',
		'phone'			=> 'trim|xss_clean|valid_phone',
		'fax'			=> 'trim|xss_clean|valid_phone',
		'url'			=> 'trim|xss_clean|valid_hostname',
		'position'		=> 'trim|xss_clean',
		'fullname'		=> 'trim|xss_clean',
		'fullkana'		=> 'trim|xss_clean|prep_kana|valid_kana',
		//
		'brandname'		=> 'trim|required',
		'brandkana'		=> 'trim|required|xss_clean|prep_kana|valid_kana',
		//
		'm_corpname'	=> 'trim|xss_clean',
		'm_corpkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'm_countrycode' => 'trim|xss_clean',
		'm_zip'			=> 'trim|xss_clean|valid_zip',
		'm_prefecture'	=> 'trim|xss_clean',
		'm_address1'	=> 'trim|xss_clean',
		'm_address2'	=> 'trim|xss_clean',
		'm_division'	=> 'trim|xss_clean',
		'm_position'	=> 'trim|xss_clean',
		'm_fullname'	=> 'trim|xss_clean|',
		'm_fullkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'm_phone'		=> 'trim|xss_clean|valid_phone',
		'm_fax'			=> 'trim|xss_clean|valid_phone',
		'm_mobile'		=> 'trim|xss_clean|valid_phone',
		'm_email'		=> 'trim|xss_clean|valid_email',
		//
		'b_corpname'	=> 'trim|xss_clean',
		'b_corpkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'b_countrycode' => 'trim|xss_clean',
		'b_zip'			=> 'trim|xss_clean|valid_zip',
		'b_prefecture'	=> 'trim|xss_clean',
		'b_address1'	=> 'trim|xss_clean',
		'b_address2'	=> 'trim|xss_clean',
		'b_phone'		=> 'trim|xss_clean|valid_phone',
		'b_fax'			=> 'trim|xss_clean|valid_phone',
		'b_division'	=> 'trim|xss_clean',
		'b_position'	=> 'trim|xss_clean',
		'b_fullname'	=> 'trim|xss_clean',
		'b_fullkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		//
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
		//
		'd_corpname'	=> 'trim|xss_clean',
		'd_corpkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'd_countrycode' => 'trim|xss_clean',
		'd_zip'			=> 'trim|xss_clean|valid_zip',
		'd_prefecture'	=> 'trim|xss_clean',
		'd_address1'	=> 'trim|xss_clean',
		'd_address2'	=> 'trim|xss_clean',
		'd_division'	=> 'trim|xss_clean',
		'd_position'	=> 'trim|xss_clean',
		'd_fullname'	=> 'trim|xss_clean',
		'd_fullkana'	=> 'trim|xss_clean|prep_kana|valid_kana',
		'd_phone'		=> 'trim|xss_clean|valid_phone',
		'd_fax'			=> 'trim|xss_clean|valid_phone',
		//
		'q_entrycars'	=> 'trim|xss_clean|is_natural',
		'q_boothcount1' => 'trim|xss_clean|is_natural',
		'q_boothcount2' => 'trim|xss_clean|is_natural',
		'q_boothcount3' => 'trim|xss_clean|is_natural',
        'q_boothcount4' => 'trim|xss_clean|is_natural',
        'remark'		=> 'trim|xss_clean',
		//
		'promotion'		=> 'trim|xss_clean|alpha_dash',
		'comment'		=> 'trim|xss_clean',
		'statusno'		=> 'trim|xss_clean|is_natural_no_zero',
		'accepted'		=> 'trim|xss_clean|valid_isodatetime',
	);
	protected $foreign_query = array('scantext');		// 全文検索用で使用するカラム
	protected $pp = 100;

	function __construct() {
		parent::__construct();
		$this->load->model('exhibitors_model');
	}

	public function search()
	{
		$keyword = $this->input->post('q');
		$status = $this->input->post('s');
		$spaces = $this->input->post('sp');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
		}
		if ($status != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 's=' . rawurlencode($status);
		}
		if ($spaces != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'sp=' . rawurlencode($spaces);
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

		$this->load->model('booth_model');
		$data['booth'] = $this->booth_model->get_abbrlist();

		$keyword = $this->input->get('q');
		$status = $this->input->get('s');
		$spaces = $this->input->get('sp');
		$this->db->start_cache();
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$marches = '';
			if (preg_match('/^([^:]+):([^:]+)$/', $keyword, $matches)) {
				switch($matches[1]) {
				case 'promotion':
					$this->db->like('promotion', $matches[2]);
					break;
				default:
					$this->db->collate_like('scantext', $keyword);
					break;
				}
			} else {
				$this->db->collate_like('scantext', $keyword);
			}
		} else {
			$data['q'] = '';
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($status !== FALSE) {
		if ($status !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			switch($status) {
			case 100:
				$this->db->where_in('statusno', array(100,200,201,202));
				break;
			case 300:
				$this->db->where_in('statusno', array(300,302));
				break;
			default:
				$this->db->where('statusno', $status);
				break;
			}
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($spaces !== FALSE) {
		if ($spaces !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['sp'] = $spaces;
			if ($spaces == 'S' ) {
				$this->db->like('scanbooth', $spaces);
			} else {
				$this->db->like('scanbooth', $spaces.'@');
			}
		} else {
			$data['sp'] = '';
		}

		$this->db->stop_cache();
		$this->db->select('exhid, corpname, corpkana, fullname, fullkana, brandname, brandkana');
		$this->db->select('promotion, remark, comment, statusno, route, accepted, receipted, created, updated, deleted');
		$this->db->from('v_exhibitors_search');
		$data['count'] = $this->db->count_all_results();

		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring == '' ? '?':'&') . 'q=' . urlencode($keyword);
		} else {
			$querystring .= ($querystring == '' ? '?':'&') . 'q=';
		}
		if ($status != '') {
			$querystring .= ($querystring == '' ? '?':'&') . 's=' . urlencode($status);
		}
		if ($spaces != '') {
			$querystring .= ($querystring == '' ? '?':'&') . 'sp=' . urlencode($spaces);
		}

		$query = $this->db->get('v_exhibitors_search');
		$this->db->flush_cache();
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}

		$this->load->model('status_model');
		$data['status'] = $this->status_model->get_dropdown();
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('country_model');
		$data['countrycode'] = $this->country_model->get_dropdown();
		$this->load->model('prefecture_model');
		$data['prefecture'] = $this->prefecture_model->get_dropdown(TRUE);
		$this->load->model('category_model');
		$data['category'] = $this->category_model->get_dropdown();
		$this->load->model('section_model');
		$data['section'] = $this->section_model->get_dropdown();
		$this->load->model('booth_model');
		$data['booth'] = $this->booth_model->get_dropdown(FALSE,TRUE);
		$data['boothgroup'] = $this->booth_model->get_dropdown(TRUE,TRUE);
		$this->load->model('status_model');
		$data['status'] = $this->status_model->get_dropdown();
	}

	protected function check_logic(&$data)
	{
		$this->load->model('booth_model');
		$foreign = $data['foreign'];
		$result = TRUE;
        $spacecounts = array('A'=>0,'B'=>0,'C'=>0,'D'=> 0,'E'=>0,'F'=>0,'S'=>0, 'S300'=> 0,'S350'=> 0,'S400'=> 0,'S450'=> 0,'S500'=> 0,'S600'=> 0,'S700'=> 0,'S720'=> 0,'S750'=> 0,'S800'=> 0,'S850'=> 0,'S900'=> 0,'S1000'=> 0,'S1050'=> 0,'S1200'=> 0);
//        $spacelimits = array('A'=>5,'B'=>2,'C'=>5,'D'=>30,'E'=>2,'F'=>2,'S'=>1, 'S300'=> 1,'S350'=> 1,'S400'=> 1,'S450'=> 1,'S500'=> 1,'S600'=> 1,'S700'=> 1,'S720'=> 1,'S750'=> 1,'S800'=> 1,'S850'=> 1,'S900'=> 1,'S1000'=> 1,'S1050'=> 1,'S1200'=> 1);
        // 201808 本番関k表に合わせた
        $spacelimits = array('A'=>5,'B'=>4,'C'=>5,'D'=>30,'E'=>3,'F'=>2,'S'=>1, 'S300'=> 1,'S350'=> 1,'S400'=> 1,'S450'=> 1,'S500'=> 1,'S600'=> 1,'S700'=> 1,'S720'=> 1,'S750'=> 1,'S800'=> 1,'S850'=> 1,'S900'=> 1,'S1000'=> 1,'S1050'=> 1,'S1200'=> 1);
		$othercount = 0;
		$contcount = 0;
		$waitcount = 0;

		for ($i=1;$i<=9;$i++) {
			if (isset($foreign['q_booth'.$i]) && $foreign['q_booth'.$i] != '') {
				$row = $this->booth_model->get_boothspace($data['foreign']['q_booth'.$i]);
				if (($row['spaceabbr'] != 'A' && $spacecounts['A'] > 0) ||
					($row['spaceabbr'] == 'A' && $othercount > 0)) {
					$data['message']['__all'] = 'Aスペースは他のスペースと同時に申込はできません。';
					$result = FALSE;
					break;
				}
				$letter = substr($row['spaceabbr'],0,1);
				if (isset($spacelimits[$letter]) && $spacelimits[$letter] > 0) {
					$row['spaceabbr'] = $letter;
					$spacecounts[$row['spaceabbr']] += $row['boothcount'];
					if ($spacecounts[$row['spaceabbr']] > $spacelimits[$row['spaceabbr']]) {
						$data['message']['__all'] = '小間の申込上限数を超えています。';
						$result = FALSE;
						break;
					}
					$othercount += ($row['spaceabbr'] == 'A')? 0:$row['boothcount'];
				}
			}
		}
		if ($result === FALSE) {
			log_message('notice', $data['message']['__all']);
		}
		return $result;
	}

	function get_record(&$data, $uid) {
		$data['foreign'] = $this->exhibitors_model->read($uid);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function create_record($foreign) {
	function create_record(&$foreign) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		return $this->exhibitors_model->create($foreign, $foreign['statusno'], 'F');
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function update_record($foreign) {
	function update_record($foreign = array()) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		return $this->exhibitors_model->update($foreign);
	}

	// Same as DELETE Procedure
	public function cancel($uid=null)
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (! isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix . '_nodata');
		} else {
			$this->session->set_flashdata('foreign', $data['foreign']);
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	/*
	 * 100(仮登録) → 200(未確認) or 201(内容不備) or 202(キャンセル待ち)
	 * 200(未確認) → 300(受理待ち) or 302(キャンセル待ち)
	 * 300(受理可能) → 400(出展) or 402(キャンセル待ち) or 403(出展拒否)
	 * 400(出展) → 401(受理書DL)
	 * 900(キャンセル)はいつでも
	 */
	function status_in()
	{
		$this->check_action();
		$foreign = $this->input->post();

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $foreign['exhid']);

		$data['foreign']['nextstate'] = $foreign['nextstate'];

		// 入力値をチェック
		$foreign_value = array(
			'exhid'		=> 'trim|required',
			'nextstate' => 'trim|xss_clean|is_natural_no_zero',
		);
		foreach($foreign_value as $key=>$val) {
			$this->form_validation->set_rules($key, 'lang:'.$key, $val);
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

		// 上記チェック中にフィルタもかけるため、チェック後に格納する
		foreach($foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
//		if (!isset($data['message']) || empty($data['message'])) {
//			$this->check_logic($data);
//		}

		// 入力不備ならリダイレクト
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/status/' . $data['foreign'][$this->foreign_keyid]);
		}

		// 確認画面にリダイレクト
		redirect(uri_redirect_string() . '/status_confirm');
	}

	function status_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');
		$this->parser->parse($this->form_prefix.'_status_confirm.html', $data);
	}

	function generate_password()
	{
		$letter1 = '2345678cEFGHJKLmnPRsT';
		$letter2 = '2345678CefghjkLMNprSt';
		$letter = intval(rand() % 2) ? $letter1:$letter2;
		$letterx = $letter;
		$password = 
			substr(str_shuffle($letter), 0, 1).
			substr(str_repeat(str_shuffle($letterx),2), 0, 7).
			substr(str_shuffle($letter), 0, 1);

		return $password;
	}

	function status_confirm_in()
	{
		$this->check_action();

		$foreign = $this->session->flashdata('foreign');
		$keyid = $foreign[$this->foreign_keyid];
		if ($this->foreign_token !== FALSE) {
			$token = $foreign[$this->foreign_token];
		}

		// 実際のステータス変更
		$this->db
			->set('statusno', $foreign['nextstate'])
			->where('expired', 0)
			->where($this->foreign_keyid, $keyid);

		if ($this->foreign_token !== FALSE) {
			$this->db
				->set('token', $this->create_token())
				->where($this->foreign_token, $token);
		}
		$this->db->update($this->table_name);
		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		} else {
			$result = TRUE;
		}

		// ステータスが受理の場合は、メンバーIDも同時に作成する

		if ($result) {
			if (isset($foreign['nextstate']) && $foreign['nextstate'] == 401) {
				$data = $this->setup_data();
				$this->setup_form($data);
				$this->get_record($data, $keyid);

				for($i = 0; $i <= 9; $i++) {
					if (isset($data['foreign']['q_boothid'.$i]) && $data['foreign']['q_boothid'.$i] != '') {
						$row = $this->booth_model->get_boothspace($data['foreign']['q_booth'.$i]);
						$username = strtolower($data['foreign']['exhid'] . substr($row['spaceabbr'],0,1) . $i);
						$rolename = (isset($data['foreign']['promotion']) && $data['foreign']['promotion'] != '');
						$this->db->set('memberid', "nextuid('members.memberid','M')", FALSE);
						$this->db->set('rolename',($rolename != FALSE ? 'promotion':'exhibitor'));
						$this->db->set('username', $username);
						$this->db->set('password', $this->generate_password());
						$this->db->set('email', $data['foreign']['c_email']);
						$this->db->set('pcode1', $data['foreign']['exhid']);
						$this->db->set('pcode2', $data['foreign']['q_boothid'.$i] );
						$this->db->set('token', $this->create_token());
						$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
						$this->db->set('activate', 0);
						if ($this->db->insert('members')) {
							log_message('notice', sprintf('[M2011] CREATED MEMBER=%d', empty($memberid)?'':$memberid));
						} else {
							log_message('notice', sprintf('[M4011] FAILED CREATE MEMBER=%s', $exhibitor['exhid']));
						}
					}
				}
			}
		}

		if ($result === FALSE) {
			$line = $this->lang->line('LOG:N4005');
			log_message('notice', sprintf($line, $this->table_name, $keyid));
			log_message('notice', $this->db->last_query());
			$line = $this->lang->line('N4005');
			$message = explode("\n", $line);
		} else {
			$line = $this->lang->line('LOG:M2005');
			log_message('notice', sprintf($line, $this->table_name, $keyid));
			log_message('notice', $this->db->last_query());
			$line = $this->lang->line('M2005');
			$message = explode("\n", $line);
			$this->log_history('ステータス変更', $keyid);
			// 変更後ロジック
			$this->after_status($foreign);
		}
		$this->session->keep_flashdata('foreign');
		$this->session->set_flashdata('message', $message);
		redirect('/' . dirname(uri_string()) . '/status_changed', 'location', 302);
	}

	function status_changed()
	{
		$this->completed($this->form_prefix.'_status_changed');
	}

	protected function after_status(&$data)
	{
		if (isset($data['nextstate']) && $data['nextstate'] == 400) {
			// 更新日はデータベース日付なので、もう一度取り直す.
			$uid = $data[$this->foreign_keyid];
			$this->get_record($data, $uid);

			$this->load->library('email');
			$mailto = array($data['foreign']['c_email']);
			if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
				$mailfrom = 'info@tokyoautosalon.jp';
				$namefrom = 'TOKYO AUTO SALON';
			} else {
				$mailfrom = 'kobayashi@hornet-works.jp';
				$namefrom = 'TOKYO AUTO SALON(TEST MAIL)';
			}

			// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
			// $this->load->library('encrypt');
			// $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
			// $code = $this->encrypt->encode($data['foreign'][$this->foreign_keyid]);
			$this->load->library('encryption');
			$this->encryption->initialize(
				array(
						'cipher' => 'blowfish',
						'mode' => 'cbc'
				)
			);
			$code = $this->encryption->encrypt($data['foreign'][$this->foreign_keyid]);
			// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
			$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
			$data['base_url'] = $this->config->site_url();
			if ((!isset($data['foreign']['promotion'])) || $data['foreign']['promotion'] == '') {
				$text = $this->parser->parse('mail/exhibitor_confirm.txt', $data, TRUE);
			} else {
				// [tasa:1002]媒体はステータス変更時にプロモーションレジストされる扱い.
				$text = $this->parser->parse('mail/promotion_regist_url.txt', $data, TRUE);
			}
			if (strpos($text, "\n") !== FALSE) {
				list($subject, $message) = explode("\n", $text, 2);
			} else {
				$subject = 'TOKYO AUTO SALON 2020【出展申込み確認メール】（控）';
				$message = $text;
			}

			$this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
			$this->email->to($mailto);
			$this->email->bcc($mailfrom.", mori@sun-a.com");
			$this->email->reply_to($mailfrom);
			$this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
			$this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
			$this->email->send();
			log_message('notice', 'Send Notice(change) to ' . implode(',',$mailto));
		}elseif (isset($data['nextstate']) && $data['nextstate'] == 302) {
            // 更新日はデータベース日付なので、もう一度取り直す.
            $uid = $data[$this->foreign_keyid];
            $this->get_record($data, $uid);

            $this->load->library('email');
            $mailto = array($data['foreign']['c_email']);
            if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
                $mailfrom = 'info@tokyoautosalon.jp';
                $namefrom = 'TOKYO AUTO SALON';
            } else {
                $mailfrom = 'kobayashi@hornet-works.jp';
                $namefrom = 'TOKYO AUTO SALON';
            }

			// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
			// $this->load->library('encrypt');
            // $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
			// $code = $this->encrypt->encode($data['foreign'][$this->foreign_keyid]);
			$this->load->library('encryption');
			$this->encryption->initialize(
				array(
						'cipher' => 'blowfish',
						'mode' => 'cbc'
				)
			);
			$code = $this->encryption->encrypt($data['foreign'][$this->foreign_keyid]);
			// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
            $data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
            $data['base_url'] = $this->config->site_url();
            if ((!isset($data['foreign']['promotion'])) || $data['foreign']['promotion'] == '') {
                $text = $this->parser->parse('mail/exhibitor_confirm_waiting.txt', $data, TRUE);
            }
            if (strpos($text, "\n") !== FALSE) {
                list($subject, $message) = explode("\n", $text, 2);
            } else {
                $subject = 'TOKYO AUTO SALON 2020【出展申込み確認メール】（控）';
                $message = $text;
            }

            $this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
            $this->email->to($mailto);
            $this->email->bcc($mailfrom.", mori@sun-a.com");
            $this->email->reply_to($mailfrom);
            $this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
            $this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
            $this->email->send();
            log_message('notice', 'Send Notice(change) to ' . implode(',',$mailto));
        }
	}

	public function report()
	{
		$data = $this->setup_data();

		$lists = array();
		$this->db->select('spaceid, spacename, spaceabbr, maxspaces');
		$this->db->order_by('seqno');
		$query = $this->db->get('v_spaces');
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				$lists[$row['spaceid']] = $row;
				$lists[$row['spaceid']]['s100'] = 0;
				$lists[$row['spaceid']]['s200'] = 0;
				$lists[$row['spaceid']]['s300'] = 0;
				$lists[$row['spaceid']]['s400'] = 0;
				$lists[$row['spaceid']]['s401'] = 0;
				$lists[$row['spaceid']]['s202'] = 0;
				$lists[$row['spaceid']]['s302'] = 0;
				$lists[$row['spaceid']]['s402'] = 0;
				$lists[$row['spaceid']]['faxcount'] = 0;
				$lists[$row['spaceid']]['webcount'] = 0;
			}
		}
		$this->db->select('booths.spaceid, exhibitors.statusno');
		$this->db->select_sum('`boothcount` * `count`', 'counter', FALSE);
		$this->db->select_sum("IF(STRCMP(`route`, 'W'), `boothcount` * `count`, 0)", 'faxcount', FALSE);
		$this->db->select_sum("IF(STRCMP(`route`, 'W'), 0, `boothcount` * `count`)", 'webcount', FALSE);
		$this->db->from('booths');
		$this->db->join('exhibitor_booth', 'exhibitor_booth.boothid = booths.boothid AND exhibitor_booth.expired = 0');
		$this->db->join('exhibitors', 'exhibitors.exhid = exhibitor_booth.exhid AND exhibitors.expired = 0');
		$this->db->group_by(array('booths.spaceid', 'exhibitors.statusno'));
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				$lists[$row['spaceid']]['s'.$row['statusno']] = $row['counter'];
				$lists[$row['spaceid']]['webcount'] += $row['webcount'];
				$lists[$row['spaceid']]['faxcount'] += $row['faxcount'];
			}
		}
		log_message('info', $this->db->last_query());

		$data['lists'] = $lists;
		$this->parser->parse('exhibitor_report', $data);
	}

	protected function after_change(&$data)
	{
		// 変更確認メールを送る必要があるときのみ、送信する.
		if (isset($data['post']['remail_x']) && isset($data['post']['remail_y'])) {
			// 更新日はデータベース日付なので、もう一度取り直す.
			$uid = $data['foreign'][$this->foreign_keyid];
			$this->get_record($data, $uid);

			$this->load->library('email');
			$mailto = array($data['foreign']['c_email']);
			if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
				$mailfrom = 'info@tokyoautosalon.jp';
				$namefrom = 'TOKYO AUTO SALON';
			} else {
				$mailfrom = 'kobayashi@hornet-works.jp';
				$namefrom = 'TOKYO AUTO SALON(TEST MAIL)';
			}
	
			// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
			// $this->load->library('encrypt');
			// $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
			// $code = $this->encrypt->encode($data['foreign'][$this->foreign_keyid]);
			$this->load->library('encryption');
			$this->encryption->initialize(
				array(
						'cipher' => 'blowfish',
						'mode' => 'cbc'
				)
			);
			$code = $this->encryption->encrypt($data['foreign'][$this->foreign_keyid]);
			// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
			$data['base_url'] = $this->config->site_url();
			$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
			$text = $this->parser->parse('mail/exhibitor_change.txt', $data, TRUE);
			if (strpos($text, "\n") !== FALSE) {
				list($subject, $message) = explode("\n", $text, 2);
			} else {
				$subject = 'TOKYO AUTO SALON 2020【出展申込み確認メール】（控）';
				$message = $text;
			}

			$this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
			$this->email->to($mailto);
			$this->email->bcc($mailfrom.", mori@sun-a.com");
			$this->email->reply_to($mailfrom);
			$this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
			$this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
			$this->email->send();
			log_message('notice', 'Send Notice(change) to ' . implode(',',$mailto));
		}
	}

	protected function after_delete(&$data)
	{
		if (!isset($data['foreign']['c_email'])) {
			return;
		}

		// 更新日はデータベース日付なので、もう一度取り直す.
		$uid = $data['foreign'][$this->foreign_keyid];
		$this->get_record($data, $uid);

		$this->load->library('email');
		$mailto = array($data['foreign']['c_email']);
		$mailfrom = 'info@tokyoautosalon.jp';
		$namefrom = 'TOKYO AUTO SALON';

		// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
		// $this->load->library('encrypt');
		// $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
		// $code = $this->encrypt->encode($data['foreign'][$this->foreign_keyid]);
		$this->load->library('encryption');
		$this->encryption->initialize(
			array(
					'cipher' => 'blowfish',
					'mode' => 'cbc'
			)
		);
		$code = $this->encryption->encrypt($data['foreign'][$this->foreign_keyid]);
		// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
		$data['base_url'] = $this->config->site_url();
		$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
		$text = $this->parser->parse('mail/exhibitor_delete.txt', $data, TRUE);
		if (strpos($text, "\n") !== FALSE) {
			list($subject, $message) = explode("\n", $text, 2);
		} else {
			$subject = 'TOKYO AUTO SALON 2020【出展申込み確認メール】（控）';
			$message = $text;
		}
/*
		$this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
		$this->email->to($mailto);
		$this->email->bcc($mailfrom);
		$this->email->reply_to($mailfrom);
		$this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
		$this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
		$this->email->send();
*/
	}

	function acceptance($uid = '', $preview='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			$this->load->model('member/members');
//			$user = $this->members->get_user_by_pcode($data['foreign']['exhid']);
//			$data['username'] = $user->username;
//			$data['password'] = $user->password;
//			$data['passyomi'] = $this->pronunciation_word($data['password']);
			$data['preview'] = $preview;
			$this->load->view('pdf/exhibitor_acceptance.php', $data);
		}
	}

	function download($command = '')
	{
		$command = strtolower($command);
		if ($command == 'all') {
			$datestr = date('YmdHi');
			$filename = 'exhibitor-all-'.$datestr.'.csv';
			$data = $this->download_all();
			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
			$this->load->helper('download');
			force_download($filename, $data);
		} elseif ($command == '001') {
			$datestr = date('YmdHi');
			$filename = 'exhibitor-001-'.$datestr.'.csv';
			$data = $this->download_001();
			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
			$this->load->helper('download');
			force_download($filename, $data);
		} elseif ($command == '001xls') {
//			$datestr = date('YmdHi');
//			$filename = 'exhibitor-001-'.$datestr.'.xls';
			$data = $this->download_001xls();
//			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
//			$this->load->helper('download');
//			force_download($filename, $data);
		} elseif ($command == '003') {
			$datestr = date('YmdHi');
			$filename = 'exhibitor-003-'.$datestr.'.csv';
			$data = $this->download_003();
			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
			$this->load->helper('download');
			force_download($filename, $data);
		} elseif ($command == 'yamato') {
//			$datestr = date('YmdHi');
//			$filename = 'exhibitor-yamato-'.$datestr.'.csv';
			$data = $this->download_yamato();
//			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
//			$this->load->helper('download');
//			force_download($filename, $data);
		} elseif ($command == 'sagawa') {
			$datestr = date('YmdHi');
			$filename = 'exhibitor-sagawa-'.$datestr.'.csv';
			$data = $this->download_sagawa();
			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
			$this->load->helper('download');
			force_download($filename, $data);
		}
		$data = $this->setup_data();
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function download_all()
	{
		$this->load->dbutil();
		$this->db->select("e.exhid '出展コード', e.corpname '出展会社名', e.corpkana '出展会社名カナ'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$this->db->select("e.countrycode '国', e.zip '郵便番号', e.prefecture '都道府県', CONCAT(e.address1,' ',IFNULL(e.address2,'')) '住所'", FALSE);
		$this->db->select("e.position '役職', e.fullname '出展者氏名', e.fullkana '出展者氏名カナ'");
		$this->db->select("e.phone 'TEL', e.fax 'FAX', e.url 'URL'");

		$this->db->select("m.corpname '(責任者)会社名', m.corpkana '(責任者)会社名カナ'");
		$this->db->select("m.zip '(責任者)郵便番号', m.prefecture '(責任者)都道府県', CONCAT(m.address1,' ',IFNULL(m.address2,'')) '(責任者)住所'", FALSE);
		$this->db->select("m.division '(責任者)所属', m.position '(責任者)役職', m.fullname '(責任者)氏名', m.fullkana '(責任者)氏名カナ'");
		$this->db->select("m.phone '(責任者)TEL', m.fax '(責任者)FAX', m.mobile '(責任者)携帯', m.email '(責任者)メールアドレス'");

		$this->db->select("b.corpname '(請求先)会社名', b.corpkana '(請求先)会社名カナ'");
		$this->db->select("b.zip '(請求先)郵便番号', b.prefecture '(請求先)都道府県', CONCAT(b.address1,' ',IFNULL(b.address2,'')) '(請求先)住所'", FALSE);
		$this->db->select("b.division '(請求先)所属', b.position '(請求先)役職', b.fullname '(請求先)氏名', b.fullkana '(請求先)氏名カナ'");
		$this->db->select("b.phone '(請求先)TEL', b.fax '(請求先)FAX'");

		$this->db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$this->db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$this->db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$this->db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");

		$this->db->select("d.corpname '(送付先)会社名', d.corpkana '(送付先)会社名カナ'");
		$this->db->select("d.zip '(送付先)郵便番号', d.prefecture '(送付先)都道府県', CONCAT(d.address1,' ',IFNULL(d.address2,'')) '(送付先)住所'", FALSE);
		$this->db->select("d.division '(送付先)所属', d.position '(送付先)役職', d.fullname '(送付先)氏名', d.fullkana '(送付先)氏名カナ'");
		$this->db->select("d.phone '(送付先)TEL', d.fax '(送付先)FAX'");

		$this->db->select("bs.boothname '小間形状'");
		$this->db->select("bs.boothcount AS '希望小間数'");
		$this->db->select("LPAD(bo.exhboothno, 4, '0') AS '小間番号'", FALSE);
        $this->db->select("e.remark '備考'");

		$this->db->select("e.promotion 'プロモーションコード'");
		$this->db->select("rs.statusname 'ステータス'");
		$this->db->select("e.comment '事務局メモ'");
		$this->db->select("e.accepted '申込日時'");
		$this->db->select("e.updated '更新日時'");

		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$this->db->join('exhibitor_bill b', 'e.exhid = b.exhid AND b.seqno = 0');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');

		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->join('receipt_status rs', 'rs.statusno = e.statusno' , 'left');
		$this->db->where('e.expired', '0');
		$this->db->where('bo.expired', '0');
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		return deco_csv_from_result($query);
	}

	protected function download_001()
	{
		$this->load->dbutil();
		$this->db->select("e.exhid '出展コード', e.corpname '出展会社名', e.corpkana '出展会社名カナ'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$this->db->select("e.countrycode '国', e.zip '郵便番号', e.prefecture '都道府県', CONCAT(e.address1,' ',IFNULL(e.address2,'')) '住所'", FALSE);
		$this->db->select("e.position '役職', e.fullname '出展者氏名', e.fullkana '出展者氏名カナ'");
		$this->db->select("e.phone 'TEL', e.fax 'FAX', e.url 'URL'");

		$this->db->select("m.corpname '(責任者)会社名', m.corpkana '(責任者)会社名カナ'");
		$this->db->select("m.zip '(責任者)郵便番号', m.prefecture '(責任者)都道府県', CONCAT(m.address1,' ',IFNULL(m.address2,'')) '(責任者)住所'", FALSE);
		$this->db->select("m.division '(責任者)所属', m.position '(責任者)役職', m.fullname '(責任者)氏名', m.fullkana '(責任者)氏名カナ'");
		$this->db->select("m.phone '(責任者)TEL', m.fax '(責任者)FAX', m.mobile '(責任者)携帯', m.email '(責任者)メールアドレス'");

		$this->db->select("b.corpname '(請求先)会社名', b.corpkana '(請求先)会社名カナ'");
		$this->db->select("b.zip '(請求先)郵便番号', b.prefecture '(請求先)都道府県', CONCAT(b.address1,' ',IFNULL(b.address2,'')) '(請求先)住所'", FALSE);
		$this->db->select("b.division '(請求先)所属', b.position '(請求先)役職', b.fullname '(請求先)氏名', b.fullkana '(請求先)氏名カナ'");
		$this->db->select("b.phone '(請求先)TEL', b.fax '(請求先)FAX'");

		$this->db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$this->db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$this->db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$this->db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");

		$this->db->select("d.corpname '(送付先)会社名', d.corpkana '(送付先)会社名カナ'");
		$this->db->select("d.zip '(送付先)郵便番号', d.prefecture '(送付先)都道府県', CONCAT(d.address1,' ',IFNULL(d.address2,'')) '(送付先)住所'", FALSE);
		$this->db->select("d.division '(送付先)所属', d.position '(送付先)役職', d.fullname '(送付先)氏名', d.fullkana '(送付先)氏名カナ'");
		$this->db->select("d.phone '(送付先)TEL', d.fax '(送付先)FAX'");

		$this->db->select("bs.boothname '小間形状'");
		$this->db->select("bs.boothcount AS '希望小間数'");
		$this->db->select("LPAD(bo.exhboothno, 4, '0') AS '小間番号'", FALSE);
        $this->db->select("e.remark '備考'");
		$this->db->select("e.promotion 'プロモーションコード'");
		$this->db->select("rs.statusname 'ステータス'");
		$this->db->select("e.comment '事務局メモ'");
		$this->db->select("e.accepted '申込日時'");
		$this->db->select("e.updated '更新日時'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$this->db->join('exhibitor_bill b', 'e.exhid = b.exhid AND b.seqno = 0');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');
		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->join('receipt_status rs', 'rs.statusno = e.statusno' , 'left');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where('bo.expired', '0');
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		return deco_csv_from_result($query);
//		return $this->dbutil->csv_from_result($query);
	}

	protected function download_001xls()
	{
		$this->load->dbutil();
		$this->db->select("e.exhid '出展コード', e.corpname '出展会社名', e.corpkana '出展会社名カナ'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$this->db->select("e.zip '郵便番号', e.prefecture '都道府県', CONCAT(e.address1,' ',IFNULL(e.address2,'')) '住所'", FALSE);
		$this->db->select("e.position '役職', e.fullname '出展者氏名', e.fullkana '出展者氏名カナ'");
		$this->db->select("e.phone 'TEL', e.fax 'FAX', e.url 'URL'");

		$this->db->select("m.corpname '(責任者)会社名', m.corpkana '(責任者)会社名カナ'");
		$this->db->select("m.zip '(責任者)郵便番号', m.prefecture '(責任者)都道府県', CONCAT(m.address1,' ',IFNULL(m.address2,'')) '(責任者)住所'", FALSE);
		$this->db->select("m.division '(責任者)所属', m.position '(責任者)役職', m.fullname '(責任者)氏名', m.fullkana '(責任者)氏名カナ'");
		$this->db->select("m.phone '(責任者)TEL', m.fax '(責任者)FAX', m.mobile '(責任者)携帯', m.email '(責任者)メールアドレス'");

		$this->db->select("b.corpname '(請求先)会社名', b.corpkana '(請求先)会社名カナ'");
		$this->db->select("b.zip '(請求先)郵便番号', b.prefecture '(請求先)都道府県', CONCAT(b.address1,' ',IFNULL(b.address2,'')) '(請求先)住所'", FALSE);
		$this->db->select("b.division '(請求先)所属', b.position '(請求先)役職', b.fullname '(請求先)氏名', b.fullkana '(請求先)氏名カナ'");
		$this->db->select("b.phone '(請求先)TEL', b.fax '(請求先)FAX'");

		$this->db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$this->db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$this->db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$this->db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");

		$this->db->select("d.corpname '(送付先)会社名', d.corpkana '(送付先)会社名カナ'");
		$this->db->select("d.zip '(送付先)郵便番号', d.prefecture '(送付先)都道府県', CONCAT(d.address1,' ',IFNULL(d.address2,'')) '(送付先)住所'", FALSE);
		$this->db->select("d.division '(送付先)所属', d.position '(送付先)役職', d.fullname '(送付先)氏名', d.fullkana '(送付先)氏名カナ'");
		$this->db->select("d.phone '(送付先)TEL', d.fax '(送付先)FAX'");

		$this->db->select("bs.boothname '小間形状'");
		$this->db->select("bs.boothcount AS '希望小間数'");
		$this->db->select("LPAD(bo.exhboothno, 4, '0') AS '小間番号'", FALSE);
        $this->db->select("e.remark '備考'");
		$this->db->select("e.promotion 'プロモーションコード'");
		$this->db->select("rs.statusname 'ステータス'");
		$this->db->select("e.comment '事務局メモ'");
		$this->db->select("e.accepted '申込日時'");
		$this->db->select("e.updated '更新日時'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$this->db->join('exhibitor_bill b', 'e.exhid = b.exhid AND b.seqno = 0');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');
		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->join('receipt_status rs', 'rs.statusno = e.statusno' , 'left');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$this->db->where('bo.expired', '0');
		$query = $this->db->get();
		if ($query !== FALSE) {
			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			// $this->load->library('excel');
			// $this->excel->setActiveSheetIndex(0);
			// $sheet = $this->excel->getActiveSheet();
			$this->load->library('Excel_lib');
			$this->excel_lib->setActiveSheetIndex(0);
			$sheet = $this->excel_lib->getActiveSheet();
			// Upgrade PHP7 - Rename class to make it loadable - End by TTM
			$sheet->getpageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$sheet->getpageSetup()->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
			$sheet->setTitle('exhibitor');
			$sheet->getDefaultStyle()->getFont()->setName('ＭＳ Ｐゴシック');
			$sheet->getDefaultStyle()->getFont()->setSize(11);

			$cell_style = array('numberformat' => array('code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT));
			$y = 1;
			foreach($query->result_array() as $row) {
				$i = 1;
				foreach ($row as $key=>$val) {
					$sheet->getStyleByColumnAndRow($i, $y)->applyFromArray($cell_style);
					$sheet->setCellValueExplicitByColumnAndRow($i, $y, $val, PHPExcel_Cell_DataType::TYPE_STRING);
					$i++;
				}
				$y++;
			}

			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			// $xls = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
			$xls = PHPExcel_IOFactory::createWriter($this->excel_lib, 'Excel2007');
			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="exhibitor.xlsx"');
			header('Cache-Control: max-age=0');
			$xls->save("php://output");
			exit;
		}
	}

	protected function download_003()
	{
		$this->load->dbutil();
		$this->db->select("e.exhid '出展コード', e.corpname '出展会社名', e.brandname '出展会社名カナ'");
		$this->db->select("cus.tas 'TAS', cus.napac 'NAPAC', cus.tascount '出展回数'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$this->db->select("sp.spaceabbr AS '希望小間形状'");
		$this->db->select("bs.boothcount AS '希望小間数'");
		$this->db->select("rs.statusname 'ステータス'");
		$this->db->select("e.promotion AS 'プロモーションコード'");
		$this->db->select("e.comment AS '事務局メモ'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_booth b', 'e.exhid = b.exhid AND b.expired = 0');
		$this->db->join('booths bs', 'bs.boothid = b.boothid');
		$this->db->join('v_spaces sp', 'sp.spaceid = bs.spaceid');
		$this->db->join('customers cus', 'cus.corpkana = e.corpkana', 'left');
		$this->db->join('receipt_status rs', 'rs.statusno = e.statusno' , 'left');
		$this->db->where('e.expired', '0');
		$this->db->where('b.expired', '0');
		$this->db->order_by('e.exhid ASC');
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		return $this->dbutil->csv_from_result($query);
	}

	protected function download_yamato()
	{
		$this->load->dbutil();
//TODO:列を整える
		$this->db->select("e.exhid 'お客様管理番号'");
		$this->db->select("0 '送り状種類', 0 'クール区分', NULL '伝票番号', '2014/09/15' AS '出荷予定日', NULL 'お届け予定日'", FALSE);
		$this->db->select("NULL '配達時間帯', NULL 'お届先コード'", FALSE);
		$this->db->select("d.phone 'お届け先電話番号', NULL 'お届け先電話番号枝番'", FALSE);
		$this->db->select("d.zip 'お届け先郵便番号', CONCAT(d.prefecture,' ',d.address1,' ') 'お届け先住所'", FALSE);
		$this->db->select("d.address2 'お届け先アパートマンション名'");
		$this->db->select("d.corpname 'お届け先会社・部門１', d.division 'お届け先会社・部門２'");
		$this->db->select("d.fullname 'お届け先名', NULL 'お届け先名(ｶﾅ)', '様' AS '敬称'", FALSE);
		$this->db->select("NULL 'ご依頼主コード', '03-6897-4820' AS 'ご依頼主電話番号', NULL 'ご依頼主電話番号枝番'", FALSE);
		$this->db->select("'160-8463' AS 'ご依頼主郵便番号', '東京都新宿区新宿6-27-30 7F' AS 'ご依頼主住所'", FALSE);
		$this->db->select("NULL 'ご依頼主アパートマンション名'", FALSE);
		$this->db->select("'東京オートサロン事務局' AS 'ご依頼主名', NULL 'ご依頼主名(ｶﾅ)'", FALSE);
		$this->db->select("NULL '品名コード１', '書類' AS '品名１'", FALSE);
		$this->db->select("NULL '品名コード２', NULL '品名２'", FALSE);
		$this->db->select("NULL '荷扱い１', NULL '荷扱い２'", FALSE);
		$this->db->select("NULL '記事', NULL 'ｺﾚｸﾄ代金引換額（税込)'", FALSE);
		$this->db->select("NULL '内消費税額等', NULL '止置き'", FALSE);
		$this->db->select("NULL '営業所コード', NULL '発行枚数', NULL '個数口表示フラグ'", FALSE);
		$this->db->select("'XXXXXXXXXXXX' AS '請求先顧客コード', NULL '請求先分類コード', '01' AS '運賃管理番号'", FALSE);
//		$this->db->select("NULL '注文時カード払いデータ登録', NULL '注文時カード払い加盟店番号',", FALSE);
//		$this->db->select("NULL '注文時カード払い申込受付番号１', NULL '注文時カード払い申込受付番号２', NULL '注文時カード払い申込受付番号３',", FALSE);
//		$this->db->select("NULL 'お届け予定ｅメール利用区分', NULL 'お届け予定ｅメールe-mailアドレス', NULL '入力機種', NULL 'お届け予定ｅメールメッセージ',", FALSE);
//		$this->db->select("NULL 'お届け完了ｅメール利用区分', NULL 'お届け完了ｅメールe-mailアドレス', NULL 'お届け完了ｅメールメッセージ',", FALSE);
//		for ($i=0; $i<19; $i++) {
//			$this->db->select("NULL '予備".$i."',", FALSE);
//		}

		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');
//		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
//		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->join('receipt_status rs', 'rs.statusno = e.statusno' , 'left');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
//		$this->db->where('bo.expired', '0');
		$query = $this->db->get();
		if ($query !== FALSE) {
			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			// $this->load->library('excel');
			// $this->excel->setActiveSheetIndex(0);
			// $sheet = $this->excel->getActiveSheet();
			$this->load->library('Excel_lib');
			$this->excel_lib->setActiveSheetIndex(0);
			$sheet = $this->excel_lib->getActiveSheet();
			// Upgrade PHP7 - Rename class to make it loadable - End by TTM
			$sheet->getpageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$sheet->getpageSetup()->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
			$sheet->setTitle('exhibitor');
			$sheet->getDefaultStyle()->getFont()->setName('ＭＳ Ｐゴシック');
			$sheet->getDefaultStyle()->getFont()->setSize(11);

			$cell_style = array('numberformat' => array('code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT));
			$y = 1;
			foreach($query->result_array() as $row) {
				$i = 0;
				foreach ($row as $key=>$val) {
					$sheet->getStyleByColumnAndRow($i, $y)->applyFromArray($cell_style);
					$sheet->setCellValueExplicitByColumnAndRow($i, $y, $val, PHPExcel_Cell_DataType::TYPE_STRING);
					$i++;
				}
				$y++;
			}

			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			// $xls = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
			$xls = PHPExcel_IOFactory::createWriter($this->excel_lib, 'Excel2007');
			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="exhibitor-yamato.xlsx"');
			header('Cache-Control: max-age=0');
			$xls->save("php://output");
			exit;
		}
	}

	protected function download_sagawa()
	{
		$this->load->dbutil();
		$this->db->select("d.phone '送付先電話番号', d.zip '送付先郵便番号'");
		$this->db->select("CONCAT(d.prefecture,' ',d.address1) '送付先住所1', IFNULL(d.address2,'') '送付先住所2', NULL AS '送付先住所3'", FALSE);
		$this->db->select("d.corpname '送付先名称1'");
		$this->db->select("CONCAT(d.division,' ',d.fullname,'様') '送付先名称2'", FALSE);
		$this->db->select("e.exhid 'お客様管理ナンバー'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');
//		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
//		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->join('receipt_status rs', 'rs.statusno = e.statusno' , 'left');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('500','401','400'));
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		return $this->dbutil->csv_from_result($query);
	}

	public function debugmail($uid)
	{
		$data = array();
		$this->get_record($data, $uid);
 
		$this->load->library('email');
		$mailto = array($data['foreign']['c_email']);
		if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
				$mailfrom = 'info@tokyoautosalon.jp';
				$namefrom = 'TOKYO AUTO SALON';
		} else {
				$mailfrom = 'kobayashi@hornet-works.jp';
				$namefrom = 'TOKYO AUTO SALON';
		}
 
		
		// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
		// $this->load->library('encrypt');
		// $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
		// $code = $this->encrypt->encode($data['foreign']['exhid']);
		$this->load->library('encryption');
		$this->encryption->initialize(
			array(
					'cipher' => 'blowfish',
					'mode' => 'cbc'
			)
		);
		$code = $this->encryption->encrypt($data['foreign']['exhid']);
		// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
		$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
		$data['base_url'] = $this->config->site_url();
		if ((!isset($data['foreign']['promotion'])) || $data['foreign']['promotion'] == '') {
				$text = $this->parser->parse('mail/exhibitor_confirm_sorry.txt', $data, TRUE);
		} else {
				$text = $this->parser->parse('mail/promotion_regist_url.txt', $data, TRUE);
		}
		if (strpos($text, "\n") !== FALSE) {
				list($subject, $message) = explode("\n", $text, 2);
		} else {
				$subject = 'TOKYO AUTO SALON 2020【出展申込み確認メール】（控）';
				$message = $text;
		}
 
		$this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
		$this->email->to($mailto);
		$this->email->bcc($mailfrom.', mori@sun-a.com');
		$this->email->reply_to($mailfrom);
		$this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
		$this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
		log_message('info', 'Send Notice(from) - ' . $mailfrom . '/' . $namefrom);
		log_message('info', 'Send Notice(to) - ' . var_export($mailto,true));
		log_message('info', 'Send Notice(subject) - ' . $subject);
		log_message('info', 'Send Notice(message) - ' . $message);
		$this->email->send();
		log_message('info', 'Send Notice(body) - ' . $this->email->print_debugger());
		log_message('notice', 'Send Notice(change) to ' . implode(',',$mailto));
	}
}

/* End of file exhibitor.php */
/* Location: ./application/controllers/op/exhibitor.php */
