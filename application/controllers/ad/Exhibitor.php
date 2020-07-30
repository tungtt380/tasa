<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exhibitor extends MemOP_Controller {

	protected $form_prefix   = 'exhibitor';		// フォーム名
	protected $table_name    = 'exhibitors';	// テーブル名
	protected $table_prefix  = 'S';				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'exhid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'corpname'      => 'trim|required|xss_clean',
		'corpkana'      => 'trim|required|xss_clean|prep_kana|valid_kana',
		'countrycode'   => 'trim|xss_clean',
		'zip'           => 'trim|xss_clean|valid_zip',
		'prefecture'    => 'trim|xss_clean',
		'address1'      => 'trim|xss_clean',
		'address2'      => 'trim|xss_clean',
		'phone'         => 'trim|xss_clean|valid_phone',
		'fax'           => 'trim|xss_clean|valid_phone',
		'url'           => 'trim|xss_clean|valid_hostname',
		'position'      => 'trim|xss_clean',
		'fullname'      => 'trim|xss_clean',
		'fullkana'      => 'trim|xss_clean|prep_kana|valid_kana',
		//
		'brandname'     => 'trim|required|xss_clean',
		'brandkana'     => 'trim|required|xss_clean|prep_kana|valid_kana',
		//
		'm_corpname'    => 'trim|xss_clean',
		'm_corpkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'm_countrycode' => 'trim|xss_clean',
		'm_zip'         => 'trim|xss_clean|valid_zip',
		'm_prefecture'  => 'trim|xss_clean',
		'm_address1'    => 'trim|xss_clean',
		'm_address2'    => 'trim|xss_clean',
		'm_division'    => 'trim|xss_clean',
		'm_position'    => 'trim|xss_clean',
		'm_fullname'    => 'trim|xss_clean|',
		'm_fullkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'm_phone'       => 'trim|xss_clean|valid_phone',
		'm_fax'         => 'trim|xss_clean|valid_phone',
		'm_mobile'      => 'trim|xss_clean|valid_phone',
		'm_email'       => 'trim|xss_clean|valid_email',
		//
		'b_corpname'    => 'trim|xss_clean',
		'b_corpkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'b_countrycode' => 'trim|xss_clean',
		'b_zip'         => 'trim|xss_clean|valid_zip',
		'b_prefecture'  => 'trim|xss_clean',
		'b_address1'    => 'trim|xss_clean',
		'b_address2'    => 'trim|xss_clean',
		'b_phone'       => 'trim|xss_clean|valid_phone',
		'b_fax'         => 'trim|xss_clean|valid_phone',
		'b_division'    => 'trim|xss_clean',
		'b_position'    => 'trim|xss_clean',
		'b_fullname'    => 'trim|xss_clean',
		'b_fullkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		//
		'c_corpname'    => 'trim|xss_clean',
		'c_corpkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'c_countrycode' => 'trim|xss_clean',
		'c_zip'         => 'trim|xss_clean|valid_zip',
		'c_prefecture'  => 'trim|xss_clean',
		'c_address1'    => 'trim|xss_clean',
		'c_address2'    => 'trim|xss_clean',
		'c_division'    => 'trim|xss_clean',
		'c_position'    => 'trim|xss_clean',
		'c_fullname'    => 'trim|xss_clean',
		'c_fullkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'c_phone'       => 'trim|xss_clean|valid_phone',
		'c_fax'         => 'trim|xss_clean|valid_phone',
		'c_mobile'      => 'trim|xss_clean|valid_phone',
		'c_email'       => 'trim|xss_clean|valid_email',
		//
		'd_corpname'    => 'trim|xss_clean',
		'd_corpkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'd_countrycode' => 'trim|xss_clean',
		'd_zip'         => 'trim|xss_clean|valid_zip',
		'd_prefecture'  => 'trim|xss_clean',
		'd_address1'    => 'trim|xss_clean',
		'd_address2'    => 'trim|xss_clean',
		'd_division'    => 'trim|xss_clean',
		'd_position'    => 'trim|xss_clean',
		'd_fullname'    => 'trim|xss_clean',
		'd_fullkana'    => 'trim|xss_clean|prep_kana|valid_kana',
		'd_phone'       => 'trim|xss_clean|valid_phone',
		'd_fax'         => 'trim|xss_clean|valid_phone',
		//
		'q_entrycars'   => 'trim|xss_clean|is_natural',
		'q_boothcount1' => 'trim|required|xss_clean|is_natural_no_zero',
		'q_boothcount2' => 'trim|xss_clean|is_natural',
		'q_boothcount3' => 'trim|xss_clean|is_natural',
		//
		'promotion'     => 'trim|xss_clean|alpha_dash',
		'comment'       => 'trim|xss_clean',
		'statusno'       => 'trim|xss_clean|is_natural_no_zero',
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
		$start = $this->input->get('start');
		$this->db->start_cache();
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$this->db->like('scantext', $keyword);
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
				$this->db->where_in('statusno', array(300,301,302));
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
			$this->db->like('scanbooth', $spaces.'@');
		} else {
			$data['sp'] = '';
		}
		$this->db->stop_cache();
		$this->db->select('exhid, corpname, corpkana, fullname, fullkana, brandname, brandkana');
		$this->db->select('promotion, comment, statusno, route, created, updated, deleted');
		$this->db->from('v_exhibitors_search');
		$data['count'] = $this->db->count_all_results();

		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($start !== FALSE && is_numeric($start)) {
		if ($start !== NULL && is_numeric($start)) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$this->db->limit($this->pp, $start);
			$data['page'] = ($start/$this->pp)+1;
		} else {
			$this->db->limit($this->pp);
			$data['page'] = 1;
		}

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
		$this->load->library('pagination');
		$config['base_url'] = uri_class_string() . '/' . $querystring;
		$config['total_rows'] = $data['count'];
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'start';
		$config['per_page'] = $this->pp;
		$config['num_links'] = 2;
		$this->pagination->initialize($config);
		$data['pagenation'] = $this->pagination->create_links();

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
	}

	protected function check_logic(&$data)
	{
		$this->load->model('booth_model');
		$foreign = $data['foreign'];
		$result = TRUE;
		$spacecounts = array('A'=>0,'B'=>0,'C'=>0,'D'=> 0,'E'=>0,'F'=>0,'S'=>0);
		$spacelimits = array('A'=>5,'B'=>4,'C'=>5,'D'=>30,'E'=>3,'F'=>2,'S'=>1);
		$othercount = 0;
		$contcount = 0;
		$waitcount = 0;

		for ($i=1;$i<=9;$i++) {
			if (isset($foreign['q_boothcount'.$i]) && $foreign['q_boothcount'.$i] == 1) {
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
/*
				if ($row['inventory'] == 0) {
					$contcount += 1;
				} else {
					$waitcount += 1;
				}
				if ($contcount > 0 && $waitcount > 0) {
					$data['message']['__all'] = '<br /><span class="red">通常受付のスペースとキャンセル待ちのスペースの同時申込みはできません。</span>';
					$result = FALSE;
					break;
				}
*/
			}
		}
		if ($result === FALSE) {
			log_message('notice', $data['message']['__all']);
		}
		return $result;
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function create_record($foreign) {
	function create_record(&$foreign) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		return $this->exhibitors_model->create($foreign, $foreign['statusno'], 'F');
	}

	function get_record(&$data, $uid) {
		$data['foreign'] = $this->exhibitors_model->read($uid);
	}

	function update_record($foreign) {
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
	 * 100(仮登録) → 200(未確認) or 201(内容不備)
	 * 200(未確認) → 300(受理待ち) or 301(受理可能) or 302(キャンセル待ち)
	 * 301(受理可能) → 401(出展) or 402(キャンセル待ち) or 403(出展拒否)
	 * 900(キャンセル)はいつでも
	 */
	function status_in()
	{
		$this->check_action();

		$foreign = $this->input->post();
		$keyid = $foreign[$this->foreign_keyid];
		if ($this->foreign_token !== FALSE) {
			$token = $foreign[$this->foreign_token];
		}

		// 変更ロジック(現在は存在しないが、あれば・・・)

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
		}
		$this->session->keep_flashdata('foreign');
		$this->session->set_flashdata('message', $message);
		redirect('/' . dirname(uri_string()) . '/', 'location', 302);
	}

	public function report()
	{
		$data = $this->setup_data();

		$lists = array();
		$this->db->select('spaceid, spacename, spaceabbr, maxspaces');
		$this->db->order_by('seqno');
		$query = $this->db->get('spaces');
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				$lists[$row['spaceid']] = $row;
				$lists[$row['spaceid']]['s100'] = 0;
				$lists[$row['spaceid']]['s200'] = 0;
				$lists[$row['spaceid']]['s300'] = 0;
				$lists[$row['spaceid']]['s400'] = 0;
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
		$this->db->join('exhibitor_booth', 'exhibitor_booth.boothid = booths.boothid');
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
		$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
		$text = $this->parser->parse('mail/exhibitor_change.txt', $data, TRUE);
		if (strpos($text, "\n") !== FALSE) {
			list($subject, $message) = explode("\n", $text, 2);
		} else {
			$subject = 'TOKYO AUTO SALON 2016【出展申込み確認メール】（控）';
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

	protected function after_delete(&$data)
	{
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
		$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
		$text = $this->parser->parse('mail/exhibitor_delete.txt', $data, TRUE);
		if (strpos($text, "\n") !== FALSE) {
			list($subject, $message) = explode("\n", $text, 2);
		} else {
			$subject = 'TOKYO AUTO SALON 2016【出展申込み確認メール】（控）';
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
		} elseif ($command == '003') {
			$datestr = date('YmdHi');
			$filename = 'exhibitor-003-'.$datestr.'.csv';
			$data = $this->download_003();
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
		$this->db->select('e.exhid, e.corpname, e.brandname');
		$this->db->select('bs.boothname AS space');
		$this->db->select('e.promotion');
		$this->db->select("IF(e.statusno=202,'キャンセル待ち','') AS status", FALSE);
		$this->db->select('e.comment');
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_booth b', 'e.exhid = b.exhid');
		$this->db->join('booths bs', 'bs.boothid = b.boothid');
		$this->db->where('e.expired', '0');
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		return $this->dbutil->csv_from_result($query);
	}

	protected function download_003()
	{
		$this->load->dbutil();
		$this->db->select("e.exhid '出展コード', e.corpname '出展会社名', e.brandname '出展会社名カナ'");
		$this->db->select("cus.tas 'TAS', cus.napac 'NAPAC', cus.tascount '出展回数'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$this->db->select("sp.spaceabbr AS '希望小間形状'");
		$this->db->select("bs.boothcount AS '希望小間数'");
		$this->db->select("IF(e.statusno=202,'キャンセル待ち','') AS ステータス", FALSE);
		$this->db->select("e.promotion AS 'プロモーションコード'");
		$this->db->select("e.comment AS '事務局メモ'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_booth b', 'e.exhid = b.exhid');
		$this->db->join('booths bs', 'bs.boothid = b.boothid');
		$this->db->join('spaces sp', 'sp.spaceid = bs.spaceid');
		$this->db->join('customers cus', 'cus.corpkana = e.corpkana', 'left');
		$this->db->where('e.expired', '0');
		$this->db->order_by('e.exhid ASC');
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		return $this->dbutil->csv_from_result($query);
	}
}

/* End of file exhibitor.php */
/* Location: ./application/controllers/op/exhibitor.php */
