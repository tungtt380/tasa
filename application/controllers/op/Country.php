<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Country extends RecOP_Controller {

	protected $form_prefix  = 'country';		// フォーム名
	protected $table_name  = 'country';			// テーブル名
	protected $table_prefix = FALSE;			// テーブルの払出キー名(システムで一意)
	protected $table_expire = FALSE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'countrycode';	// テーブルの主キー名
	protected $foreign_token = FALSE;			// ２重更新・削除防止のための項目
	protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
		'countrycode' => 'trim|required|xss_clean|alpha|exact_length[2]|prep_upper',
		'countryname' => 'trim|required|xss_clean',
        'allow'       => 'trim|xss_clean|is_natural',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'countrycode', 'countryname',
	);

	// 以下のオーバーライドは操作完了画面を出力しないポリシー
	function registed()
	{
		redirect('/'.dirname(uri_string()).'/./');
	}
	function changed()
	{
		redirect('/'.dirname(uri_string()).'/./');
	}
	function deleted()
	{
		redirect('/'.dirname(uri_string()).'/./');
	}

}

/* End of file country.php */
/* Location: ./application/controllers/(:any)/country.php */
