<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fy2018 extends CI_Controller
{
	protected $ipass = array(
		'miko' => 'miko24',
		'tasaapi' => 'api!30th',
	);

	function __construct()
	{
		parent::__construct();
		if (!$this->check_ip_address()) {
			if (!isset($_SERVER['PHP_AUTH_USER'])) {
				header('WWW-Authenticate: Basic realm="TASA"');
				header('HTTP/1.0 401 Unauthorized');
				echo('Please enter a valid username and password');
				exit();
			} else if (isset($this->ipass[$_SERVER['PHP_AUTH_USER']]) && $this->ipass[$_SERVER['PHP_AUTH_USER']] == $_SERVER['PHP_AUTH_PW']) {
				return true;
			}
			else
			{
				echo('Please enter a valid username and password');
				exit();
			}
		}
	}

	protected function check_ip_address()
	{
		$ip = $this->input->server('REMOTE_ADDR');
		if (!$ip) {
			return FALSE;
		}

		$this->load->config('api',true);
		$conf = $this->config->item('api');
		if ( ! isset($conf['ALLOW_IP_LIST']) && !isarray($conf['ALLOW_IP_LIST'])) {
			return FALSE;
		}
		foreach($conf['ALLOW_IP_LIST'] as $v) {
			$s = trim($v);
			if (preg_match("/^{$s}/iu", $ip)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	function update()
	{
		$last_update = time();

		header("HTTP/1.1 200 OK");
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_update).' GMT');
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		echo('OK ' . date('Y-m-d\TH:i:s', $last_update));
		exit;
	}

	protected function wordslike($db, $letter)
	{
		switch($letter){
		case 'a':
			$db->where(
			"(e.corpkana like 'あ%' or
			e.corpkana like 'い%' or
			e.corpkana like 'う%' or
			e.corpkana like 'え%' or
			e.corpkana like 'お%')");
			break;
		case 'k':
			$db->where(
			"(e.corpkana like 'か%' or
			e.corpkana like 'き%' or
			e.corpkana like 'く%' or
			e.corpkana like 'け%' or
			e.corpkana like 'こ%')");
			break;
		case 's':
			$db->where(
			"(e.corpkana like 'さ%' or
			e.corpkana like 'し%' or
			e.corpkana like 'す%' or
			e.corpkana like 'せ%' or
			e.corpkana like 'そ%')");
			break;
		case 't':
			$db->where(
			"(e.corpkana like 'た%' or
			e.corpkana like 'ち%' or
			e.corpkana like 'つ%' or
			e.corpkana like 'て%' or
			e.corpkana like 'と%')");
			break;
		case 'n':
			$db->where(
			"(e.corpkana like 'な%' or
			e.corpkana like 'に%' or
			e.corpkana like 'ぬ%' or
			e.corpkana like 'ね%' or
			e.corpkana like 'の%')");
			break;
		case 'h':
			$db->where(
			"(e.corpkana like 'は%' or
			e.corpkana like 'ひ%' or
			e.corpkana like 'ふ%' or
			e.corpkana like 'へ%' or
			e.corpkana like 'ほ%')");
			break;
		case 'm':
		case 'y':
		case 'r':
		case 'w':
			$db->where(
			"(e.corpkana like 'ま%' or 
			e.corpkana like 'み%' or
			e.corpkana like 'む%' or
			e.corpkana like 'め%' or
			e.corpkana like 'も%' or
			e.corpkana like 'や%' or
			e.corpkana like 'ゆ%' or
			e.corpkana like 'よ%' or
			e.corpkana like 'ら%' or
			e.corpkana like 'り%' or
			e.corpkana like 'る%' or
			e.corpkana like 'れ%' or
			e.corpkana like 'ろ%' or
			e.corpkana like 'わ%' or
			e.corpkana like 'を%' or
			e.corpkana like 'ん%')");
			break;
		}
		$db->order_by('e.corpkana');
	}

	public function exhibitors($word='')
	{
		$search_words = '';
		if (in_array(substr($word,0,1),array('a','k','s','t','n','h','m','y','r','w'))) {
			$search_words = $word;
		}
		$this->load->dbutil();
		$db =& $this->db;
		$db->select("e.exhid '出展コード', e.corpname '出展者名', e.corpkana '出展者名カナ'");
		$db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$db->select("e.zip '郵便番号', e.prefecture '都道府県', CONCAT(e.address1,' ',IFNULL(e.address2,'')) '住所'", FALSE);
		$db->select("e.position '役職', e.fullname '代表者氏名', e.fullkana '代表者氏名カナ'");
		$db->select("e.phone 'TEL', e.fax 'FAX', e.url 'URL'");

		$db->select("m.corpname '(責任者)会社名', m.corpkana '(責任者)会社名カナ'");
		$db->select("m.zip '(責任者)郵便番号', m.prefecture '(責任者)都道府県', CONCAT(m.address1,' ',IFNULL(m.address2,'')) '(責任者)住所'", FALSE);
		$db->select("m.division '(責任者)所属', m.position '(責任者)役職', m.fullname '(責任者)氏名', m.fullkana '(責任者)氏名カナ'");
		$db->select("m.phone '(責任者)TEL', m.fax '(責任者)FAX', m.mobile '(責任者)携帯', m.email '(責任者)メールアドレス'");

		$db->select("b.corpname '(請求先)会社名', b.corpkana '(請求先)会社名カナ'");
		$db->select("b.zip '(請求先)郵便番号', b.prefecture '(請求先)都道府県', CONCAT(b.address1,' ',IFNULL(b.address2,'')) '(請求先)住所'", FALSE);
		$db->select("b.division '(請求先)所属', b.position '(請求先)役職', b.fullname '(請求先)氏名', b.fullkana '(請求先)氏名カナ'");
		$db->select("b.phone '(請求先)TEL', b.fax '(請求先)FAX'");

		$db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");

		$db->select("d.corpname '(送付先)会社名', d.corpkana '(送付先)会社名カナ'");
		$db->select("d.zip '(送付先)郵便番号', d.prefecture '(送付先)都道府県', CONCAT(d.address1,' ',IFNULL(d.address2,'')) '(送付先)住所'", FALSE);
		$db->select("d.division '(送付先)所属', d.position '(送付先)役職', d.fullname '(送付先)氏名', d.fullkana '(送付先)氏名カナ'");
		$db->select("d.phone '(送付先)TEL', d.fax '(送付先)FAX'");

		$db->select("e.promotion 'プロモーションコード'");
		$db->select("bo.exhboothno '小間番号'");

		$db->from('exhibitors e');
		$db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$db->join('exhibitor_bill b', 'e.exhid = b.exhid AND b.seqno = 0');
		$db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$db->join('exhibitor_dist d', 'e.exhid = d.exhid');
		$db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
		$db->join('booths bs', 'bs.boothid = bo.boothid');
		$db->where('e.expired', '0');
		$db->where_in('e.statusno', array('500','401','400'));
		$this->wordslike($db, $search_words);
		$query = $db->get();

		if ($query === FALSE) {
			return 'error';
		}

		$this->load->helper('form');
		$result = deco_csv_from_result($query,"\t","\n",'');
		header('Content-Type: text/plain; charset=utf-8');
		echo $result;
		exit;
	}

	public function exhibitor_booths($word='')
	{
		$search_words = '';
		if (in_array(substr($word,0,1),array('a','k','s','t','n','h','m','y','r','w'))) {
			$search_words = $word;
		}
		$this->load->dbutil();
		$db =& $this->db;
        
		$db->select("e.statusno 'ステータス'");
		$db->select("bo.exhboothno '小間番号'");
		$db->select("sp.spaceabbr 'スペース', bs.boothabbr '小間形状'");

		$db->select("e.exhid '出展コード', e.corpname '出展者名', e.corpkana '出展者名カナ'");
		$db->select("e.brandname '表示名', e.brandkana '表示名カナ'");
		$db->select("e.zip '郵便番号', e.prefecture '都道府県', CONCAT(e.address1,' ',IFNULL(e.address2,'')) '住所'", FALSE);
		$db->select("e.position '役職', e.fullname '代表者氏名', e.fullkana '代表者氏名カナ'");
		$db->select("e.phone 'TEL', e.fax 'FAX', e.url 'URL'");

		$db->select("m.corpname '(責任者)会社名', m.corpkana '(責任者)会社名カナ'");
		$db->select("m.zip '(責任者)郵便番号', m.prefecture '(責任者)都道府県', CONCAT(m.address1,' ',IFNULL(m.address2,'')) '(責任者)住所'", FALSE);
		$db->select("m.division '(責任者)所属', m.position '(責任者)役職', m.fullname '(責任者)氏名', m.fullkana '(責任者)氏名カナ'");
		$db->select("m.phone '(責任者)TEL', m.fax '(責任者)FAX', m.mobile '(責任者)携帯', m.email '(責任者)メールアドレス'");

		$db->select("b.corpname '(請求先)会社名', b.corpkana '(請求先)会社名カナ'");
		$db->select("b.zip '(請求先)郵便番号', b.prefecture '(請求先)都道府県', CONCAT(b.address1,' ',IFNULL(b.address2,'')) '(請求先)住所'", FALSE);
		$db->select("b.division '(請求先)所属', b.position '(請求先)役職', b.fullname '(請求先)氏名', b.fullkana '(請求先)氏名カナ'");
		$db->select("b.phone '(請求先)TEL', b.fax '(請求先)FAX'");

		$db->select("c.corpname '(連絡先)会社名', c.corpkana '(連絡先)会社名カナ'");
		$db->select("c.zip '(連絡先)郵便番号', c.prefecture '(連絡先)都道府県', CONCAT(c.address1,' ',IFNULL(c.address2,'')) '(連絡先)住所'", FALSE);
		$db->select("c.division '(連絡先)所属', c.position '(連絡先)役職', c.fullname '(連絡先)氏名', c.fullkana '(連絡先)氏名カナ'");
		$db->select("c.phone '(連絡先)TEL', c.fax '(連絡先)FAX', c.mobile '(連絡先)携帯', c.email '(連絡先)メールアドレス'");

		$db->select("d.corpname '(送付先)会社名', d.corpkana '(送付先)会社名カナ'");
		$db->select("d.zip '(送付先)郵便番号', d.prefecture '(送付先)都道府県', CONCAT(d.address1,' ',IFNULL(d.address2,'')) '(送付先)住所'", FALSE);
		$db->select("d.division '(送付先)所属', d.position '(送付先)役職', d.fullname '(送付先)氏名', d.fullkana '(送付先)氏名カナ'");
		$db->select("d.phone '(送付先)TEL', d.fax '(送付先)FAX'");

		$db->select("e.promotion 'プロモーションコード'");

		$db->from('exhibitors e');
		$db->join('exhibitor_manager m', 'e.exhid = m.exhid');
		$db->join('exhibitor_bill b', 'e.exhid = b.exhid AND b.seqno = 0');
		$db->join('exhibitor_contact c', 'e.exhid = c.exhid');
		$db->join('exhibitor_dist d', 'e.exhid = d.exhid');
		$db->join('exhibitor_booth bo', 'e.exhid = bo.exhid AND bo.expired = 0');
		$db->join('booths bs', 'bs.boothid = bo.boothid');
		$db->join('spaces sp', 'sp.spaceid = bs.spaceid');
		$db->where('e.expired', '0');
		$db->where_in('e.statusno', array('500','401','400'));
		$this->wordslike($db, $search_words);
		$query = $db->get();
		
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		$result = deco_csv_from_result($query,"\t","\n","");
		header('Content-Type: text/plain; charset=utf-8');
		echo $result;
		exit;
	}

	public function e01car()
	{
		$db =& $this->db;
		$db->select("ed.appid '手続番号', ed.appno '手続書類', bo.exhboothno '小間番号'");
		$db->select("e.exhid '出展者コード', e.corpname '出展者名', e.brandname '表示名', ed.brandnameen '表示名英文'");
		$db->select("ed.spec '系番号', CASE ed.spec WHEN 0 THEN 'チューニング' WHEN 1 THEN 'ドレスアップ' WHEN 2 THEN 'コンセプト' END '系登録'", FALSE);
		$db->select("ed.carname '出展車両名', ed.carkana '出展車両名カナ', ed.carnameen '出展車両名英文'");
		$db->select("ed.basecarbrand 'ベース車両メーカー(ブランド)名', ed.basecarname 'ベース車両名'");
		$db->select("ed.basecartype '形式', basecaryear '年式', ed.boothtype 'ブース種別'");
		$db->select("ed.sectionno 'エントリ部門', ed.enableroad '公道走行可'");
		$db->select("IF(ed.enablerace,ed.enablerace,0) '競技専用', IF(ed.prototype,ed.prototype,0) '参考出品'", FALSE);
		$db->select("ed.sales '販売可能', ed.stand 'スペックボードの作成'");
		$db->select("ed.publicdate '公開日'");
		$db->select("REPLACE(ed.concept,'\\n','\\\\n') 'コンセプト', IF(ed.complete>0, 100, ed.progress) AS '完成度'", FALSE);
		$db->select("IF(bo.exhboothno,CONCAT(bo.exhboothno,LPAD(ed.seqno,2,'0')),NULL) '車両番号'", FALSE);
		$db->select("ed.enginetype 'エンジン形式', ed.enginecc '排気量'");
		$db->select("ed.outputnum '出力', ed.outputunit '出力単位', ed.outputrpm '出力rpm'");
		$db->select("ed.torquenum 'トルク', ed.torqueunit 'トルク単位', ed.torquerpm 'トルクrpm'");
		$db->select("REPLACE(ed.enginecomment,'\\n','\\\\n') 'チューニング内容＆使用パーツ'", FALSE);
		$db->select("ed.muffler 'マフラー', ed.manifold 'EXマニホールド', ed.transmission 'ミッション'");
		$db->select("ed.clutch 'クラッチ', ed.differential 'デフ', ed.aeroname 'エアロキット名'");
		$db->select("ed.bodycolor 'ボディカラー'");
		$db->select("REPLACE(ed.dressupcomment,'\\n','\\\\n') 'ドレスアップ内容＆使用パーツ'", FALSE);
		$db->select("ed.sheet 'シート', ed.steering 'ステアリング', ed.meter 'メーター'");
		$db->select("ed.audio 'オーディオ', ed.carnavi 'カーナビ'");
		$db->select("ed.floormat 'フロアマット'");
		$db->select("REPLACE(ed.etc,'\\n','\\\\n') '内装系その他'", FALSE);
		$db->select("ed.suspension 'サスキット名', ed.absorber 'ショック', ed.spring 'スプリング', ed.brake 'ブレーキ'");
		$db->select("REPLACE(ed.suspensioncomment,'\\n','\\\\n') 'サスペンションその他'", FALSE);
		$db->select("ed.wheel 'ホイールメーカー・名称', ed.frontsize 'ホイールサイズ(F)', ed.rearsize 'ホイールサイズ(R)'");
		$db->select("ed.tire 'タイヤメーカー・名称', ed.fronttire 'タイヤサイズ(F)', ed.reartire 'タイヤサイズ(R)'");
		$db->select("ed.maxspeed '最高速', ed.dragspeed 'ドラッグ'");
		$db->select("REPLACE(ed.speedcomment,'\\n','\\\\n') '速度その他'", FALSE);
		$db->select("REPLACE(ed.comment,'\\n','\\\\n') 'その他チューニング'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/car/',ed.photo1) 'photo1'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/car/',ed.photo2) 'photo2'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/car/',ed.photo3) 'photo3'", FALSE);
		$db->select("ed.updated '最終更新日時'");
		$db->select("e.promotion 'プロモーションコード'");

		$db->from('v_exapply_01_detail ed');
		$db->join('exhibitor_booth bo', 'ed.exhboothid = bo.exhboothid');
		$db->join('exhibitors e', 'e.exhid = bo.exhid');
		$db->join('booths bs', 'bs.boothid = bo.boothid');
		$db->where('ed.expired', '0');
		$db->where('bo.expired', '0');
		$db->where('e.expired', '0');
		$db->where_in('e.statusno', array('500','401','400'));
		$query = $db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		$result = deco_csv_from_result($query,"\t","\n",'');
//		$result = mb_convert_kana($result, "KVsa", "UTF-8");
		header('Content-Type: text/plain; charset=utf-8');
		echo $result;
		exit;
	}

	public function e01carex()
	{
		$db =& $this->db;
		$db->select("ed.appid '手続番号', ed.appno '手続書類', bo.exhboothno '小間番号'");
		$db->select("e.exhid '出展者コード', e.corpname '出展者名', e.brandname '表示名', ed.brandnameen '表示名英文'");
		$db->select("ed.spec '系番号', CASE ed.spec WHEN 0 THEN 'チューニング' WHEN 1 THEN 'ドレスアップ' WHEN 2 THEN 'コンセプト' END '系登録'", FALSE);
		$db->select("ed.carname '出展車両名', ed.carkana '出展車両名カナ', ed.carnameen '出展車両名英文'");
		$db->select("ed.basecarbrand 'ベース車両メーカー(ブランド)名', ed.basecarname 'ベース車両名'");
		$db->select("ed.basecartype '形式', basecaryear '年式', ed.boothtype 'ブース種別'");
		$db->select("ed.sectionno 'エントリ部門', ed.enableroad '公道走行可'");
		$db->select("IF(ed.enablerace,ed.enablerace,0) '競技専用', IF(ed.prototype,ed.prototype,0) '参考出品'", FALSE);
		$db->select("ed.sales '販売可能', ed.stand 'スペックボードの作成'");
		$db->select("ed.publicdate '公開日'");
		$db->select("REPLACE(ed.concept,'\\n','\\\\n') 'コンセプト', IF(ed.complete>0, 100, ed.progress) AS '完成度'", FALSE);
		$db->select("IF(bo.exhboothno,CONCAT(bo.exhboothno,LPAD(ed.seqno,2,'0')),NULL) '車両番号'", FALSE);
		$db->select("ed.enginetype 'エンジン形式', ed.enginecc '排気量'");
		$db->select("ed.outputnum '出力', ed.outputunit '出力単位', ed.outputrpm '出力rpm'");
		$db->select("ed.torquenum 'トルク', ed.torqueunit 'トルク単位', ed.torquerpm 'トルクrpm'");
		$db->select("REPLACE(ed.enginecomment,'\\n','\\\\n') 'チューニング内容＆使用パーツ'", FALSE);
		$db->select("ed.muffler 'マフラー', ed.manifold 'EXマニホールド', ed.transmission 'ミッション'");
		$db->select("ed.clutch 'クラッチ', ed.differential 'デフ', ed.aeroname 'エアロキット名'");
		$db->select("ed.bodycolor 'ボディカラー'");
		$db->select("REPLACE(ed.dressupcomment,'\\n','\\\\n') 'ドレスアップ内容＆使用パーツ'", FALSE);
		$db->select("ed.sheet 'シート', ed.steering 'ステアリング', ed.meter 'メーター'");
		$db->select("ed.audio 'オーディオ', ed.carnavi 'カーナビ'");
		$db->select("ed.floormat 'フロアマット'");
		$db->select("REPLACE(ed.etc,'\\n','\\\\n') '内装系その他'", FALSE);
		$db->select("ed.suspension 'サスキット名', ed.absorber 'ショック', ed.spring 'スプリング', ed.brake 'ブレーキ'");
		$db->select("REPLACE(ed.suspensioncomment,'\\n','\\\\n') 'サスペンションその他'", FALSE);
		$db->select("ed.wheel 'ホイールメーカー・名称', ed.frontsize 'ホイールサイズ(F)', ed.rearsize 'ホイールサイズ(R)'");
		$db->select("ed.tire 'タイヤメーカー・名称', ed.fronttire 'タイヤサイズ(F)', ed.reartire 'タイヤサイズ(R)'");
		$db->select("ed.maxspeed '最高速', ed.dragspeed 'ドラッグ'");
		$db->select("REPLACE(ed.speedcomment,'\\n','\\\\n') '速度その他'", FALSE);
		$db->select("REPLACE(ed.comment,'\\n','\\\\n') 'その他チューニング'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/car/',ed.photo1) 'photo1'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/car/',ed.photo2) 'photo2'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/car/',ed.photo3) 'photo3'", FALSE);
		$db->select("ed.updated '最終更新日時'");
		$db->select("e.promotion 'プロモーションコード'");

		$db->from('v_exapply_01_detail ed');
		$db->join('exhibitor_booth bo', 'ed.exhboothid = bo.exhboothid');
		$db->join('exhibitors e', 'e.exhid = bo.exhid');
		$db->join('booths bs', 'bs.boothid = bo.boothid');
		$db->where('ed.expired', '0');
		$db->where('bo.expired', '0');
		$db->where("(`publicdate` IS NULL OR STR_TO_DATE(`publicdate`,'%Y-%m-%d %H:%i:%s') <= CURRENT_TIMESTAMP)", NULL, FALSE);
		$db->where('e.expired', '0');
		$db->where_in('e.statusno', array('500','401','400'));
		$query = $db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		$result = deco_csv_from_result($query,"\t","\n",'');
//		$result = mb_convert_kana($result, "KVsa", "UTF-8");
		header('Content-Type: text/plain; charset=utf-8');
		echo $result;
		exit;
	}

	public function e02publicinfo()
	{
		$db =& $this->db;
		$db->select("ea.appid '手続番号', ea.appno '手続書類', bo.exhboothno '小間番号'");
		$db->select("e.exhid '出展者コード', e.corpname '出展者名', e.brandname '表示名', ea.brandnameen '表示名英文'");
		$db->select('ea.publicaddress 住所公開, ea.publicphone TEL公開, ea.publicfax FAX公開, ea.publicurl URL公開, ea.publicemail メールアドレス公開');
		$db->select("e.countrycode AS '国'");
		$db->select("ea.zip '(公開)郵便番号', IF(e.countrycode='JP',ea.prefecture,'') '(公開)都道府県', ea.address1 '(公開)住所1', ea.address2 '(公開)住所2', ", FALSE);
//		$db->select("ea.zip '(公開)郵便番号', ea.prefecture '(公開)都道府県', ea.address1 '(公開)住所1', ea.address2 '(公開)住所2', ");
		$db->select("ea.phone '(公開)電話番号', ea.fax '(公開)FAX番号', ea.email '(公開)メールアドレス'");
		$db->select("CONCAT('http://',REPLACE(ea.url,'http://','')) '(公開)URL'",FALSE);
		$db->select("REPLACE(ea.prcomment,'\\n','\\\\n') 'PRコメント'", FALSE);
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/publicinfo/',ed.photo) '(公開)画像'", FALSE);
		$db->select("REPLACE(ed.photocomment,'\\n','\\\\n') '(公開)画像コメント'", FALSE);
		$db->select("e.promotion 'プロモーションコード'");

		$db->from('v_exapply_02 ea');
		$db->join('v_exapply_02_detail ed', 'ea.exhboothid = ed.exhboothid', 'left');
		$db->join('exhibitor_booth bo', 'ea.exhboothid = bo.exhboothid');
		$db->join('exhibitors e', 'e.exhid = bo.exhid');
		$db->join('booths bs', 'bs.boothid = bo.boothid');
		$db->where('bo.expired', '0');
		$db->where('e.expired', '0');
		$db->where_in('e.statusno', array('500','401','400'));
		$query = $db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		$result = deco_csv_from_result($query,"\t","\n",'');
//		$result = mb_convert_kana($result, "KVsa", "UTF-8");
		header('Content-Type: text/plain; charset=utf-8');
		echo $result;
		exit;
	}

	public function e03present()
	{
		$db =& $this->db;
		$db->select("ea.appid '手続番号', ea.appno '手続書類', bo.exhboothno '小間番号'");
		$db->select("e.exhid '出展者コード', e.corpname '出展者名', e.brandname '表示名'");
		$db->select("ea.corpname '(協賛)企業名'");
		$db->select("ea.zip '(協賛)郵便番号', ea.prefecture '(協賛)都道府県', ea.address1 '(協賛)住所1', ea.address2 '(協賛)住所2', ");
		$db->select("ea.phone '(協賛)電話番号', ea.fax '(協賛)FAX番号'");
		$db->select("ed.seqno '商品番号', ed.itemname '商品名', ed.itemprice '標準価格', ed.quantity '個数'");
		$db->select("CONCAT('http://archive.tokyoautosalon.jp/2018/photos/present/',ed.itemphoto) '商品画像'", FALSE);
		$db->from('v_exapply_03 ea');
		$db->join('v_exapply_03_detail ed', 'ea.exhboothid = ed.exhboothid', 'left');
		$db->join('exhibitor_booth bo', 'ea.exhboothid = bo.exhboothid');
		$db->join('exhibitors e', 'e.exhid = bo.exhid');
		$db->join('booths bs', 'bs.boothid = bo.boothid');
		$db->where('ed.expired', '0');
		$db->where('bo.expired', '0');
		$db->where('e.expired', '0');
		$db->where_in('e.statusno', array('500','401','400'));
		$query = $db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		$result = deco_csv_from_result($query,"\t","\n","");
//		$result = mb_convert_kana($result, "KVsa", "UTF-8");
		header('Content-Type: text/plain; charset=utf-8');
		echo $result;
		exit;
	}
}
// vim:ts=4
// End of file fy2018.php
