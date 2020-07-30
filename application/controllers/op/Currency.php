<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Currency extends RecOP_Controller {

	protected $form_prefix = 'currency';		// フォーム名
	protected $table_name = 'currency';			// テーブル名
	protected $table_prefix = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire = FALSE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'countrycode';	// テーブルの主キー名
	protected $foreign_token = FALSE;			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'countrycode'    => 'trim|required|xss_clean|alpha|exact_length[2]|prep_upper',
		'currencycode'   => 'trim|required|xss_clean',
		'currencysymbol' => 'trim|required',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'countrycode', 'currencycode', 'currencysymbol',
	);

	function registed()
	{
		redirect('/' . dirname(uri_string()) . '/./');
	}
	function changed()
	{
		redirect('/' . dirname(uri_string()) . '/./');
	}
	function deleted()
	{
		redirect('/' . dirname(uri_string()) . '/./');
	}

	// Upgrade CI3 - Prevent error when data length > 3 - Start by TTM
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
		if(strlen($data['foreign']['currencysymbol']) >3)
		{
			$data['foreign']['currencysymbol'] = substr($data['foreign']['currencysymbol'], 0, 3);
		}
		if(strlen($data['foreign']['currencycode']) >3)
		{
			$data['foreign']['currencycode'] = substr($data['foreign']['currencycode'], 0, 3);
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
	// Upgrade CI3 - Prevent error when data length > 3 - End by TTM
}

/* End of file currency.php */
/* Location: ./application/controllers/(:any)/currency.php */
