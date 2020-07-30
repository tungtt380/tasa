<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exmatch extends RecOP_Controller {

	protected $form_prefix	 = 'exmatch';		// フォーム名
	protected $table_name	 = 'customers';		// テーブル名
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'exhid';			// テーブルの主キー名
	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'exhid'		  => 'trim|required|xss_clean',	// 必要なのは、出展者IDと顧客ID
		'customerid'  => 'trim|xss_clean',
	);

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();

		$this->db->select('e.exhid, e.corpname, e.corpkana, e.brandname, e.brandkana');
		$this->db->select('e.promotion, e.comment, cus.customerid');
		$this->db->from('exhibitors e');
		$this->db->join('customers cus', 'e.exhid = cus.exhid', 'left');
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('401','400'));

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		} else {
			$data['lists'] = array();
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function search_in()
	{
		$keyword = $this->input->post('q');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
		}
		if ($querystring != '') {
			redirect('/' . dirname(uri_string()) . $querystring);
		}
		redirect('/' . dirname(uri_string()) . '/./');
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function detail($exhid)
	function detail($uid = '')
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
		$exhid = $uid;
		// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
		$data = $this->setup_data();

		// 今回の顧客情報の取得
		$this->db->select('e.exhid, e.corpname, e.corpkana, e.brandname, e.brandkana');
		$this->db->select('e.zip, e.prefecture, e.address1, e.address2, e.phone, e.promotion, e.comment, cus.customerid');
		$this->db->from('exhibitors e');
		$this->db->join('customers cus', 'e.exhid = cus.exhid', 'left');
		$this->db->where('e.exhid', $exhid);
		$this->db->where('e.expired', '0');
		$this->db->where_in('e.statusno', array('401','400'));

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['detail'] = $query->row_array();
		} else {
			$data['detail'] = array();
		}

		// マッチした稼働中の顧客情報の取得
		$keyword = $this->input->get('q');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword === FALSE || $keyword == '') {
		if ($keyword === NULL || $keyword == '') {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$keyword = $data['detail']['corpkana'];
		}
		$data['q'] = $keyword;

		// 顧客マスタを顧客カナで曖昧検索をおこなう。
		$this->db->select('c.customerid, c.exhid, c.corpname, c.corpkana');
		$this->db->select('c.zip, c.prefecture, c.address1, c.address2, c.phone');
		$this->db->from('customers c');
		$this->db->where('c.expired', '0');
		$this->db->collate_like('c.corpkana', $data['q']);

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['customer'] = $query->result_array();
		} else {
			$data['customer'] = array();
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function detailsearch_in()
	{
		$exhid = $this->input->post('exhid');
		$keyword = $this->input->post('q');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
		}
		if ($querystring != '') {
			redirect('/' . dirname(uri_string()) . '/detail/' . $exhid . $querystring);
		}
		redirect('/' . dirname(uri_string()) . '/./');
	}

	// 初回設定処理(未実装)
	function create_in()
	{
		$this->check_action();
		$data['foreign'] = $this->input->post();

		// 入力値をチェック
		foreach($this->foreign_value as $key=>$val) {
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

		// 入力値はフィルタするため、実際のデータはここで格納する
		foreach($this->foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
		if (!isset($data['message']) || empty($data['message'])) {
			$this->check_logic($data);
		}

		// 入力不備の場合は、元の画面に戻る
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/');
		}

		// データベースに登録
//		die(var_export($data));
		$result = $this->create_record($data['foreign']);

		// データベース登録の成否により、ログとメッセージを出力する
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

	protected function create_record(&$foreign)
	{
		$exhid = $foreign['exhid'];

		// 元になるデータの抽出
		$this->db->where('exhid', $exhid);
		$this->db->where('expired', '0');
		$query = $this->db->get('exhibitors');
		if ($query->num_rows() <= 0) {
			return FALSE;
		}
		$foreign = $query->row_array();

		// データの作成
		$this->load->model('customers_model');
		$result = $this->customers_model->create($foreign, 'AS020200');

		if ($result !== FALSE) {
			$line = $this->lang->line('LOG:M2001');
			log_message('notice', sprintf($line, $this->table_name, $result));
			log_message('info', $this->db->last_query());
		} else {
			$line = $this->lang->line('LOG:N4001');
			log_message('notice', sprintf($line, $this->table_name));
			log_message('info', $this->db->last_query());
		}
		return $result;
	}

	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// protected function update_record(&$foreign)
	protected function update_record($foreign = Array())
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		$customerid = $foreign['customerid'];
		$exhid = $foreign['exhid'];

		// 元になるデータの抽出
		$this->db->where('exhid', $exhid);
		$this->db->where('expired', '0');
		$query = $this->db->get('exhibitors');
		if ($query->num_rows() <= 0) {
			return FALSE;
		}
		$foreign = $query->row_array();

		// データの作成
		$foreign['customerid'] = $customerid;
		unset($foreign['token']);
		$this->load->model('customers_model');
		$result = $this->customers_model->update($foreign, 'AS020200');

		if ($result !== FALSE) {
			$line = $this->lang->line('LOG:M2001');
			log_message('notice', sprintf($line, $this->table_name, $result));
			log_message('info', $this->db->last_query());
		} else {
			$line = $this->lang->line('LOG:N4001');
			log_message('notice', sprintf($line, $this->table_name));
			log_message('info', $this->db->last_query());
		}
		return $result;
	}

	// 引継処理
	function change_in()
	{
		$this->check_action();
		$data['foreign'] = $this->input->post();

		// 入力値をチェック
		foreach($this->foreign_value as $key=>$val) {
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
		foreach($this->foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
		if (!isset($data['message']) || empty($data['message'])) {
			$this->check_logic($data);
		}

		// 入力不備ならリダイレクト
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/change/' . $data['foreign'][$this->foreign_keyid]);
		}

		// レコードの更新
		$result = $this->update_record($data['foreign']);
		$line = $this->lang->line($result ? 'M2002':'N4002');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result !== FALSE ? '1':'0'));

		if ($result) {
			$this->log_history('変更', $data['foreign'][$this->foreign_keyid]);
			$this->after_change($data);
		}
		redirect(uri_redirect_string() . '/changed');
	}

	function registed()
	{
		redirect('/' . dirname(uri_string()) . '/./');
	}
	function changed()
	{
		redirect('/' . dirname(uri_string()) . '/./');
	}
}
// vim:ts=4
