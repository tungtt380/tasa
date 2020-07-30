<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends RecOP_Controller {

	protected $form_prefix = 'members';		// フォーム名
	protected $table_name = 'members';		// テーブル名
	protected $table_prefix = 'M';			// テーブルの払出キー名(システムで一意)
	protected $table_expire = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'memberid';	// テーブルの主キー名
	protected $foreign_token = FALSE;		// ２重更新・削除防止のための項目
	protected $foreign_value = array(		// 入力チェック用に使用するカラムとパターン
		'rolename'  => 'trim|required',
		'username'  => 'trim|required|valid_username',
		'password'  => 'trim|required|valid_password',
		'email'     => 'trim|required|valid_email',
		'activate'  => 'trim',
		'reject'    => 'trim',
	);
	protected $foreign_query = array(		// 全文検索用で使用するカラム
		'rolename', 'username', 'email',
	);

	//【一覧画面】
	public function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$this->setup_form($data);

		$keyword = $this->input->get('q');
		$breath = false;
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$combined_query = array_fill_keys($this->foreign_query, $keyword);
			// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
			// $this->db->grouplike_start();
			$this->db->group_start();
			// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
			$this->db->or_like($combined_query);
			$this->db->or_like('scantext', $keyword);
			// Upgrade CI3 - Some methods are replaced by another in CI3 - Start by TTM
			// $this->db->grouplike_end();
			$this->db->group_end();
			// Upgrade CI3 - Some methods are replaced by another in CI3 - End by TTM
		} else {
			$data['q'] = '';
		}
		if ($this->foreign_order != '') {
			$this->db->order_by($this->foreign_order);
		}
		if ($data['rolename'] != 'sysop') {
			$this->db->where('memberid >=', 'M0000001000');
		}
		$this->db->where('eb.expired', '0');
		$this->db->from($this->table_name. ' eb');
		$this->db->join('v_exhibitors_search e', 'e.exhid = eb.pcode1 AND e.expired = 0', 'left');
		$query = $this->db->get();
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		$this->setup_calc($data);
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);

	}

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

	protected function setup_form(&$data)
	{
		$this->load->helper('form');
		$this->load->model('role_model');
		$data['role'] = $this->role_model->get_dropdown();
	}

    protected function create_record(&$foreign)
    {
		// Upgrade CI3 - Use empty check to replace isset - Start by TTM
        // if (!isset($foreign['activate'])) {
        //     $foreign['activate'] = 1;
        // }
        // if (!isset($foreign['reject'])) {
        //     $foreign['reject'] = 0;
		// }
		if (empty($foreign['activate'])) {
            $foreign['activate'] = 1;
        }
        if (empty($foreign['reject'])) {
            $foreign['reject'] = 0;
		}
		// Upgrade CI3 - Use empty check to replace isset - End by TTM
        parent::create_record($foreign);
    }

}

/* End of file account.php */
/* Location: ./application/controllers/(:any)/account.php */
