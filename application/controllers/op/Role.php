<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Role extends RecOP_Controller {

	protected $form_prefix   = 'role';		// フォーム名
	protected $table_name    = 'roles';		// テーブル名
	protected $table_prefix  = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire  = FALSE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'roleid';	// テーブルの主キー名
	protected $foreign_token = FALSE;		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'seqno'    => 'trim|required|integer',
		'rolename' => 'trim|required|alpha_numeric',
		'comment'  => 'trim',
	);
	protected $foreign_query = array(		// 全文検索用で使用するカラム
		'rolename', 'comment',
	);

	function registed()
	{
		$this->write_config();
		redirect(uri_class_string() . '/');
	}
	function changed()
	{
		$this->write_config();
		redirect(uri_class_string() . '/');
	}
	function deleted()
	{
		$this->write_config();
		redirect(uri_class_string() . '/');
	}

	protected function write_config()
	{
		// ここは本来、権限グループの設定変更をした場合は高速化のために
		// 権限ファイル(/application/config/permission.php)の出力を
		// 行いたいが、現在は仮置きということで何もしていない
/*
		$filename = APPPATH . 'config/permission2.php';

		$this->db->where('disabled', '0');
		$this->db->order_by('rolename', 'ASC')->order_by('url', 'ASC');
		$query = $this->db->get('permissions');

		$header = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');\n\n";
		$source = "\$config['permission'] = array(\n";
		$b_rolename = FALSE;

		foreach($query->result() as $row) {
			if ($b_rolename != $row->rolename) {
				if ($b_rolename) {
					$source .= "\t),\n";
				}
				$b_rolename = $row->rolename;
				$source .= "\t'" . $b_rolename . "' => array(\n";
			}
			$source .= "\t\t'" . $row->url . "',\n";
		}
		$source .= "\t),\n";
		$source .= ");\n";

		$last = ignore_user_abort(TRUE);
		file_put_contents($filename, $header.$source, LOCK_EX);
		ignore_user_abort($last);
*/
	}
}

/* End of file role.php */
/* Location: ./application/controllers/(:any)/role.php */
