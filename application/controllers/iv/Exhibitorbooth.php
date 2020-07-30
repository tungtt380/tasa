<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exhibitorbooth extends MemOP_Controller {

	protected $form_prefix   = 'exhibitorbooth';	// フォーム名
	protected $table_name    = 'exhibitors';		// テーブル名
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
		'statusno'      => 'trim|xss_clean|is_natural_no_zero',
		'accepted'      => 'trim|xss_clean|valid_isodatetime',
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
		$limit  = $this->input->post('limit');
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
		if ($limit != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'limit=' . rawurlencode($limit);
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
		$limit = $this->input->get('limit');
		$this->db->start_cache();
		$this->db->where_in('statusno', array('400','401'));
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$this->db->collate_like('scantext', $keyword);
		} else {
			$data['q'] = '';
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($spaces !== NULL) {
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
		$this->db->select('promotion, comment, statusno, route, accepted, receipted, created, updated, deleted');
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
		if ($limit != '') {
			$querystring .= ($querystring == '' ? '?':'&') . 'limit=' . urlencode($limit);
		}
		$this->load->library('pagination');
		$config['base_url'] = uri_class_string() . '/' . $querystring;
		$config['total_rows'] = $data['count'];
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'start';
		$config['per_page'] = $limit;
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
		$this->load->model('status_model');
		$data['status'] = $this->status_model->get_dropdown();
	}

	function get_record(&$data, $uid) {
		$data['foreign'] = $this->exhibitors_model->read($uid);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function create_record($foreign) {
	function create_record(&$foreign) {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
die('Prohibited');
		return $this->exhibitors_model->create($foreign, $foreign['statusno'], 'F');
	}

	function update_record($foreign) {
die('Prohibited');
		return $this->exhibitors_model->update($foreign);
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
			$data = $this->download_001xls();
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
		$this->db->select("e.exhid '出展コード', e.corpname '出展会社名', e.corpkana '出展会社名カナ'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$this->db->select("e.countrycode '国', e.zip '郵便番号', e.prefecture '都道府県', CONCAT(e.address1,' ',IFNULL(e.address2,'')) '住所'", FALSE);
		$this->db->select("e.phone 'TEL', e.fax 'FAX', e.fullname '出展者氏名', e.fullkana '出展者氏名カナ'");
		$this->db->select("bs.boothname '小間形状'");
		$this->db->select("e.promotion 'プロモーションコード'");
		$this->db->select("IF(e.statusno=202,'キャンセル待ち','') AS 'ステータス'", FALSE);
		$this->db->select("e.comment '事務局メモ'");
		$this->db->select("e.accepted '申込日時'");
		$this->db->select("e.updated '更新日時'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_booth b', 'e.exhid = b.exhid');
		$this->db->join('booths bs', 'bs.boothid = b.boothid');
		$this->db->where('e.expired', '0');
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
		$this->db->select("e.promotion 'プロモーションコード'");
		$this->db->select("IF(e.statusno=202,'キャンセル待ち','') AS 'ステータス'", FALSE);
		$this->db->select("e.comment '事務局メモ'");
		$this->db->select("e.accepted '申込日時'");
		$this->db->select("e.updated '更新日時'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$this->db->join('exhibitor_bill b', 'e.exhid = b.exhid');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');
		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid');
		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('401','400'));
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
		$this->db->select("e.promotion 'プロモーションコード'");
		$this->db->select("IF(e.statusno=202,'キャンセル待ち','') AS 'ステータス'", FALSE);
		$this->db->select("e.comment '事務局メモ'");
		$this->db->select("e.accepted '申込日時'");
		$this->db->select("e.updated '更新日時'");
		$this->db->from('exhibitors e');
		$this->db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$this->db->join('exhibitor_bill b', 'e.exhid = b.exhid');
		$this->db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$this->db->join('exhibitor_dist d', 'e.exhid = d.exhid');
		$this->db->join('exhibitor_booth bo', 'e.exhid = bo.exhid');
		$this->db->join('booths bs', 'bs.boothid = bo.boothid');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('401','400'));
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
		$this->db->select("IF(e.statusno=202,'キャンセル待ち','') AS ステータス", FALSE);
		$this->db->select("e.promotion AS 'プロモーションコード'");
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
