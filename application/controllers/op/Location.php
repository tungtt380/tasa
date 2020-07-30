<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Location extends RecOP_Controller {

	protected $form_prefix   = 'location';		// フォーム名
	protected $table_name    = 'location';		// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'locationcode';	// テーブルの主キー名
	protected $foreign_token = FALSE;			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'locationcode' => 'trim|required|xss_clean|numeric',
		'seqno'        => 'trim|required|xss_clean|numeric',
		'locationname' => 'trim|required|xss_clean',
		'locationabbr' => 'trim|required|xss_clean',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'locationcode', 'locationname', 'locationabbr',
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
}

/* End of file location.php */
/* Location: ./application/controllers/(:any)/location.php */
