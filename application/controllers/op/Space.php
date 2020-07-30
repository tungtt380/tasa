<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Space extends RecOP_Controller {

	protected $form_prefix = 'space';		// フォーム名
	protected $table_name = 'v_spaces';		// テーブル名
	protected $table_prefix = FALSE;		// テーブルの払出キー名(システムで一意)
	protected $table_expire = FALSE;		// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'spaceid';	// テーブルの主キー名
	protected $foreign_token = 'token';		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'eventid'       => 'trim|required',
		'seqno'         => 'trim|required|integer',
		'spacename'     => 'trim|required',
		'spaceabbr'     => 'trim|required',
		'memberprice'   => 'trim|required|prep_nocomma|integer',
		'assocprice'    => 'trim|required|prep_nocomma|integer',
		'maxspaces'     => 'trim|required|integer',
		'forsale_count' => 'trim|required|integer',
		'comments'      => 'trim',
		'carlimits'     => 'trim|integer',
	);
	protected $foreign_query = array(			// 全文検索用で使用するカラム
		'spacename', 'spaceabbr', 'comments',
	);
	protected $foreign_order = 'seqno';

	protected function setup_calc(&$data)
	{
		$subtotal = 0;
		if (isset($data['lists'])) {
			foreach($data['lists'] as $key=>$arr) {
				$subtotal += $arr['assocprice']*$arr['forsale_count'];
			}
			$data['subtotal'] = $subtotal;
		}
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

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('event_model');
		$data['event'] = $this->event_model->get_dropdown();
		$this->load->model('space_model');
		$data['space'] = $this->space_model->get_dropdown();
	}
}

/* End of file space.php */
/* Location: ./application/controllers/(:any)/space.php */
