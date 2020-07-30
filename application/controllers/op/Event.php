<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Event extends RecOP_Controller {

	protected $form_prefix   = 'event';		// フォーム名
	protected $table_name    = 'events';	// テーブル名
	protected $table_prefix  = 'AS';		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'eventid';	// テーブルの主キー名
	protected $foreign_token = 'token';		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'eventname'   => 'trim|required',
		'eventabbr'   => 'trim|required',
		'eventsite'   => 'trim|required',
		'eventtype'   => 'trim|required',
		'event_sdate' => 'trim|valid_isodate',
		'event_edate' => 'trim|valid_isodate',
		'entry_sdate' => 'trim|valid_isodate',
		'entry_edate' => 'trim|valid_isodate',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'eventname', 'eventabbr',
	);
	protected $foreign_order = 'eventid DESC';

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('eventtype_model');
		$data['eventtype'] = $this->eventtype_model->get_dropdown();
		$this->load->model('location_model');
		$data['location'] = $this->location_model->get_dropdown();
	}

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
}

/* End of file event.php */
/* Location: ./application/controllers/(:any)/event.php */
