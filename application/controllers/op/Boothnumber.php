<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Boothnumber extends MemOP_Controller {

	protected $form_prefix   = 'boothnumber';       // フォーム名
	protected $table_name    = 'exhibitor_booth';   // テーブル名
	protected $table_prefix  = FALSE;        // テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;        // テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'appid'; // テーブルの主キー名
	protected $foreign_token = 'token';     // ２重更新・削除防止のための項目
	protected $foreign_value = array(       // 入力チェック用に使用するカラムとパターン
		'exhboothid' => 'trim|required',
		'exhboothno' => 'trim|min_length[3]|max_length[4]',
	);

	function index()
	{
		redirect(uri_class_string() . '/regist');
	}

	// 本来は違うのだが、ここではindexと同じ
    // 2013/11
    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
    // function detail()
    function detail($uid = '')
    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $data['message'] = $this->session->flashdata('message');
        $this->session->keep_flashdata('foreign');

        $keyword = $this->input->get('q');
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
        // if ($keyword !== FALSE) {
        if ($keyword !== NULL) {
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
            $data['q'] = $keyword;
            $this->db->collate_like('scantext', $keyword);
        } else {
            $data['q'] = '';
        }

        $data['boothnumber'] = array();
        // Upgrade PHP7 - Silence “Parameter must be an array or an object that implements Countable” error in PHP 7 - Start by TTM
        // $data['foreign']['exhboothid']
		// $maxcount = count($data['foreign']['exhboothid']);
		// for ($i = 0; $i < $maxcount; $i++) {
		// 	$data['boothnumber'][$data['foreign']['exhboothid'][$i]] = $data['foreign']['exhboothno'][$i];
        // }
        if($data['foreign']['exhboothid']) {
            $maxcount = count($data['foreign']['exhboothid']);
            for ($i = 0; $i < $maxcount; $i++) {
                $data['boothnumber'][$data['foreign']['exhboothid'][$i]] = $data['foreign']['exhboothno'][$i];
            }
        }
        // Upgrade PHP7 - Silence “Parameter must be an array or an object that implements Countable” error in PHP 7 - End by TTM

		// 出展者小間(eb)+出展者(e)をベースに小間リストを構築
        $this->db->select("eb.exhboothid, eb.exhid, eb.seqno, eb.exhboothno, e.corpname, e.brandname");
        $this->db->select("s.spaceabbr, b.boothabbr, b.boothcount, eb.created, eb.updated, e.statusno");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('v_exhibitors_search e', 'e.exhid = eb.exhid');
		$this->db->where_in('e.statusno', array('400','401','500'));
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

	// 本来は違うのだが、ここではindexと同じ
	function regist()
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $data['message'] = $this->session->flashdata('message');
        $this->session->keep_flashdata('foreign');

        $keyword = $this->input->get('q');
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
        // if ($keyword !== FALSE) {
        if ($keyword !== NULL) {
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
            $data['q'] = $keyword;
            $this->db->collate_like('scantext', $keyword);
        } else {
            $data['q'] = '';
        }

        $data['boothnumber'] = array();
        // Upgrade PHP7 - Silence “Parameter must be an array or an object that implements Countable” error in PHP 7 - Start by TTM
		// $maxcount = count($data['foreign']['exhboothid']);
		// for ($i = 0; $i < $maxcount; $i++) {
		// 	$data['boothnumber'][$data['foreign']['exhboothid'][$i]] = $data['foreign']['exhboothno'][$i];
        // }
        if($data['foreign']['exhboothid']){
            $maxcount = count($data['foreign']['exhboothid']);
            for ($i = 0; $i < $maxcount; $i++) {
                $data['boothnumber'][$data['foreign']['exhboothid'][$i]] = $data['foreign']['exhboothno'][$i];
            }
        }
        // Upgrade PHP7 - Silence “Parameter must be an array or an object that implements Countable” error in PHP 7 - End by TTM

		// 出展者小間(eb)+出展者(e)をベースに小間リストを構築
        $this->db->select("eb.exhboothid, eb.exhid, eb.seqno, eb.exhboothno, e.corpname, e.brandname");
        $this->db->select("s.spaceabbr, b.boothabbr, b.boothcount, eb.created, eb.updated, e.statusno");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('v_exhibitors_search e', 'e.exhid = eb.exhid');
		$this->db->where_in('e.statusno', array('400','401','500'));
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

	function regist_in()
	{
        $this->check_action();

        $data = $this->setup_data();
        $data['foreign'] = $this->input->post();
        $this->session->set_flashdata('foreign', $data['foreign']);

        // 入力成功後のロジックチェックしたい場合
        if (!isset($data['message']) || empty($data['message'])) {
            $this->check_logic($data);
        }

        // 入力不備ならリダイレクト
        if (isset($data['message']) && !empty($data['message'])) {
            $this->session->set_flashdata('message', $data['message']);
            log_message('notice', var_export($data,TRUE));
            redirect(uri_redirect_string() . '/regist');
        }

        // 確認画面にリダイレクト
        redirect(uri_redirect_string() . '/regist_confirm');
	}

	function check_logic(&$data)
	{
        // Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
        $result = TRUE;
        // Upgrade PHP7 - Fix bug "Undefined variable" - End by TTM

		$maxcount = count($data['foreign']['exhboothid']);
		for ($i = 0; $i < $maxcount; $i++) {
			if (!isset($data['foreign']['exhboothid'][$i]) || !ctype_digit($data['foreign']['exhboothid'][$i])) {
				$data['message']['__all'] = '<p>必須項目がありません。</p>';
				$result = FALSE;
				break;
			}
			if (isset($data['foreign']['exhboothno'][$i]) && $data['foreign']['exhboothno'][$i] != '') {
				$len = strlen($data['foreign']['exhboothno'][$i]);
				if (!ctype_digit($data['foreign']['exhboothno'][$i]) || ($len != 3 && $len != 4)) {
					$data['message']['__all'] = '<p>小間番号は数値３〜４桁のみ有効です。('.$data['foreign']['exhboothid'][$i].')</p>';
					$result = FALSE;
					break;
				}
			}
		}
        if ($result === FALSE) {
            log_message('notice', $data['message']['__all']);
        }
        return $result;
	}

	function regist_confirm()
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $this->session->keep_flashdata('foreign');

		// 出展者小間(eb)+出展者(e)をベースに小間リストを構築
        $this->db->select("eb.exhboothid, eb.exhid, eb.seqno, eb.exhboothno, e.corpname, e.brandname");
        $this->db->select("s.spaceabbr, b.boothabbr, b.boothcount, eb.created, eb.updated, e.statusno");
        $this->db->from('exhibitor_booth eb');
        $this->db->join('booths b', 'b.boothid = eb.boothid');
        $this->db->join('v_spaces s', 's.spaceid = b.spaceid');
        $this->db->join('v_exhibitors_search e', 'e.exhid = eb.exhid');
		$this->db->where_in('e.statusno', array('400','401','500'));
		$this->db->where('eb.expired', '0');
		$this->db->where('e.expired', '0');

        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }

		$data['boothnumber'] = array();
		$maxcount = count($data['foreign']['exhboothid']);
		for ($i = 0; $i < $maxcount; $i++) {
			$data['boothnumber'][$data['foreign']['exhboothid'][$i]] = $data['foreign']['exhboothno'][$i];
		}
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
    // function create_record($foreign)
    function create_record(&$foreign)
    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$maxcount = count($foreign['exhboothid']);
		$result = TRUE;

		// トランザクションの開始
        $this->db->trans_start();

		for ($i = 0; $i < $maxcount; $i++) {
			if ($result === TRUE) {
				if (ctype_digit($foreign['exhboothno'][$i])) {
					$this->db->set('exhboothno',$foreign['exhboothno'][$i]);
//キャパシティオーバーが発生しているときがあるため、小間番号のキーのみで検索(2012.11.24)
					$this->db->where('exhboothid',$foreign['exhboothid'][$i]);
		            if (!$this->db->update($this->table_name)) {
		                log_message('notice', $this->db->last_query());
		                $result = FALSE;
		            }
				}
//間違えて小間番号を入力した場合に削除ができなかったので、削除動作を追加(2016.11.29)
				else {
					$this->db->set('exhboothno','NULL',FALSE);
					$this->db->where('exhboothid',$foreign['exhboothid'][$i]);
		            if (!$this->db->update($this->table_name)) {
		                log_message('notice', $this->db->last_query());
		                $result = FALSE;
		            }
//ここまで(2016.11.29)
				}
			}
		}
        // すべてうまくいったならコミットする
        if ($result === FALSE || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
        return $result;
	}

	function search()
	{
        $referer = $this->input->server('HTTP_REFERER');
		$referer = uri_class_string() . (strstr($referer, '/detail') ? '/detail':'/regist/');

        $keyword = $this->input->post('q');
        if ($keyword != '') {
            redirect($referer . '?q=' . rawurlencode($keyword));
        }
        redirect($referer);
	}

    protected function setup_form(&$data)
    {
        $this->load->helper('form');
        $this->load->model('status_model');
        $data['status'] = $this->status_model->get_dropdown();
    }
}
