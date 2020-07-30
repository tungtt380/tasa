<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Section extends RecOP_Controller {

	protected $form_prefix   = 'section';		// フォーム名
	protected $table_name    = 'section';		// テーブル名
	protected $table_prefix  = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'sectioncode';	// テーブルの主キー名
	protected $foreign_token = FALSE;			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'sectioncode' => 'trim|required|xss_clean|numeric',
		'seqno'       => 'trim|required|xss_clean|numeric',
		'sectionname' => 'trim|required|xss_clean',
		'sectionabbr' => 'trim|required|xss_clean',
		'allow'       => 'trim|xss_clean|is_natural',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'sectioncode', 'sectionname', 'sectionabbr',
	);

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

/* End of file section.php */
/* Location: ./application/controllers/(:any)/section.php */
