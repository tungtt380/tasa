<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class E01car extends ExhOP_Controller {
	protected $form_appno    = 1;
	protected $form_prefix   = 'e01car';				// フォーム名
	protected $table_name    = 'v_exapply_01_detail';	// テーブル名
	protected $table_prefix  = FALSE;					// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;					// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid';					// テーブルの主キー名
	protected $foreign_token = 'token';					// ２重更新・削除防止のための項目
	protected $sectionarr = array(
		1=>'コンセプトカー',
		2=>'ドレスアップカー',
		3=>'チューニングカー',
		4=>'セダン',
		5=>'ミニバン/ワゴン',
		6=>'SUV',
		7=>'コンパクトカー',
		8=>'インポートカー',
		9=>'参考出品',
	);
	protected $foreign_value = array(					// 入力チェック用に使用するカラムとパターン
		'appid'             => 'trim',
		'exhboothid'        => 'trim',
		'appno'             => 'trim',
		'seqno'             => 'trim',
		'brandname'         => 'trim|xss_clean|required',
		'brandnameen'       => 'trim|xss_clean|required',	// 2015 added
		'carname'           => 'trim|xss_clean|required',
		'carnameen'         => 'trim|xss_clean|required',	// 2015 added
		'carkana'           => 'trim|xss_clean|required',
		'basecarbrand'      => 'trim|xss_clean',			// 2015 added
		'basecarname'       => 'trim|xss_clean',
		'basecartype'       => 'trim|xss_clean',
		'basecaryear'       => 'trim|xss_clean',
		'boothtype'         => 'trim|xss_clean',
		'sectionno'         => 'trim|xss_clean',
		'enableroad'        => 'trim|xss_clean|is_natural|required',
		'enablerace'        => 'trim|xss_clean|is_natural',
		'prototype'         => 'trim|xss_clean|is_natural',
		'stand'             => 'trim|xss_clean|is_natural',
		'reference'         => 'trim|xss_clean|is_natural',
		'referenceprice'    => 'trim|xss_clean|is_natural',
		'concept'           => 'trim|xss_clean',
		'complete'          => 'trim|xss_clean|is_natural',
		'progress'          => 'trim|xss_clean|is_natural',
		'enginetype'        => 'trim|xss_clean',
		'enginecc'          => 'trim|xss_clean|valid_enginecc',
		'outputnum'         => 'trim|xss_clean|numeric',
		'outputunit'        => 'trim',
		'outputrpm'         => 'trim|xss_clean|numeric',
		'torquenum'         => 'trim|xss_clean|numeric',
		'torqueunit'        => 'trim',
		'torquerpm'         => 'trim|xss_clean|numeric',
		'enginecomment'     => 'trim|xss_clean',
		'muffler'           => 'trim|xss_clean',
		'manifold'          => 'trim|xss_clean',
		'transmission'      => 'trim|xss_clean',
		'clutch'            => 'trim|xss_clean',
		'differential'      => 'trim|xss_clean',
		'aeroname'          => 'trim|xss_clean',
		'bodycolor'         => 'trim|xss_clean',
		'dressupcomment'    => 'trim|xss_clean',
		'sheet'             => 'trim|xss_clean',
		'steering'          => 'trim|xss_clean',
		'meter'             => 'trim|xss_clean',
		'audio'             => 'trim|xss_clean',
		'carnavi'           => 'trim|xss_clean',
		'etc'               => 'trim|xss_clean',
		'suspension'        => 'trim|xss_clean',
		'absorber'          => 'trim|xss_clean',
		'spring'            => 'trim|xss_clean',
		'brake'             => 'trim|xss_clean',
		'suspensioncomment' => 'trim|xss_clean',
		'wheel'             => 'trim|xss_clean',
		'frontsize'         => 'trim|xss_clean',
		'rearsize'          => 'trim|xss_clean',
		'tire'              => 'trim|xss_clean',
		'fronttire'         => 'trim|xss_clean',
		'reartire'          => 'trim|xss_clean',
		'maxspeed'          => 'trim|xss_clean|numeric',
		'dragspeed'         => 'trim|xss_clean|numeric',
		'speedcomment'      => 'trim|xss_clean',
		'comment'           => 'trim|xss_clean',
		'photo1'   => 'trim|xss_clean',
/*
		'photo2'   => 'trim|xss_clean',
		'photo3'   => 'trim|xss_clean',
*/
		'spec'     => 'trim|xss_clean',
		'sales'    => 'trim|xss_clean',
		'floormat' => 'trim|xss_clean',
		'publicdate' => 'trim|xss_clean|valid_isodatetime2',
	);
	function __construct() {
		parent::__construct();
		$this->load->model('exhibitors_model');
	}
	protected function setup_data()
	{
	    $data = parent::setup_data();
        $this->config->load('release', FALSE, TRUE);
		$data['e01webonly'] = intval($this->config->item('e01webonly'));
		return $data;
	}
	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('prefecture_model');
		$data['prefecture'] = $this->prefecture_model->get_dropdown(TRUE);
        $this->load->model('category_model');
        $data['category'] = $this->category_model->get_dropdown();
        $this->load->model('section_model');
        $data['section'] = $this->section_model->get_dropdown();
	}
    protected function setup_form_ex(&$data)
    {
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
        // if ($this->member->get_exhid()) {
        //     $exhboothid = $this->member->get_exhboothid();
		// 	$exhid = $this->member->get_exhid();
		if ($this->member_lib->get_exhid()) {
            $exhboothid = $this->member_lib->get_exhboothid();
			$exhid = $this->member_lib->get_exhid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
        } else {
            $exhboothid = $data['foreign']['exhboothid'];
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
        $this->db->where('exhboothid', $data['foreign']['exhboothid']);
        $this->db->where('expired', 0);
        $query = $this->db->get('exhibitor_booth');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['foreign']['exhid'] = $row['exhid'];
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
if (FALSE) {
		// 進捗状況チェック
		if($foreign['progress']>100){
		    $data['message']['__all'] = "進捗状況はは0～100％の範囲で入力して下さい。";
	            return FALSE;
		}
}
		// 画像アップロード処理とエラー処理
		$this->load->library('upload');
		for($i=1;$i<=3;$i++){
			if ($_FILES['photo'.$i]['size']>0) {
				$config = array();
				$config['upload_path'] = APPPATH.'/photos/car/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_width']  = '1600';
				$config['max_height']  = '1200';
				$config['file_name'] = $foreign['exhid'] . '-' . mb_convert_roma($_FILES['photo'.$i]['name']);
				$this->upload->initialize($config);
				if(!$this->upload->do_upload("photo".$i)){
					$data['message']['__all'] = $this->upload->display_errors();
					$result = FALSE;
				}
				$file_data = $this->upload->data();
				log_message('notice', 'UPLOAD:'.var_export($file_data,TRUE));
				$data['pic'][$i]=$file_data['file_name'];
			}else if($data['foreign']['photo'.$i]){
				$data['pic'][$i]=$data['foreign']['photo'.$i];
			}
		}
		$this->session->set_flashdata('pic', $data['pic']);
		if ($result === FALSE) {
			log_message('notice', $data['message']['__all']);
		}
	}
	function get_record_ex(&$data, $uid) {
		$data['foreign'] = $this->exhibitors_model->read($uid);
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function create_record($foreign)
	function create_record(&$foreign)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$foreign['appno'] = 1;
//		if (! isset($foreign['exhboothid']) || $foreign['exhboothid'] == '') {
//			return FALSE;
//		}
		// seqno取得
		$this->db->select('max(seqno) as cnt');
		$this->db->where('exhboothid', $foreign['exhboothid']);
		$query = $this->db->get('v_exapply_01_detail');
		if ($query !== FALSE && $query->num_rows() > 0) {
			$seqno = $query->row_array();
		}
		$foreign['seqno'] = $seqno['cnt'] + 1;
		return parent::create_record($foreign);
	}
	public function images($filename = '')
	{
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('pic');
		$this->session->keep_flashdata('foreign2');
		$this->load->helper('file');
		$path = APPPATH . '/photos/car/';
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
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $exhid = $this->member->get_exhid();
		// $exhboothid = $this->member->get_exhboothid();
		$exhid = $this->member_lib->get_exhid();
		$exhboothid = $this->member_lib->get_exhboothid();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno, b.boothname');
		$this->db->select('e.corpname, e.brandname, s.spaceabbr');
		$this->db->select('vx.expired, v.carname, v.stand, v.spec');
        $this->db->select('v.appid, v.created, v.updated');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_01 vx', 'vx.exhboothid = eb.exhboothid', 'left');
        $this->db->join('v_exapply_01_detail v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));
        if (uri_folder_string() =='/ex') {
	        $this->db->where('eb.exhboothid', $exhboothid);
        } else {
//	        $this->db->where_not_in('s.spaceabbr', array('A'));
		}

		// Upgrade CI3 - Add ORDER BY clause to get same result as CI2 - Start by TTM
		$this->db->order_by('v.carname, v.stand, v.spec, v.appid, v.created, v.updated ASC');
		// Upgrade CI3 - Add ORDER BY clause to get same result as CI2 - End by TTM

		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		} else {
			$data['lists'] = array();
		}
		// 小間ごとにまとめる
		$a=0;
		$before_exhboothid='';
		foreach($data['lists'] as $lists_key=>$lists_data){
			if($before_exhboothid != $lists_data['exhboothid']){
				$a=0;
				$data['lists2'][$lists_data['exhboothid']]=$data['lists'][$lists_key];
				$data['lists2'][$lists_data['exhboothid']]['carnames'][$a]=$data['lists'][$lists_key]['carname'];
				$data['lists2'][$lists_data['exhboothid']]['stands'][$a]=$data['lists'][$lists_key]['stand'];
				$data['lists2'][$lists_data['exhboothid']]['specs'][$a]=$data['lists'][$lists_key]['spec'];
				$data['lists2'][$lists_data['exhboothid']]['appids'][$a]=$data['lists'][$lists_key]['appid'];
				$a++;
			}else{
				$data['lists2'][$lists_data['exhboothid']]['carnames'][$a]=$data['lists'][$lists_key]['carname'];
				$data['lists2'][$lists_data['exhboothid']]['stands'][$a]=$data['lists'][$lists_key]['stand'];
				$data['lists2'][$lists_data['exhboothid']]['specs'][$a]=$data['lists'][$lists_key]['spec'];
				$data['lists2'][$lists_data['exhboothid']]['appids'][$a]=$data['lists'][$lists_key]['appid'];
				$a++;
			}
			$before_exhboothid = $lists_data['exhboothid'];
		}
log_message('error', var_export($data,true));
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }
	public function confirm()
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $this->session->keep_flashdata('foreign');
        $this->setup_form_ex($data);
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	public function confirm_in()
	{
		$this->check_action();
        $data = $this->setup_data();
        $data['foreign'] = $this->session->flashdata('foreign');
        $this->session->keep_flashdata('foreign');
        // データの作成
        $this->db->set('exhboothid', $data['foreign']['exhboothid'])
                 ->set('appno', 1)
                 ->set('seqno', 0)
                 ->set('expired', 1)
                 ->set('created', 'CURRENT_TIMESTAMP', FALSE);
        $this->db->insert('v_exapply_01');
        // 結果
        if ($this->db->affected_rows() <= 0) {
            $result = FALSE;
        } else {
            $result = isset($foreign[$this->foreign_keyid]) ? $foreign[$this->foreign_keyid]:$this->db->insert_id();
        }
        if ($result !== FALSE) {
            $line = $this->lang->line('LOG:M2001');
            log_message('notice', sprintf($line, $this->table_name, $result));
            log_message('info', $this->db->last_query());
        } else {
            $line = $this->lang->line('LOG:N4001');
            log_message('notice', sprintf($line, $this->table_name));
            log_message('info', $this->db->last_query());
        }
        // データベースに登録
        $line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
        $message = explode("\n", $line);
        $this->session->set_flashdata('message', $message);
        $this->session->set_flashdata('result', ($result !== FALSE) ? '1':'0');
        if ($result !== FALSE) {
            $this->log_history('登録', $result);
            $this->after_regist($data);
        }
        // 登録完了画面へ
        redirect(uri_redirect_string());
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function create($exhid, $boothid, $spec)
	public function create($exhid = null, $boothid = null, $spec = null)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$data['foreign']['exhid'] = $exhid;
		$data['foreign']['exhboothid'] = $boothid;
		$data['foreign']['spec'] = $spec;
		$this->session->set_flashdata('foreign', $data['foreign']);
		if ($spec == '9') {
			redirect(uri_redirect_string() . '/../../../confirm');
		}
        redirect(uri_redirect_string() . '/../../../regist');
	}
	public function regist()
	{
	    $data = $this->setup_data();
        $this->setup_form($data);
       	$data['foreign'] = $this->session->flashdata('foreign');
       	$data['message'] = $this->session->flashdata('message');
	    $this->session->keep_flashdata('foreign');
		if (!isset($data['foreign']['exhid'])) {
			redirect(uri_redirect_string() . '/');
		}
		// 2015:公道走行についてのデフォルトを可に
		if( ! isset($data['foreign']['endableroad']) || $data['foreign']['endableroad'] === '') {
//			$data['foreign']['enableroad'] = 1;
		}
		// 2015:スペックボード作成についてのデフォルトを可に
		if( ! isset($data['foreign']['stand']) || $data['foreign']['stand'] === '') {
			$data['foreign']['stand'] = $data['e01webonly'] ? 0:1;
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
		if (isset($data['pic'][1])) $data['foreign']['photo1'] = $data['pic'][1];
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
		// データ呼び出し
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['message'] = $this->session->flashdata('message');
		$data['foreign'] = $this->session->flashdata('foreign');
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->keep_flashdata('foreign');
		} else {
			$this->get_record($data, $uid);
			$this->session->set_flashdata('foreign', $data['foreign']);
		}
		$this->setup_form_ex($data);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}
	function change_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$data['pic'] = $this->session->flashdata('pic');
		$this->session->keep_flashdata('pic');
		if (isset($data['pic'][1])) $data['foreign']['photo1'] = $data['pic'][1];
/*
		if (isset($data['pic'][2])) $data['foreign']['photo2'] = $data['pic'][2];
		if (isset($data['pic'][3])) $data['foreign']['photo3'] = $data['pic'][3];
*/
		$this->session->set_flashdata('foreign', $data['foreign']);
		$this->setup_form_ex($data);
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
		$this->session->keep_flashdata('foreign');
		$data['pic'] = $this->session->flashdata('pic');
		$this->session->keep_flashdata('pic');
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	function delete($uid='')
	{
		// データ呼び出し
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		$this->session->set_flashdata('foreign', $data['foreign']);
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
	public function preview_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');
		$data['pic'] = $this->session->flashdata('pic');
		$this->session->keep_flashdata('pic');
		$this->setup_form_ex($data);
        $this->db->select('eb.exhboothno, e.corpname, e.brandname');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->where('eb.expired', '0');
        $this->db->where('eb.exhboothid', $data['foreign']['exhboothid']);
        $query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$record = $query->row_array();
			$data['foreign']['exhboothno'] = $record['exhboothno'];
			$data['foreign']['corpname'] = $record['corpname'];
		}
		if ($data['foreign']['spec'] == 0) {
			$this->preview_tuning($data);
		} else if ($data['foreign']['spec'] == 1) {
			$this->preview_dressup($data);
		} else if ($data['foreign']['spec'] == 2) {
			$this->preview_concept($data);
		}
	}
	public function download_specboard()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno');
        $this->db->select('e.corpname, e.brandname, e.promotion, s.spaceabbr');
		$this->db->select('v.seqno, v.carname, v.carkana, v.basecarname, v.basecartype, v.basecaryear');
		$this->db->select('v.boothtype, v.sectionno');
		$this->db->select('v.enableroad, v.enablerace, v.prototype, v.stand');
		$this->db->select('v.concept, v.complete, v.progress, v.carno');
		$this->db->select('v.enginetype, v.enginecc, v.outputnum, v.outputunit, v.outputrpm');
		$this->db->select('v.torquenum, v.torqueunit, v.torquerpm, v.enginecomment');
		$this->db->select('v.muffler, v.manifold, v.transmission, v.clutch, v.differential');
		$this->db->select('v.aeroname, v.bodycolor, v.dressupcomment');
		$this->db->select('v.sheet, v.steering, v.meter');
		$this->db->select('v.audio, v.carnavi, v.etc');
		$this->db->select('v.suspension, v.absorber, v.spring, v.brake');
		$this->db->select('v.wheel, v.frontsize, v.rearsize, v.tire, v.fronttire, v.reartire');
		$this->db->select('v.maxspeed, v.dragspeed, v.speedcomment, v.comment');
		$this->db->select('v.photo1');
		$this->db->select('v.suspensioncomment, v.spec, v.sales, v.floormat, v.publicdate, v.updated');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_01_detail v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where('v.stand !=', '0');
        $query = $this->db->get();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $record = $query->result_array();
        } else {
            $record = array();
        }
		// PDFの準備
		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
		// $this->load->library('pdf');
		$this->load->library('Pdf_lib');
		// Upgrade PHP7 - Rename class to make it loadable - End by TTM
        $this->load->library('zip');
        ini_set('memory_limit', '5120M');
		foreach($record as $foreign)
		{
	        if ($foreign['exhboothno'] != '' && $foreign['promotion'] == '') {
	            $number = $foreign['exhboothno'] . str_pad($foreign['seqno'], '2', '0', STR_PAD_LEFT);
	        } else {
	            $number = $foreign['seqno'];
	        }
			$data['foreign'] = $foreign;
			$filename = 'e01car-'.$data['foreign']['exhid'].'-'.$number.'.pdf';
			$updated = FALSE;
			if (file_exists(APPPATH.'/userdata/specboard/'.$filename)) {
				// 2013-11-21 20:39:26
				$filetime = date('Y-m-d H:i:s', filemtime(APPPATH.'/userdata/specboard/'.$filename));
				if ($filetime < $data['foreign']['updated']) {
					$updated = TRUE;
				}
			} else {
				$updated = TRUE;
			}
if ($updated) {
			$pdf = new FPDI_EX(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A3', true, 'UTF-8', false);
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(12, 16, 12);
			$pdf->setPrintHeader(FALSE);
			$pdf->setPrintFooter(FALSE);
			$pdf->SetHeaderMargin(2);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetAutoPageBreak(TRUE, 10);
			$pdf->SetDrawColor(32, 32, 32);
			$pdf->SetFillColor(128,128,128);
			if ($data['foreign']['spec'] == 0) {
				$this->preview_tuning($data, $pdf);
			} else if ($data['foreign']['spec'] == 1) {
				$this->preview_dressup($data, $pdf);
			} else if ($data['foreign']['spec'] == 2) {
				$this->preview_concept($data, $pdf);
			}
			$pdf->Output(APPPATH.'/userdata/specboard/'.$filename, 'F');
			unset($pdf);
}
			$this->zip->read_file(APPPATH.'/userdata/specboard/'.$filename);
			set_time_limit(30);
		}
		// ZIPのダウンロード
		$ymd = date('YmdHi');
		$this->zip->archive(APPPATH.'/userdata/specboard/'.'e01car_specboard_'.$ymd.'.zip');
		$this->load->helper('download');
		force_download('e01car_specboard_'.$ymd.'.zip', file_get_contents(APPPATH.'/userdata/specboard/'.'e01car_specboard_'.$ymd.'.zip'));
//		$this->zip->download('e01car_specboard_'.date('YmdHi').'.zip');
	}
	public function download_preview()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno');
        $this->db->select('e.corpname, e.brandname, e.promotion, s.spaceabbr');
		$this->db->select('v.seqno, v.carname, v.carkana, v.basecarname, v.basecartype, v.basecaryear');
		$this->db->select('v.boothtype, v.sectionno');
		$this->db->select('v.enableroad, v.enablerace, v.prototype, v.stand');
		$this->db->select('v.concept, v.complete, v.progress, v.carno');
		$this->db->select('v.enginetype, v.enginecc, v.outputnum, v.outputunit, v.outputrpm');
		$this->db->select('v.torquenum, v.torqueunit, v.torquerpm, v.enginecomment');
		$this->db->select('v.muffler, v.manifold, v.transmission, v.clutch, v.differential');
		$this->db->select('v.aeroname, v.bodycolor, v.dressupcomment');
		$this->db->select('v.sheet, v.steering, v.meter');
		$this->db->select('v.audio, v.carnavi, v.etc');
		$this->db->select('v.suspension, v.absorber, v.spring, v.brake');
		$this->db->select('v.wheel, v.frontsize, v.rearsize, v.tire, v.fronttire, v.reartire');
		$this->db->select('v.maxspeed, v.dragspeed, v.speedcomment, v.comment');
//		$this->db->select('v.photo1, v.photo2, v.photo3, v.photo4, v.photo5');
		$this->db->select('v.photo1');
		$this->db->select('v.suspensioncomment, v.spec, v.sales, v.floormat, v.publicdate');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_01_detail v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where('v.stand !=', '0');
        $query = $this->db->get();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $record = $query->result_array();
        } else {
            $record = array();
        }
		// PDFの準備
		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
		// $this->load->library('pdf');
		$this->load->library('Pdf_lib');
		// Upgrade PHP7 - Rename class to make it loadable - End by TTM
		$pdf = new FPDI_EX(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A3', true, 'UTF-8', false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(12, 16, 12);
		$pdf->setPrintHeader(FALSE);
		$pdf->setPrintFooter(FALSE);
		$pdf->SetHeaderMargin(2);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->SetDrawColor(32, 32, 32);
		$pdf->SetFillColor(128,128,128);
		foreach($record as $foreign) {
			$data['foreign'] = $foreign;
			if ($data['foreign']['spec'] == 0) {
				$this->preview_tuning($data, $pdf);
			} else if ($data['foreign']['spec'] == 1) {
				$this->preview_dressup($data, $pdf);
			} else if ($data['foreign']['spec'] == 2) {
				$this->preview_concept($data, $pdf);
			}
		}
		// PDFのダウンロード
		header('Content-Type: application/pdf');
		header('Cache-Control: max-age=0');
		$pdf->Output('e01car-'.date('YmdHi').'-all.pdf', 'D');
	}
	public function specboard($appid,$style='concept')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno');
        $this->db->select('e.corpname, e.brandname, e.promotion, s.spaceabbr');
        $this->db->select('v.seqno, v.carname, v.carkana, v.basecarname, v.basecartype, v.basecaryear');
        $this->db->select('v.boothtype, v.sectionno');
        $this->db->select('v.enableroad, v.enablerace, v.prototype, v.stand');
        $this->db->select('v.concept, v.complete, v.progress, v.carno');
        $this->db->select('v.enginetype, v.enginecc, v.outputnum, v.outputunit, v.outputrpm');
        $this->db->select('v.torquenum, v.torqueunit, v.torquerpm, v.enginecomment');
        $this->db->select('v.muffler, v.manifold, v.transmission, v.clutch, v.differential');
        $this->db->select('v.aeroname, v.bodycolor, v.dressupcomment');
        $this->db->select('v.sheet, v.steering, v.meter');
        $this->db->select('v.audio, v.carnavi, v.etc');
        $this->db->select('v.suspension, v.absorber, v.spring, v.brake');
        $this->db->select('v.wheel, v.frontsize, v.rearsize, v.tire, v.fronttire, v.reartire');
        $this->db->select('v.maxspeed, v.dragspeed, v.speedcomment, v.comment');
//      $this->db->select('v.photo1, v.photo2, v.photo3, v.photo4, v.photo5');
        $this->db->select('v.photo1');
        $this->db->select('v.suspensioncomment, v.spec, v.sales, v.floormat, v.publicdate');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_01_detail v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where('v.appid', $appid);
        $query = $this->db->get();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $data['foreign'] = $query->row_array();
        } else {
            $data['foreign'] = array();
        }
		// PDFの準備
		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
		// $this->load->library('pdf');
		$this->load->library('Pdf_lib');
		// Upgrade PHP7 - Rename class to make it loadable - End by TTM
		$pdf = new FPDI_EX(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A3', true, 'UTF-8', false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(12, 16, 12);
		$pdf->setPrintHeader(FALSE);
		$pdf->setPrintFooter(FALSE);
		$pdf->SetHeaderMargin(2);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->SetDrawColor(32, 32, 32);
		$pdf->SetFillColor(128,128,128);
		if ($style=='tuning') {
			$this->preview_tuning($data, $pdf);
		} else if ($style == 'dressup') {
			$this->preview_dressup($data, $pdf);
		} else if ($style == 'concept') {
			$this->preview_concept($data, $pdf);
		}
		// PDFのダウンロード
		header('Content-Type: application/pdf');
		header('Cache-Control: max-age=0');
		$pdf->Output('e01car-'.date('YmdHi').'-'.$data['foreign']['exhid'].'-'.$data['foreign']['exhboothno'].'.pdf', 'D');
	}
	public function preview($appid,$style='concept')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
        // 出展者小間(eb)+出展者(e)をベースに申請書類一覧を構築
        $this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno');
        $this->db->select('e.corpname, e.brandname, e.promotion, s.spaceabbr');
        $this->db->select('v.seqno, v.carname, v.carkana, v.basecarname, v.basecartype, v.basecaryear');
        $this->db->select('v.boothtype, v.sectionno');
        $this->db->select('v.enableroad, v.enablerace, v.prototype, v.stand');
        $this->db->select('v.concept, v.complete, v.progress, v.carno');
        $this->db->select('v.enginetype, v.enginecc, v.outputnum, v.outputunit, v.outputrpm');
        $this->db->select('v.torquenum, v.torqueunit, v.torquerpm, v.enginecomment');
        $this->db->select('v.muffler, v.manifold, v.transmission, v.clutch, v.differential');
        $this->db->select('v.aeroname, v.bodycolor, v.dressupcomment');
        $this->db->select('v.sheet, v.steering, v.meter');
        $this->db->select('v.audio, v.carnavi, v.etc');
        $this->db->select('v.suspension, v.absorber, v.spring, v.brake');
        $this->db->select('v.wheel, v.frontsize, v.rearsize, v.tire, v.fronttire, v.reartire');
        $this->db->select('v.maxspeed, v.dragspeed, v.speedcomment, v.comment');
//      $this->db->select('v.photo1, v.photo2, v.photo3, v.photo4, v.photo5');
        $this->db->select('v.photo1');
        $this->db->select('v.suspensioncomment, v.spec, v.sales, v.floormat, v.publicdate');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_01_detail v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where('v.appid', $appid);
        $query = $this->db->get();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $data['foreign'] = $query->row_array();
        } else {
            $data['foreign'] = array();
        }
		// PDFの準備
		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
		// $this->load->library('pdf');
		$this->load->library('Pdf_lib');
		// Upgrade PHP7 - Rename class to make it loadable - End by TTM
		$pdf = new FPDI_EX(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A3', true, 'UTF-8', false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(12, 16, 12);
		$pdf->setPrintHeader(FALSE);
		$pdf->setPrintFooter(FALSE);
		$pdf->SetHeaderMargin(2);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->SetDrawColor(32, 32, 32);
		$pdf->SetFillColor(128,128,128);
		if ($style=='tuning') {
			$this->preview_tuning($data, $pdf);
		} else if ($style == 'dressup') {
			$this->preview_dressup($data, $pdf);
		} else if ($style == 'concept') {
			$this->preview_concept($data, $pdf);
		}
		// PDFのダウンロード
		header('Content-Type: application/pdf');
		header('Cache-Control: max-age=0');
		$pdf->Output();
	}
	protected function preview_tuning($data, &$pdf)
	{
		$baseY = 8;
		$b = '0';
		$line = 8;
		$foreign = $data['foreign'];
		foreach($foreign as $key=>$val) {
			$val = mb_convert_kana($val, "KVsa", "UTF-8");
			$foreign[$key] = str_replace("\n", " ", $val);
		}
		// 投票番号が存在するのは、プロモーションコードが存在しないとき＆小間番号が存在する時
		if ($foreign['exhboothno'] != '' && $foreign['promotion'] == '') {
			$number = $foreign['exhboothno'] . str_pad($foreign['seqno'], '2', '0', STR_PAD_LEFT);
		} else {
			$number = '';
		}
		$enableroad = $foreign['enableroad'] ? '公道走行可':'公道走行不可';
		$sales = $foreign['sales'] ? '販売可':'';
		$sectionstr = isset($this->sectionarr[$foreign['sectionno']]) ? $this->sectionarr[$foreign['sectionno']]:'';
		$sectionsubstr = ($foreign['sectionno'] == 9) ? '':'部門';
		// ページを追加して、テンプレートを設定
		$pdf->AddPage();
		$pdf->setSourceFile(APPPATH . '/views/pdf/2020tuning.pdf');
		$tpl = $pdf->importPage(1);
		$pdf->useTemplate($tpl);
		$pdf->SetFont('kozgopromedium', 'B', 18);
		$pdf->SetTextColor(255);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0.127);
		$pdf->SetFillColor(128, 128, 128);
        $pdf->Rect(242, $line-1, 40, 10, 'F', null);
        $pdf->MultiCell(40, 8, $enableroad, $b, 'C', 0, 1, 242, $line);
        if ($sales != '') {
            $pdf->Rect(200, $line-1, 40, 10, 'F', null);
            $pdf->MultiCell(40, 8, $sales, $b, 'C', 0, 1, 200, $line);
        }
		// タイトル車名
		$pdf->SetFont('kozgoproheavy', '', 40);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0);
		$pdf->MultiCellEx(220, 18, $foreign['carname'], $b, 'L', 0, 1, 72, $baseY+10); // 2013 is 16
		// 投票番号(29.76)
/*
//TAS2016だけのパッチ当て
if ($number == '113101') {
	if (substr($foreign['carname'],0,2) == 'R3') {
		$number = '113102';
	} else if (substr($foreign['carname'],0,2) == 'CR') {
		$number = '113104';
	} else if (substr($foreign['carname'],0,2) != 'GT') {
		$number = '113105';
	} else if (substr($foreign['carname'],0,2) != 'EN') {
		$number = '113103';
	}
}
*/
		$pdf->SetFont('helvetica', 'B', 40);
		$pdf->SetTextColor(220,20,60);
		$pdf->SetFontStretching(strlen($number) == 5 ? 70:60);
		$pdf->SetFontSpacing(0.127);
		$pdf->MultiCell(33, 0, $number, $b, 'C', 0, 1, 28.2, $baseY+31); // 2013 is 35
		$fw = 235;
		$hw = 115;
		$qw = 60;
		$line = 46; // 2013 is 49.5;
		// 出展者名
		$pdf->SetFont('kozgopromedium', 'B', 18);
		$pdf->SetTextColor(0);
		$pdf->SetFontStretching(80);
		$pdf->MultiCell(120, 8, $foreign['brandname'], $b, 'L', 0, 1, 66, $line+0.5);
		$pdf->MultiCell(60, 8, $sectionstr.$sectionsubstr, $b, 'J', 0, 1, 186, $line+0.5);
		$pdf->MultiCell(30, 8, $foreign['exhboothno'], $b, 'L', 0, 1, 251, $line+0.5);
		// コンセプト
		$pdf->SetFont('kozgopromedium', 'B', 17);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0.5);
		$pdf->MultiCellEx($fw, 18, $foreign['concept'], $b, 'L', 0, 1, 33, $line+20);
		// 車名
		$pdf->SetFont('kozgopromedium', 'B', 17);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0.5);
		$pdf->MultiCellEx($hw, 8, $foreign['basecarname'], $b, 'L', 0, 1, 33, $line+50);
		$pdf->MultiCellEx(70, 8, $foreign['basecartype'], $b, 'L', 0, 1, 152, $line+50);
		$pdf->MultiCellEx(40, 8, $foreign['basecaryear'], $b, 'L', 0, 1, 229, $line+50);
		// エンジン
		$pdf->MultiCellEx($hw, 8, $foreign['enginetype'], $b, 'L', 0, 1, 33, $line+70);
		$pdf->MultiCellEx(25, 8, $foreign['enginecc'], $b, 'R', 0, 1, 142, $line+70);
		if ($foreign['outputnum'] != '') {
			$pdf->SetTextColor(128,128,128);
			$pdf->SetFontStretching(100);
			$pdf->SetFont('kozgopromedium', 'B', 8.5);
			$pdf->MultiCellEx(25, 8, ($foreign['outputunit'] == 2 ? 'ps':'kw'), $b, 'R', 0, 1, 171.25-0.75, $line+74.5);
			$pdf->SetFont('kozgopromedium', 'B', 17);
			$pdf->SetTextColor(0);
			$pdf->SetFontStretching(80);
		}
		$pdf->MultiCellEx(25, 8, $foreign['outputnum'], $b, 'R', 0, 1, 164, $line+70);
		$pdf->MultiCellEx(25, 8, $foreign['outputrpm'], $b, 'R', 0, 1, 187, $line+70);
		if ($foreign['torquenum'] != '') {
			$pdf->SetTextColor(128,128,128);
			$pdf->SetFontStretching(100);
			$pdf->SetFont('kozgopromedium', 'B', 8.5);
			$pdf->MultiCellEx(25, 8, ($foreign['torqueunit'] == 2 ? 'Nm':'kg'), $b, 'R', 0, 1, 218-0.75, $line+74.5);
			$pdf->SetFont('kozgopromedium', 'B', 17);
			$pdf->SetTextColor(0);
			$pdf->SetFontStretching(80);
		}
		$pdf->MultiCellEx(25, 8, $foreign['torquenum'], $b, 'R', 0, 1, 211, $line+70);
		$pdf->MultiCellEx(25, 8, $foreign['torquerpm'], $b, 'R', 0, 1, 233, $line+70);
		$pdf->MultiCellEx($fw, 18, $foreign['enginecomment'], $b, 'L', 0, 1, 33, $line+80);
		// 排気系
		$pdf->MultiCellEx($fw, 8, $foreign['muffler'], $b, 'L', 0, 1, 33, $line+110);
		$pdf->MultiCellEx($fw, 8, $foreign['manifold'], $b, 'L', 0, 1, 33, $line+120);
		// 伝動系
		$pdf->MultiCellEx($hw, 8, $foreign['transmission'], $b, 'L', 0, 1, 33, $line+140);
		$pdf->MultiCellEx($hw, 8, $foreign['clutch'], $b, 'L', 0, 1, 152, $line+140);
		$pdf->MultiCellEx($fw, 8, $foreign['differential'], $b, 'L', 0, 1, 33, $line+150);
		// その他チューニング(1行)
		$pdf->MultiCellEx($fw, 8, $foreign['comment'], $b, 'L', 0, 1, 33, $line+170);
		// 外装関係
		$pdf->MultiCellEx($hw, 8, $foreign['aeroname'], $b, 'L', 0, 1, 33, $line+190);
		$pdf->MultiCellEx($hw, 8, $foreign['bodycolor'], $b, 'L', 0, 1, 152, $line+190);
		$pdf->MultiCellEx($fw, 8, $foreign['dressupcomment'], $b, 'L', 0, 1, 33, $line+200);
		// 内装関係
		$pdf->MultiCellEx($hw, 8, $foreign['sheet'], $b, 'L', 0, 1, 33, $line+220);
		$pdf->MultiCellEx($hw, 8, $foreign['steering'], $b, 'L', 0, 1, 152, $line+220);
		$pdf->MultiCellEx($fw, 8, $foreign['meter'], $b, 'L', 0, 1, 33, $line+230);
		$pdf->MultiCellEx($hw, 8, $foreign['audio'], $b, 'L', 0, 1, 33, $line+240);
		$pdf->MultiCellEx($hw, 8, $foreign['carnavi'], $b, 'L', 0, 1, 152, $line+240);
		$pdf->MultiCellEx($fw, 8, $foreign['etc'], $b, 'L', 0, 1, 33, $line+250);
		// サスペンション
		$pdf->MultiCellEx($fw, 8, $foreign['suspension'], $b, 'L', 0, 1, 33, $line+270);
		$pdf->MultiCellEx($hw, 8, $foreign['absorber'], $b, 'L', 0, 1, 33, $line+280);
		$pdf->MultiCellEx($hw, 8, $foreign['spring'], $b, 'L', 0, 1, 152, $line+280);
		$pdf->MultiCellEx($fw, 8, $foreign['brake'], $b, 'L', 0, 1, 33, $line+290);
		// ホイール
		$pdf->MultiCellEx($hw, 8, $foreign['wheel'], $b, 'L', 0, 1, 33, $line+310);
		$pdf->MultiCellEx($qw, 8, $foreign['frontsize'], $b, 'L', 0, 1, 152, $line+310);
		$pdf->MultiCellEx($qw, 8, $foreign['rearsize'], $b, 'L', 0, 1, 212, $line+310);
		// タイヤ
		$pdf->MultiCellEx($hw, 8, $foreign['tire'], $b, 'L', 0, 1, 33, $line+330);
		$pdf->MultiCellEx($qw, 8, $foreign['fronttire'], $b, 'L', 0, 1, 152, $line+330);
		$pdf->MultiCellEx($qw, 8, $foreign['reartire'], $b, 'L', 0, 1, 212, $line+330);
		// 速度
		$pdf->MultiCellEx(30, 6, $foreign['maxspeed'], $b, 'R', 0, 1, 43, $line+350);
		$pdf->MultiCellEx(30, 6, $foreign['dragspeed'], $b, 'R', 0, 1, 107, $line+350);
		$pdf->MultiCellEx($hw, 8, $foreign['speedcomment'], $b, 'L', 0, 1, 164, $line+350);
		$pdf->lastPage();
	}
	protected function preview_dressup($data, &$pdf)
	{
		$b = '0';
		$spacing = 0.127;
		$foreign = $data['foreign'];
		foreach($foreign as $key=>$val) {
			$val = mb_convert_kana($val, "KVsa", "UTF-8");
			$foreign[$key] = str_replace("\n", " ", $val);
		}
		// 投票番号が存在するのは、プロモーションコードが存在しないとき＆小間番号が存在する時
		if ($foreign['exhboothno'] != '' && $foreign['promotion'] == '') {
			$number = $foreign['exhboothno'] . str_pad($foreign['seqno'], '2', '0', STR_PAD_LEFT);
		} else {
			$number = '';
		}
		$enableroad = $foreign['enableroad'] ? '公道走行可':'公道走行不可';
		$sales = $foreign['sales'] ? '販売可':'';
		$sectionstr = isset($this->sectionarr[$foreign['sectionno']]) ? $this->sectionarr[$foreign['sectionno']]:'';
		$sectionsubstr = ($foreign['sectionno'] == 9) ? '':'部門';
		$line = 8;
		// ページを追加して、テンプレートを設定
		$pdf->AddPage();
		$pdf->setSourceFile(APPPATH . '/views/pdf/2020dressup.pdf');
		$tpl = $pdf->importPage(1);
		$pdf->useTemplate($tpl);
		$pdf->SetFont('kozgopromedium', 'B', 18);
		$pdf->SetTextColor(255);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing($spacing);
		$pdf->SetFillColor(128, 128, 128);
		$pdf->Rect(242, $line-1, 40, 10, 'F', null);
		$pdf->MultiCell(40, 8, $enableroad, $b, 'C', 0, 1, 242, $line);
		if ($sales != '') {
			$pdf->Rect(200, $line-1, 40, 10, 'F', null);
			$pdf->MultiCell(40, 8, $sales, $b, 'C', 0, 1, 200, $line);
		}
		$pdf->SetFont('kozgoproheavy', '', 40);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0);
		$pdf->MultiCellEx(220, 18, $foreign['carname'], $b, 'L', 0, 1, 70, $line+10);
		$pdf->SetFont('helvetica', 'B', 40);
		$pdf->SetFontStretching(strlen($number) == 5 ? 70:60);
		$pdf->SetTextColor(220,20,60);
		$pdf->SetFontSpacing(0.127);
		$pdf->MultiCell(33, 0, $number, $b, 'C', 0, 1, 28.2, $line+31);
		$fw = 235;
		$hw = 115;
		$qw = 55;
		$line = 46; // 2013 is 50
		// 出展者名
		$pdf->SetFont('kozgopromedium', 'B', 20);
		$pdf->SetFontStretching(70);
		$pdf->SetTextColor(0);
		$pdf->MultiCell(120, 8, $foreign['brandname'], $b, 'L', 0, 1, 66, $line);
		$pdf->MultiCell(80, 8, $sectionstr.$sectionsubstr, $b, 'L', 0, 1, 186, $line);
		$pdf->MultiCell(30, 8, $foreign['exhboothno'], $b, 'L', 0, 1, 250, $line);
		// コンセプト
		$pdf->SetFont('kozgopromedium', 'B', 17);
		$pdf->SetFontStretching(80);
		$pdf->MultiCellEx($fw, 28, $foreign['concept'], $b, 'L', 0, 1, 33, $line+19.5);
		// ベース
		$pdf->MultiCellEx($hw, 8, $foreign['basecarname'], $b, 'L', 0, 1, 33, $line+60);
		$pdf->MultiCellEx(70, 8, $foreign['basecartype'], $b, 'L', 0, 1, 156, $line+60);
		$pdf->MultiCellEx(40, 8, $foreign['basecaryear'], $b, 'L', 0, 1, 233, $line+60);
		// 外装関係
		$pdf->MultiCellEx($hw, 8, $foreign['aeroname'], $b, 'L', 0, 1, 33, $line+80);
		$pdf->MultiCellEx($hw, 8, $foreign['bodycolor'], $b, 'L', 0, 1, 156, $line+80);
		$pdf->MultiCellEx($fw, 18, $foreign['dressupcomment'], $b, 'L', 0, 1, 33, $line+90);
		// 内装関係
		$pdf->MultiCellEx($hw, 8, $foreign['sheet'], $b, 'L', 0, 1, 33, $line+120);
		$pdf->MultiCellEx($hw, 8, $foreign['steering'], $b, 'L', 0, 1, 156, $line+120);
		$pdf->MultiCellEx($hw, 8, $foreign['meter'], $b, 'L', 0, 1, 33, $line+130);
		$pdf->MultiCellEx($hw, 8, $foreign['floormat'], $b, 'L', 0, 1, 156, $line+130);
		$pdf->MultiCellEx($hw, 8, $foreign['audio'], $b, 'L', 0, 1, 33, $line+140);
		$pdf->MultiCellEx($hw, 8, $foreign['carnavi'], $b, 'L', 0, 1, 156, $line+140);
		$pdf->MultiCellEx($fw, 38, $foreign['etc'], $b, 'L', 0, 1, 33, $line+150);
		// サスペンション
		$line = 245.5;
		$pdf->MultiCellEx($fw, 8, $foreign['suspension'], $b, 'L', 0, 1, 33, $line);
		$pdf->MultiCellEx($hw, 8, $foreign['absorber'], $b, 'L', 0, 1, 33, $line+10);
		$pdf->MultiCellEx($hw, 8, $foreign['spring'], $b, 'L', 0, 1, 156, $line+10);
		$pdf->MultiCellEx($hw, 8, $foreign['brake'], $b, 'L', 0, 1, 33, $line+20);
		$pdf->MultiCellEx($hw, 8, $foreign['suspensioncomment'], $b, 'L', 0, 1, 156, $line+20);
		// ホイール
		$line = 285.5;
		$pdf->MultiCellEx($hw, 8, $foreign['wheel'], $b, 'L', 0, 1, 33, $line);
		$pdf->MultiCellEx($qw, 8, $foreign['frontsize'], $b, 'L', 0, 1, 156, $line);
		$pdf->MultiCellEx($qw, 8, $foreign['rearsize'], $b, 'L', 0, 1, 215, $line);
		// タイヤ
		$line = 305.5;
		$pdf->MultiCellEx($hw, 8, $foreign['tire'], $b, 'L', 0, 1, 33, $line);
		$pdf->MultiCellEx($qw, 8, $foreign['fronttire'], $b, 'L', 0, 1, 156, $line);
		$pdf->MultiCellEx($qw, 8, $foreign['reartire'], $b, 'L', 0, 1, 215, $line);
		// エンジン系
		$line = 325.5;
		$pdf->MultiCellEx($hw, 8, $foreign['enginetype'], $b, 'L', 0, 1, 33, $line);
		$pdf->MultiCellEx(25, 8, $foreign['enginecc'], $b, 'R', 0, 1, 142, $line);
		if ($foreign['outputnum'] != '') {
			$pdf->SetTextColor(128,128,128);
			$pdf->SetFont('kozgopromedium', 'B', 8.5);
			$pdf->SetFontStretching(100);
			$pdf->MultiCellEx(25, 8, ($foreign['outputunit'] == 2 ? 'ps':'kw'), $b, 'R', 0, 1, 170, $line+4.75);
			$pdf->SetFont('kozgopromedium', 'B', 17);
			$pdf->SetFontStretching(80);
			$pdf->SetTextColor(0);
		}
		$pdf->MultiCellEx(25, 8, $foreign['outputnum'], $b, 'R', 0, 1, 164, $line);
		$pdf->MultiCellEx(25, 8, $foreign['outputrpm'], $b, 'R', 0, 1, 187, $line);
		if ($foreign['torquenum'] != '') {
			$pdf->SetTextColor(128,128,128);
			$pdf->SetFont('kozgopromedium', 'B', 8.5);
			$pdf->SetFontStretching(100);
			$pdf->MultiCellEx(25, 8, ($foreign['torqueunit'] == 2 ? 'Nm':'kg'), $b, 'R', 0, 1, 217, $line+4.75);
			$pdf->SetFont('kozgopromedium', 'B', 17);
			$pdf->SetFontStretching(80);
			$pdf->SetTextColor(0);
		}
		$pdf->MultiCellEx(25, 8, $foreign['torquenum'], $b, 'R', 0, 1, 211, $line);
		$pdf->MultiCellEx(25, 8, $foreign['torquerpm'], $b, 'R', 0, 1, 233, $line);
		$pdf->MultiCellEx($fw, 28, $foreign['enginecomment'], $b, 'L', 0, 1, 33, $line+10);
		// 排気系
		$line = 46;
		$pdf->MultiCellEx($fw, 8, $foreign['muffler'], $b, 'L', 0, 1, 33, $line+329);
		// その他チューニング(18pt/52letter)
		$pdf->MultiCellEx($fw, 8, $foreign['comment'], $b, 'L', 0, 1, 33, $line+349);
		$pdf->lastPage();
	}
	protected function preview_concept($data, &$pdf)
	{
		$b = '0';
		$line = 8;
		$foreign = $data['foreign'];
		foreach($foreign as $key=>$val) {
			$val = mb_convert_kana($val, "KVsa", "UTF-8");
			$foreign[$key] = str_replace("\n", " ", $val);
		}
		// 投票番号が存在するのは、プロモーションコードが存在しないとき＆小間番号が存在する時
		if ($foreign['exhboothno'] != '' && $foreign['promotion'] == '') {
			$number = $foreign['exhboothno'] . str_pad($foreign['seqno'], '2', '0', STR_PAD_LEFT);
		} else {
			$number = '';
		}
		$enableroad = $foreign['enableroad'] ? '公道走行可':'公道走行不可';
		$sales = $foreign['sales'] ? '販売可':'';
		$sectionstr = isset($this->sectionarr[$foreign['sectionno']]) ? $this->sectionarr[$foreign['sectionno']]:'';
		$sectionsubstr = ($foreign['sectionno'] == 9) ? '':'部門';
		// ページを追加して、テンプレートを設定
		$pdf->AddPage();
		$pdf->setSourceFile(APPPATH . '/views/pdf/2020concept.pdf');
		$tpl = $pdf->importPage(1);
		$pdf->useTemplate($tpl);
		// 公道走行
		$pdf->SetFont('kozgopromedium', 'B', 18, '', 'default');
		$pdf->SetTextColor(255);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0.127);
        $pdf->SetFillColor(128, 128, 128);
        $pdf->Rect(242, $line-1, 40, 10, 'F', null);
        $pdf->MultiCell(40, 8, $enableroad, $b, 'C', 0, 1, 242, $line);
        if ($sales != '') {
            $pdf->Rect(200, $line-1, 40, 10, 'F', null);
            $pdf->MultiCell(40, 8, $sales, $b, 'C', 0, 1, 200, $line);
        }
		// 車両名
		$pdf->SetFont('kozgoproheavy', '', 40);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0);
		$pdf->MultiCellEx(220, 18, $foreign['carname'], $b, 'L', 0, 1, 70, 18); // 2013 is 16
		// 投票番号
		$pdf->SetFont('helvetica', 'B', 40);
		$pdf->SetTextColor(220,20,60);
		$pdf->SetFontStretching(strlen($number) == 5 ? 70:60);
		$pdf->SetFontSpacing(0);
		$pdf->MultiCell(33, 0, $number, $b, 'C', 0, 1, 28.2, 39); // 2013 is 43
		$fw = 235;
		$hw = 115;
		$line = 46; // 2013 is 49
		// 出展者名
		$pdf->SetFont('kozgopromedium', 'B', 20);
		$pdf->SetTextColor(0);
		$pdf->SetFontStretching(80);
		$pdf->SetFontSpacing(0.127);
		$pdf->MultiCell(120, 8, $foreign['brandname'], $b, 'L', 0, 1, 66, $line);
		$pdf->MultiCell(60, 8, $sectionstr.$sectionsubstr, $b, 'L', 0, 1, 186, $line);
		$pdf->MultiCell(30, 8, $foreign['exhboothno'], $b, 'L', 0, 1, 250, $line);
		$pdf->SetFont('kozgopromedium', 'B', 18);
		$pdf->MultiCellEx($fw, 78, $foreign['concept'], $b, 'L', 0, 1, 33, $line+20);
		// コンセプト
		$line = 156; // 2013 is 159
		// ベース
		$pdf->MultiCellEx($hw, 8, $foreign['basecarname'], $b, 'L', 0, 1, 33, $line);
		$pdf->MultiCellEx(70, 8, $foreign['basecartype'], $b, 'L', 0, 1, 158, $line);
		$pdf->MultiCellEx(40, 8, $foreign['basecaryear'], $b, 'L', 0, 1, 235, $line);
		// エンジン
		$pdf->MultiCellEx($hw, 8, $foreign['enginetype'], $b, 'L', 0, 1, 33, $line+20);
		$pdf->MultiCellEx(26, 8, $foreign['enginecc'], $b, 'R', 0, 1, 142, $line+20);
		if ($foreign['outputnum'] != '') {
			$pdf->SetTextColor(128,128,128);
			$pdf->SetFont('kozgopromedium', 'B', 8);
			$pdf->MultiCellEx(25, 8, ($foreign['outputunit'] == 2 ? 'ps':'kw'), $b, 'R', 0, 1, 170, $line+24.5+0.5);
			$pdf->SetFont('kozgopromedium', 'B', 17);
			$pdf->SetTextColor(0);
		}
		$pdf->MultiCellEx(26, 8, $foreign['outputnum'], $b, 'R', 0, 1, 164, $line+20);
		$pdf->MultiCellEx(26, 8, $foreign['outputrpm'], $b, 'R', 0, 1, 187, $line+20);
		if ($foreign['torquenum'] != '') {
			$pdf->SetTextColor(128,128,128);
			$pdf->SetFont('kozgopromedium', 'B', 8);
			$pdf->MultiCellEx(25, 8, ($foreign['torqueunit'] == 2 ? 'Nm':'kg'), $b, 'R', 0, 1, 216.5, $line+24.5+0.5);
			$pdf->SetFont('kozgopromedium', 'B', 17);
			$pdf->SetTextColor(0);
		}
		$pdf->MultiCellEx(26, 8, $foreign['torquenum'], $b, 'R', 0, 1, 211, $line+20);
		$pdf->MultiCellEx(26, 8, $foreign['torquerpm'], $b, 'R', 0, 1, 233, $line+20);
		$pdf->MultiCellEx($fw, 28, $foreign['enginecomment'], $b, 'L', 0, 1, 33, $line+40);
		// サスペンション
		$pdf->MultiCellEx($fw, 28, $foreign['suspensioncomment'], $b, 'L', 0, 1, 33, $line+80);
		// 外装関係
		$pdf->MultiCellEx($fw, 28, $foreign['dressupcomment'], $b, 'L', 0, 1, 33, $line+120);
		// 内装関係
		$pdf->MultiCellEx($fw, 28, $foreign['etc'], $b, 'L', 0, 1, 33, $line+159.5);
		// ホイール
		$pdf->MultiCellEx($hw, 8, $foreign['wheel'], $b, 'L', 0, 1, 33, $line+200);
		$pdf->MultiCellEx(55, 6, $foreign['frontsize'], $b, 'L', 0, 1, 156, $line+200);
		$pdf->MultiCellEx(55, 6, $foreign['rearsize'], $b, 'L', 0, 1, 216, $line+200);
		// タイヤ
		$pdf->MultiCellEx($hw, 8, $foreign['tire'], $b, 'L', 0, 1, 33, $line+220);
		$pdf->MultiCellEx(55, 6, $foreign['fronttire'], $b, 'L', 0, 1, 156, $line+220);
		$pdf->MultiCellEx(55, 6, $foreign['reartire'], $b, 'L', 0, 1, 216, $line+220);
		// 記録等
		$pdf->MultiCellEx(40, 8, $foreign['maxspeed'], $b, 'R', 0, 1, 33, $line+240);
		$pdf->MultiCellEx(40, 8, $foreign['dragspeed'], $b, 'R', 0, 1, 97, $line+240);
		$pdf->MultiCellEx($hw, 8, $foreign['speedcomment'], $b, 'L', 0, 1, 164, $line+240);
		$pdf->lastPage();
/*
		header('Content-Type: application/pdf');
		header('Cache-Control: max-age=0');
		$pdf->Output();
*/
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
    function archive()
    {
        if (uri_folder_string() == '/ex') {
            echo $this->parser->parse('prohibited');
            exit;
        }
        $this->load->library('zip');
        ini_set('memory_limit', '2048M');
        $this->db->select('eb.exhboothid, eb.exhid, eb.exhboothno');
		$this->db->select('e.corpname, e.brandname, s.spaceabbr');
//		$this->db->select('vx.expired, v.carname, v.spec, v.photo1, v.photo2, v.photo3');
		$this->db->select('vx.expired, v.carname, v.spec, v.photo1');
        $this->db->select('v.appid, v.created, v.updated');
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('exhibitors e', 'e.exhid = eb.exhid');
        $this->db->join('v_exapply_01 vx', 'vx.exhboothid = eb.exhboothid', 'left');
        $this->db->join('v_exapply_01_detail v', 'v.exhboothid = eb.exhboothid', 'left');
        $this->db->where('eb.expired', '0');
        $this->db->where('e.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));
        $query = $this->db->get();
        if ($query === FALSE) {
            show_error('ダウンロードできません.');
        }
        foreach ($query->result_array() as $row) {
            if ($row['photo1'] != '') {
                $this->zip->read_file(APPPATH.'/photos/car/'.$row['photo1']);
            }
/*
            if ($row['photo2'] != '') {
                $this->zip->read_file(APPPATH.'/photos/car/'.$row['photo2']);
            }
            if ($row['photo3'] != '') {
                $this->zip->read_file(APPPATH.'/photos/car/'.$row['photo3']);
            }
*/
        }
        $this->zip->download('e01car_photo.zip');
    }
	protected function download_build()
	{
        $this->db->select("ed.appid '手続番号', ed.appno '手続書類', bo.exhboothno '小間番号'");
        $this->db->select("e.exhid '出展者コード', e.corpname '出展者名', e.corpkana '出展者名カナ'");
		$this->db->select("e.brandname '表示名', e.brandkana '表示名カナ', ed.brandnameen '表示名英文'");
		$this->db->select("e.promotion 'プロモーションコード'");
        $this->db->select("ed.spec '系番号', CASE ed.spec WHEN 0 THEN 'チューニング' WHEN 1 THEN 'ドレスアップ' WHEN 2 THEN 'コンセプト' END '系登録'", FALSE);
        $this->db->select("ed.carname '出展車両名', ed.carkana '出展車両名カナ', ed.carnameen '出展車両名英文'");
        $this->db->select("ed.basecarbrand 'ベース車両メーカー（ブランド）名'");
        $this->db->select("ed.basecarname 'ベース車両名', ed.basecartype '形式', basecaryear '年式', ed.boothtype 'ブース種別'");
        $this->db->select("ed.sectionno 'エントリ部門', ed.enableroad '公道走行可'");
        $this->db->select("IF(ed.enablerace,ed.enablerace,0) '競技専用', IF(ed.prototype,ed.prototype,0) '参考出品'", FALSE);
        $this->db->select("ed.sales '販売可能', ed.reference '参考販売掲載', ed.referenceprice '参考販売価格(税込)', ed.stand 'スペックボードの作成'");
        $this->db->select("ed.publicdate '公開日'");
        $this->db->select("ed.enginetype 'エンジン形式', ed.enginecc '排気量'");
//      $this->db->select("REPLACE(ed.concept,'\\n','\\\\n') 'コンセプト', IF(ed.complete>0, 100, ed.progress) AS '完成度', ed.carno '車両番号'", FALSE);
        $this->db->select("REPLACE(ed.concept,'\\n','\\\\n') 'コンセプト', IF(ed.complete>0, 100, ed.progress) AS '完成度', IF(e.promotion IS NULL AND bo.exhboothno,CONCAT(bo.exhboothno,LPAD(ed.seqno,2,'0')),NULL) '車両番号'", FALSE);
        $this->db->select("ed.outputnum '出力', ed.outputunit '出力単位', ed.outputrpm '出力rpm'");
        $this->db->select("ed.torquenum 'トルク', ed.torqueunit 'トルク単位', ed.torquerpm 'トルクrpm'");
        $this->db->select("REPLACE(ed.enginecomment,'\\n','\\\\n') 'チューニング内容＆使用パーツ'", FALSE);
        $this->db->select("ed.muffler 'マフラー', ed.manifold 'EXマニホールド', ed.transmission 'ミッション'");
        $this->db->select("ed.clutch 'クラッチ', ed.differential 'デフ', ed.aeroname 'エアロキット名'");
        $this->db->select("ed.bodycolor 'ボディカラー'");
        $this->db->select("REPLACE(ed.dressupcomment,'\\n','\\\\n') 'ドレスアップ内容＆使用パーツ'", FALSE);
        $this->db->select("ed.sheet 'シート', ed.steering 'ステアリング', ed.meter 'メーター'");
        $this->db->select("ed.audio 'オーディオ', ed.carnavi 'カーナビ'");
        $this->db->select("ed.floormat 'フロアマット'");
        $this->db->select("REPLACE(ed.etc,'\\n','\\\\n') '内装系その他'", FALSE);
        $this->db->select("ed.suspension 'サスキット名', ed.absorber 'ショック', ed.spring 'スプリング', ed.brake 'ブレーキ'");
        $this->db->select("REPLACE(ed.suspensioncomment,'\\n','\\\\n') 'サスペンションその他'", FALSE);
        $this->db->select("ed.wheel 'ホイールメーカー・名称', ed.frontsize 'ホイールサイズ(F)', ed.rearsize 'ホイールサイズ(R)'");
        $this->db->select("ed.tire 'タイヤメーカー・名称', ed.fronttire 'タイヤサイズ(F)', ed.reartire 'タイヤサイズ(R)'");
        $this->db->select("ed.maxspeed '最高速', ed.dragspeed 'ドラッグ'");
        $this->db->select("REPLACE(ed.speedcomment,'\\n','\\\\n') '速度その他'", FALSE);
        $this->db->select("REPLACE(ed.comment,'\\n','\\\\n') 'その他チューニング'", FALSE);
        $this->db->select("CONCAT('http://archive.tokyoautosalon.jp/2020/photos/car/',ed.photo1) 'photo1'", FALSE);
/*
        $this->db->select("CONCAT('http://archive.tokyoautosalon.jp/2012/photos/car/',ed.photo2) 'photo2'", FALSE);
        $this->db->select("CONCAT('http://archive.tokyoautosalon.jp/2012/photos/car/',ed.photo3) 'photo3'", FALSE);
*/
        $this->db->select("ed.created '新規登録日時', ed.updated '最終更新日時'");
        $this->db->from('v_exapply_01_detail ed');
        $this->db->join('exhibitor_booth bo', 'ed.exhboothid = bo.exhboothid');
        $this->db->join('exhibitors e', 'e.exhid = bo.exhid');
        $this->db->join('booths bs', 'bs.boothid = bo.boothid');
        $this->db->where('e.expired', '0');
        $this->db->where('ed.expired', '0');
        $this->db->where_in('e.statusno', array('500','401','400'));
	}
}
// vim:ts=4
// End of file e01car.php
// Location: ./application/controllers/op/e01car.php