<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Booth extends RecOP_Controller {

	protected $form_prefix = 'booth';		// フォーム名
	protected $table_name = 'booths';		// テーブル名
	protected $table_prefix = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire = FALSE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'boothid';	// テーブルの主キー名
	protected $foreign_token = TRUE;		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'eventid'    => 'trim|required',
		'boothno'    => 'trim|required|integer',
		'spaceid'    => 'trim|required',
		'boothname'  => 'trim|required',
		'boothabbr'  => 'trim|required',
		'boothcount' => 'trim|required|integer',
		'allow'      => 'trim|xss_clean|is_natural',
	);
	protected $foreign_query = array(		// 全文検索用で使用するカラム
		'boothname', 'boothabbr',
	);
	protected $foreign_order = 'boothno ASC';

	function registed()
	{
		redirect(uri_class_string() . '/');
	}
	function changed()
	{
		redirect(uri_class_string() . '/');
	}
	function deleted()
	{
		redirect(uri_class_string() . '/');
	}

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('event_model');
		$data['event'] = $this->event_model->get_dropdown();
		$this->load->model('space_model');
		$data['space'] = $this->space_model->get_dropdown();
	}
}

/* End of file event.php */
/* Location: ./application/controllers/(:any)/event.php */
