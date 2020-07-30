<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category extends RecOP_Controller {

	protected $form_prefix = 'category';		// フォーム名
	protected $table_name = 'category';			// テーブル名
	protected $table_prefix = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire = FALSE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'categorycode';	// テーブルの主キー名
	protected $foreign_token = FALSE;			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'categorycode' => 'trim|required|xss_clean|numeric',
		'seqno'        => 'trim|required|xss_clean|numeric',
		'categoryname' => 'trim|required|xss_clean',
		'categoryabbr' => 'trim|required|xss_clean',
		'allow'        => 'trim|xss_clean|is_natural',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'categorycode', 'categoryname', 'categoryabbr',
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

/* End of file category.php */
/* Location: ./application/controllers/(:any)/category.php */
