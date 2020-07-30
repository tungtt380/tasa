<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * MY Application Controller Class
 *
 * Cafelounge オリジナルの Controller クラス
 * Smarty に渡すためのデータを予め定義している
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/general/controllers.html
 */
class MY_Controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Upgrade CI3 - Fix method calling - Start by TTM
		// $this->CI = get_instance();
		$this->CI =& get_instance();
		// Upgrade CI3 - Fix method calling - End by TTM
		$this->load->library('parser');
	}

	/**
	 * ディレクトリ名でアクセスしてきた際には、スラッシュ付で補完して
	 * リダイレクトする
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function slash_complete()
	{
		if ($this->input->server('QUERY_STRING') == '') {
			if (substr($this->input->server('REQUEST_URI'), -1, 1) != '/') {
				redirect('/'.uri_string().'/./');
			}
		}
	}

	/**
	 * フォームからの入力値を参照する場合は、最初に必ずこれを呼ぶこと
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function check_action()
	{
		$method = $this->input->server('REQUEST_METHOD');
		if (substr($this->input->server('REQUEST_URI'), 0, 4) == '/en/') {
			$language = 'english';
		} else {
			$language = 'japanese';
		}
		if ($method != 'POST') {
			log_message('notice', sprintf('Prohibited %s /%s', $method, uri_string()));
			$this->parser->parse('prohibited');
		} else {
			$this->load->library('form_validation', array('language' => $language));
			$this->lang->load('form_label', $language);
			$this->lang->load('form_reason', $language);
		}
	}

	protected function setup_data()
	{
		return array();
	}

	/**
	* 必要に応じてフォームに必要な変数を組み込むようにオーバーライド
	*
	* @access	protected
	* @param	&$data
	* @return	array
	*/
	protected function setup_form(&$data)
	{
		// NO ACTION
	}
	protected function setup_form_ex(&$data)
	{
		// NO ACTION
	}
	protected function setup_calc(&$data)
	{
		// NO ACTION
	}
	protected function check_logic(&$data)
	{
		// NO ACTION
	}
	protected function after_regist(&$data)
	{
		// NO ACTION
	}
	protected function after_change(&$data)
	{
		// NO ACTION
	}
	protected function after_delete(&$data)
	{
		// NO ACTION
	}

	protected function uri_compare_regex($compare = array())
	{
		$segment = $this->uri->total_segments() - $this->uri->total_rsegments() + 2;
		$uri = '/' . implode('/', array_slice($this->uri->segment_array(), 0, $segment));
		foreach($compare as $cmpstr) {
			$cmpstr = str_replace('/*', '(/.*)?', $cmpstr);
			if (substr_compare($cmpstr, '*', -1) == 0) {
				$cmpstr = substr_replace($cmpstr, '(.*)', -1);
			}
			$regex = '#^' . $cmpstr . '$#';
			if (preg_match($regex, $uri) != 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

	protected function create_token($seed = 'ZYX')
	{
		return base64_encode(sha1(uniqid(rand() . $seed), TRUE) . 'A');
	}
}

// ------------------------------------------------------------------------

/**
 * Operation Controller Class
 *
 * 認証が完了しているユーザのための Controller クラス
 * クラス生成時に認証チェックをおこない、認可されない場合はリダイレクト
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/general/controllers.html
 */
class OP_Controller extends MY_Controller {

	public function __construct()
	{
		parent::__construct();

		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->load->library('member');
		// if (!$this->member->is_login()) {
		// 	redirect(uri_folder_string().'/login');
		// }
		// $username = $this->member->get_username();
		// $rolename = $this->member->get_rolename();
		$this->load->library('member_lib');
		if (!$this->member_lib->is_login()) {
			redirect(uri_folder_string().'/login');
		}
		$username = $this->member_lib->get_username();
		$rolename = $this->member_lib->get_rolename();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM

		$this->config->load('permission', FALSE, TRUE);
		$permission = $this->config->item('permission');
		if (!isset($permission[$rolename]) || !$this->uri_compare_regex($permission[$rolename])) {
			log_message('notice', sprintf('Permission denied %s /%s', $username, uri_string()));
			echo $this->parser->parse('prohibited');
			exit;
		}

		$this->load->language('form_label');
		$this->load->model('histories_model');
	}

	protected function setup_data()
	{
		$data = array();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $data['username'] = $this->member->get_username();
		// $data['rolename'] = $this->member->get_rolename();
		$data['username'] = $this->member_lib->get_username();
		$data['rolename'] = $this->member_lib->get_rolename();
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
		$permission = $this->config->item('permission');
		$data['rolepermission'] = $permission[$data['rolename']];
		return $data;
	}

}

// ------------------------------------------------------------------------

/**
 * Record Operation Controller Class
 *
 * 事務局がデータベースの簡易登録を行うときのクラス
 * この操作には確認画面は基本的には存在しない
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/general/controllers.html
 */
class RecOP_Controller extends OP_Controller {

	protected $form_prefix;		// フォーム名
	protected $table_name;		// テーブル名
	protected $table_prefix;	// テーブルの払出キー名(システムで一意)
	protected $table_expire;	// テーブルが論理削除の場合TRUE
	protected $foreign_keyid;	// テーブルの主キー名
	protected $foreign_token;	// ２重更新・削除防止のための項目
	protected $foreign_value;	// 入力チェック用に使用するカラムとパターン
	protected $foreign_query;	// 全文検索用で使用するカラム
	protected $foreign_order;

	//【一覧画面】
	public function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$this->setup_form($data);

		$keyword = $this->input->get('q');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$combined_query = array_fill_keys($this->foreign_query, $keyword);
			$this->db->or_like($combined_query);
		} else {
			$data['q'] = '';
		}
		if ($this->foreign_order != '') {
			$this->db->order_by($this->foreign_order);
		}

		$query = $this->db->get($this->table_name);
		if ($query !== FALSE && $query->num_rows() > 0) {
			$data['lists'] = $query->result_array();
		}
		$this->setup_calc($data);
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【検索】
	public function search()
	{
		$keyword = $this->input->post('q');
		if ($keyword != '') {
			redirect(uri_redirect_string() . '/?q=' . rawurlencode($keyword));
		}
		redirect(uri_redirect_string() . '/./');
	}

	//【詳細画面】
	public function detail($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			redirect(uri_redirect_string() . '/', 'location', 302);
		}
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【登録画面 - セッション切り】
	public function create($uid='')
	{
		if ($uid != '') {
			redirect(uri_redirect_string() . '/../regist/' . $uid);
		} else {
			redirect(uri_redirect_string() . '/regist');
		}
	}

	//【登録画面】
	public function regist()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【登録処理】
	public function regist_in()
	{
		$this->check_action();

		$data = $this->setup_data();
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

		// 入力値はフィルタするため、実際のデータはここで格納する
		foreach($this->foreign_value as $key=>$val) {
			$data['foreign'][$key] = $this->form_validation->set_value($key);
		}
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
		if (!isset($data['message']) || empty($data['message'])) {
			$this->check_logic($data);
		}

		// 入力不備の場合は、元の画面に戻る
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/regist');
		}

		// データベースに登録
		$result = $this->create_record($data['foreign']);

		// データベース登録の成否により、ログとメッセージを出力する
		$line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result !== FALSE) ? '1':'0');

		if ($result !== FALSE) {
			$this->log_history('登録', $result);
			$this->after_regist($data);
		}

		// 登録完了画面へ
		redirect(uri_redirect_string() . '/registed');
	}

	//【登録完了画面】
	public function registed()
	{
		$this->completed($this->form_prefix.'_'.__FUNCTION__);
	}

	//【変更画面】
	public function change($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->keep_flashdata('foreign');
		} else {
			$this->get_record($data, $uid);
		}

		$this->setup_form_ex($data);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	//【変更処理】
	public function change_in()
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

	//【変更完了画面】
	public function changed()
	{
		$this->completed($this->form_prefix.'_'.__FUNCTION__);
	}

	//【削除画面】
	public function delete($uid='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);

		$this->setup_form_ex($data);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix . '_nodata');
		} else {
			$this->session->set_flashdata('foreign', $data['foreign']);
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	//【削除処理】
	public function delete_in()
	{
		$this->check_action();
		$data['foreign'] = $this->input->post();
		$data['post'] = $this->input->post();
		$this->session->keep_flashdata('foreign');

		// レコードの削除
		$result = $this->delete_record($data['foreign']);
		$line = $this->lang->line($result !== FALSE ? 'M2003':'N4003');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result !== FALSE) ? '1':'0');

		if ($result !== FALSE) {
			$this->log_history('削除', $data['foreign'][$this->foreign_keyid]);
			$this->after_delete($data);
		}

		redirect(uri_redirect_string() . '/deleted');
	}

	//【削除完了画面】
	public function deleted()
	{
		$this->completed($this->form_prefix.'_'.__FUNCTION__);
	}

	//【処理完了画面】
	protected function completed($template)
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		if (!empty($data['message'])) {
			$data['title'] = array_shift($data['message']);
		}
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('message');

		$this->setup_form_ex($data);
		$this->parser->parse($template, $data);
	}

	//【データの取得】デフォルト
	protected function get_record(&$data, $uid)
	{
		$this->db->where($this->foreign_keyid, $uid);
		if ($this->table_expire)
			$this->db->where('expired', 0);

		$query = $this->db->get($this->table_name);
		if ($query !== FALSE && $query->num_rows() == 1) {
			$data['foreign'] = $query->row_array();
		} else {
			$data['foreign'] = array();
			log_message('notice', $this->db->last_query());
		}
	}

	//【データの登録】デフォルト
	protected function create_record(&$foreign)
	{
		// Upgrade CI3 - Set exhid to NULL to adapt with query SQL in list function - Start by TTM
		if(empty($foreign['appid']))
			$foreign['appid'] = NULL;
		if(empty($foreign['exhid']))
			$foreign['exhid'] = NULL;
		// Upgrade CI3 - Set exhid to NULL to adapt with query SQL in list function - End by TTM
		// シリアルキーが必要な場合は、先に主キーを取得しておく
		if ($this->foreign_keyid !== FALSE && $this->table_prefix !== FALSE) {
			$uidname = $this->table_name . "." . $this->foreign_keyid;
			$this->db->select("nextuid('" . $uidname . "','" . $this->table_prefix . "') AS uid", FALSE);
			$query = $this->db->get();
			if ($query !== FALSE && $query->num_rows() == 1) {
				$row = $query->row_array();
				$uid = $row['uid'];
			}
			$query->free_result();

			if (!isset($uid) && $uid = '') {
				$line = $this->lang->line('LOG:N4001');
				log_message('notice', sprintf($line, $this->table_name));
				log_message('info', $this->db->last_query());
				return FALSE;
			}
			$foreign[$this->foreign_keyid] = $uid;
		}

		if ($this->foreign_token !== FALSE) {
			if ($this->foreign_token === TRUE) {
				$this->foreign_token = 'token';
			}
			$foreign[$this->foreign_token] = $this->create_token();
		}

		// データの作成
		$this->db->set(array_intersect_key($foreign, $this->foreign_value))
                 ->set('created', 'CURRENT_TIMESTAMP', FALSE);
		if ($this->foreign_keyid !== FALSE && $this->table_prefix !== FALSE) {
			$this->db->set($this->foreign_keyid, $foreign[$this->foreign_keyid]);
		}
		if ($this->foreign_token !== FALSE) {
			$this->db->set($this->foreign_token, $foreign[$this->foreign_token]);
		}
		$this->db->insert($this->table_name);
		
		// 結果
		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		} else {
			$result = (isset($foreign[$this->foreign_keyid]) && $foreign[$this->foreign_keyid] != '') ? $foreign[$this->foreign_keyid]:$this->db->insert_id();
		}
		if ($result !== FALSE) {
			$line = $this->lang->line('LOG:M2001');
			log_message('notice', sprintf($line, $this->table_name, $result));
			log_message('info', $this->db->last_query());
		} else {
			$line = $this->lang->line('LOG:N4001');
			log_message('notice', sprintf($line, $this->table_name));
			log_message('info', $this->db->last_query());
		}
		return $result;
	}

	//【データの更新】デフォルト
	protected function update_record($foreign = array())
	{
		// Upgrade CI3 - Set exhid to NULL to adapt with query SQL in list function - Start by TTM
		if(!empty($foreign['seqno']) && $foreign['seqno'] > 2147483647) {
			$foreign['seqno'] = 2147483647;
		}			
		// Upgrade CI3 - Set exhid to NULL to adapt with query SQL in list function - End by TTM
		$uid = $foreign[$this->foreign_keyid];
		if ($this->foreign_token !== FALSE) {
			if ($this->foreign_token === TRUE) {
				$this->foreign_token = 'token';
			}
			$token = $foreign[$this->foreign_token];
		}

		$this->db->set(array_intersect_key($foreign, $this->foreign_value))
		         ->where($this->foreign_keyid, $uid);
		if ($this->foreign_token !== FALSE) {
			$this->db->set($this->foreign_token, $this->create_token())
			         ->where($this->foreign_token, $token);
		}
		if ($this->table_expire !== FALSE) {
			$this->db->where('expired', '0');
		}

		$this->db->update($this->table_name);
		$result = ($this->db->affected_rows() <= 0) ? FALSE:$uid;
		if ($result != FALSE) {
			$line = $this->lang->line('LOG:M2002');
			log_message('notice', sprintf($line, $this->table_name, $uid));
			log_message('info', $this->db->last_query());
		} else {
			$line = $this->lang->line('LOG:N4002');
			log_message('notice', sprintf($line, $this->table_name, $uid));
			log_message('info', $this->db->last_query());
		}
		return $result;
	}

	//【データの削除】デフォルト
	protected function delete_record($foreign)
	{
		$foreign = $this->session->flashdata('foreign');
		$uid = $foreign[$this->foreign_keyid];
		if ($this->foreign_token !== FALSE) {
			$token = $foreign[$this->foreign_token];
		}
		if ($this->table_expire !== FALSE) {
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE)
			         ->set('expired', 1)
			         ->where('expired', 0)
			         ->where($this->foreign_keyid, $uid);
			$this->db->update($this->table_name);
		} else {
			$this->db->where($this->foreign_keyid, $uid);
			$this->db->delete($this->table_name);
		}

		if ($this->table_expire) {
			$this->db
				->set('expired', 1)
				->set('deleted', 'CURRENT_TIMESTAMP', FALSE)
				->where('expired', 0)
				->where($this->foreign_keyid, $uid);
			if ($this->foreign_token !== FALSE) {
				$this->db->set($this->foreign_token, $this->create_token())
				         ->where($this->foreign_token, $token);
			}
			$query = $this->db->update($this->table_name);
//			$query = ($this->db->affected_rows() <= 0) ? FALSE:TRUE;
		} else {
			$this->db->where($this->foreign_keyid, $uid);
			$query = $this->db->delete($this->table_name);
		}
		log_message('notice', $this->db->affected_rows());
		log_message('notice', var_export($query,TRUE));

		// 結果
		if ($query === FALSE) {
			$result = FALSE;
		} else {
			$result = $uid;
		}
		if ($result !== FALSE) {
			$line = $this->lang->line('LOG:M2003');
			log_message('notice', sprintf($line, $this->table_name, $uid));
			log_message('info', $this->db->last_query());
		} else {
			$line = $this->lang->line('LOG:N4003');
			log_message('notice', sprintf($line, $this->table_name, $uid));
			log_message('info', $this->db->last_query());
		}
		return $result;
	}

	/**
	 * 操作履歴の追加
	 *
	 * @param $action
	 * @param $uid
	 */
	protected function log_history($action, $uid='')
	{
		$label = $this->lang->line($this->table_name);
		$state = ($uid == '' ? '':'('.$uid.')');
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->histories_model->log($this->member->get_userid(), $action, $label.$state);
		$this->histories_model->log($this->member_lib->get_userid(), $action, $label.$state);
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
	}
}

// ------------------------------------------------------------------------

/**
 * Member Operation Controller Class
 *
 * 会員がデータベースの登録を行うときのクラス
 * この操作には確認画面は基本的には存在する
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/general/controllers.html
 */

class MemOP_Controller extends RecOP_Controller {

	public function regist_in()
	{
		$this->check_action();

		$data = $this->setup_data();
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
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
		if (!isset($data['message']) || empty($data['message'])) {
			$this->check_logic($data);
		}

		// 入力不備ならリダイレクト
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/regist');
		}

		// 確認画面にリダイレクト
		redirect(uri_redirect_string() . '/regist_confirm');
	}

	public function regist_confirm()
	{
		$this->_confirm($this->form_prefix.'_'.__FUNCTION__);
	}

	public function regist_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['post'] = $this->input->post();
		$this->session->keep_flashdata('foreign');

		log_message('debug', var_export($data['foreign'],TRUE));

		// データベースに登録
		$result = $this->create_record($data['foreign']);

		$line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);

		if ($result !== FALSE) {
			$this->log_history('登録', $result);
			$this->after_regist($data);
		}

		// 登録完了画面へ
		redirect(uri_redirect_string() . '/registed');
	}

	public function change_in()
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

		// 確認画面にリダイレクト
		redirect(uri_redirect_string() . '/change_confirm');
	}

	public function change_confirm()
	{
		$this->_confirm($this->form_prefix.'_'.__FUNCTION__);
	}

	public function change_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['post'] = $this->input->post();
		$this->session->keep_flashdata('foreign');

		// データベースを更新
		$result = $this->update_record($data['foreign']);
		$line = $this->lang->line($result !== FALSE ? 'M2002':'N4002');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result === FALSE ? '0':'1'));

		if ($result !== FALSE) {
			$this->log_history('変更', $result);
			$this->after_change($data);
		}

		// 登録完了画面へ
		redirect(uri_redirect_string() . '/changed');
	}

	//【処理完了画面】
	protected function _confirm($template)
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($template, $data);
	}

	//【ヘルパ：Excelダウンロード】
	protected function download_xls_from_result($query, $nodename)
	{
		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
		// $this->load->library('excel');
		// $this->excel->setActiveSheetIndex(0);
		// $sheet = $this->excel->getActiveSheet();
		$this->load->library('Excel_lib');
		$this->excel_lib->setActiveSheetIndex(0);
		$sheet = $this->excel_lib->getActiveSheet();
		// Upgrade PHP7 - Rename class to make it loadable - End by TTM
		$sheet->getpageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$sheet->getpageSetup()->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
		$sheet->setTitle($nodename);
		$sheet->getDefaultStyle()->getFont()->setName('ＭＳ Ｐゴシック');
		$sheet->getDefaultStyle()->getFont()->setSize(11);

		$cell_style = array('numberformat' => array('code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT));
		$line = 1;
		foreach($query->result_array() as $row) {
			if ($line == 1) {
			    $column = 0;
			    foreach ($row as $key=>$val) {
			        $sheet->getStyleByColumnAndRow($column, $line)->applyFromArray($cell_style);
			        $sheet->setCellValueExplicitByColumnAndRow($column, $line, $key, PHPExcel_Cell_DataType::TYPE_STRING);
			        $column++;
				}
				$line++;
			}
		    $column = 0;
		    foreach ($row as $key=>$val) {
		        $sheet->getStyleByColumnAndRow($column, $line)->applyFromArray($cell_style);
		        $sheet->setCellValueExplicitByColumnAndRow($column, $line, $val, PHPExcel_Cell_DataType::TYPE_STRING);
		        $column++;
		    }
		    $line++;
		}

		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
		// $xls = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
		$xls = PHPExcel_IOFactory::createWriter($this->excel_lib, 'Excel2007');
		// Upgrade PHP7 - Rename class to make it loadable - Start by TTM

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$nodename.'.xlsx"');
		header('Cache-Control: max-age=0');
		$xls->save("php://output");
		exit;
	}
}

// ------------------------------------------------------------------------

/**
 * Public Operation Controller Class
 *
 * 非会員がデータベースの登録を行うときのクラス
 * この操作には確認画面は基本的には存在する
 *
 * @package		Cafelounge
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/general/controllers.html
 */

class PubOP_Controller extends MY_Controller {

	protected $form_prefix;   // フォーム名
	protected $table_name;    // テーブル名
	protected $table_expire;  // テーブルが論理削除の場合TRUE
	protected $table_prefix;  // テーブルの払出キー名(システムで一意)
	protected $foreign_keyid; // テーブルの主キーとなるカラム名
	protected $foreign_value; // 入力チェック用に使用するカラムとパターン

	public function index()
	{
		redirect('./regist');
	}

	//【詳細画面】
	public function detail($ticket='')
	{

		// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
		// $this->load->library('encrypt');
		// @$this->encrypt->set_cipher(MCRYPT_BLOWFISH);
		// $ticket = str_replace(array('_','-'), array('+','/'), $ticket);
		// $uid = $this->encrypt->decode($ticket . "==");
		$this->load->library('encryption');
		$this->encryption->initialize(
			array(
					'cipher' => 'blowfish',
					'mode' => 'cbc'
			)
		);
		$ticket = str_replace(array('_','-'), array('+','/'), $ticket);
		$uid = $this->encryption->decrypt($ticket . "==");
		// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
		if ($uid === FALSE) {
			$this->parser->parse($this->form_prefix.'_error', $data);
			return;
		}

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->setup_form_ex($data);
			$this->parser->parse($this->form_prefix.'_error', $data);
			return;
		}
		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【登録画面】
	public function regist()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【登録確認】
	public function regist_in()
	{
		$this->check_action();

		$data = $this->setup_data();
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
		$this->session->set_flashdata('foreign', $data['foreign']);

		// 入力成功後のロジックチェックしたい場合
		if (!isset($data['message']) || empty($data['message'])) {
			$this->check_logic($data);
		}

		// 入力不備ならリダイレクト
		if (isset($data['message']) && !empty($data['message'])) {
			$this->session->set_flashdata('message', $data['message']);
			log_message('notice', var_export($data,TRUE));
			redirect(uri_redirect_string() . '/regist', 'location', 302);
		}

		// 確認画面にリダイレクト
		redirect(uri_redirect_string() . '/regist_confirm');
	}

	//【登録確認画面】
	public function regist_confirm()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【登録処理】
	public function regist_confirm_in()
	{
		$this->check_action();

		$data = $this->setup_data();
		$data['foreign'] = $this->session->flashdata('foreign');
		$this->session->keep_flashdata('foreign');

		// データベースに登録
		$result = $this->create_record($data['foreign']);
		$line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result !== FALSE) ? '1':'0');

		if ($result !== FALSE) {
			$this->log_history('登録', $result);
			$this->after_regist($data);
		}
		// 登録完了画面へ
		redirect(uri_redirect_string() . '/registed');
	}

	//【登録完了画面】
	public function registed()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['foreign'] = $this->session->flashdata('foreign');
		$data['message'] = $this->session->flashdata('message');
		if (!empty($data['message'])) {
			$data['title'] = array_shift($data['message']);
		}
		$this->session->keep_flashdata('foreign');
		$this->session->keep_flashdata('message');

		$this->setup_form_ex($data);
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	//【データの取得】デフォルト
	protected function get_record(&$data, $uid)
	{
		$this->db->where($this->foreign_keyid, $uid);
		if ($this->table_expire)
			$this->db->where('expired', 0);

		$query = $this->db->get($this->table_name);
		if ($query !== FALSE && $query->num_rows() == 1) {
			$data['foreign'] = $query->row_array();
		} else {
			$data['foreign'] = array();
			log_message('notice', $this->db->last_query());
		}
	}

	//【データの登録】デフォルト
	protected function create_record(&$foreign)
	{
		// Upgrade CI3 - Set exhid to NULL to adapt with query SQL in list function - Start by TTM
		if(empty($foreign['exhid']))
			$foreign['exhid'] = NULL;
		// Upgrade CI3 - Set exhid to NULL to adapt with query SQL in list function - End by TTM
		// シリアルキーが必要な場合は、先に主キーを取得しておく
		if ($this->foreign_keyid !== FALSE && $this->table_prefix !== FALSE) {
			$uidname = $this->table_name . "." . $this->foreign_keyid;
			$this->db->set("nextuid('" . $uidname . "','" . $this->table_prefix . "') AS uid", FALSE);
			$query = $this->db->get();
			if ($query !== FALSE && $query->num_rows() == 1) {
				$row = $query->row_array();
				$uid = $row['uid'];
			}
			$query->free_result();

			if (!isset($uid) && $uid = '') {
				$line = $this->lang->line('LOG:N4001');
				log_message('notice', sprintf($line, $this->table_name));
				log_message('info', $this->db->last_query());
				return FALSE;
			}
		}

		// データの作成
		$this->db->set(array_intersect_key($foreign, $this->foreign_value))
                 ->set('created', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->insert($this->table_name);

		// 結果
		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		} else {
			$result = isset($foreign[$this->foreign_keyid]) ? $foreign[$this->foreign_keyid]:$this->db->insert_id();
		}
		if ($result !== FALSE) {
			$line = $this->lang->line('LOG:M2001');
			log_message('notice', sprintf($line, $this->table_name, $result));
			log_message('info', $this->db->last_query());
		} else {
			$line = $this->lang->line('LOG:N4001');
			log_message('notice', sprintf($line, $this->table_name));
			log_message('info', $this->db->last_query());
		}
		return $result;
	}

	/**
	 * 操作履歴の追加
	 *
	 * @param $action
	 * @param $uid
	 */
	protected function log_history($action, $uid='')
	{
		$this->load->model('histories_model');
		$label = $this->lang->line($this->table_name);
		$state = ($uid == '' ? '':'('.$uid.')');
		// Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
		if(!isset($action)) $action = NULL;
		// Upgrade PHP7 - Fix bug "Undefined variable" - End by TTM
		$this->histories_model->log(0, $action, $label.$state);
	}
}

class ExhOP_Controller extends MemOP_Controller
{
    public function download($mode='')
    {
        $data = $this->setup_data();
        $this->setup_form($data);

		// 防御
		if (uri_folder_string() == '/ex') {
			die('Prohibited');
		}

        if ($mode == 'csv') {
            $datestr = date('YmdHi');
            $filename = strtolower(get_class($this)).'-all-'.$datestr.'.csv';
            $data = $this->download_csv();
            $data = mb_convert_encoding($data,'SJIS-win','UTF-8');
            $this->load->helper('download');
            force_download($filename, $data);
        } else if ($mode == 'xlsx') {
            $data = $this->download_xlsx();
        }
    }
    protected function download_csv()
    {
        $this->load->dbutil();

        $this->download_build();
        $query = $this->db->get();
        if ($query === FALSE) {
            return 'error';
        }
        $this->load->helper('form');
        return deco_csv_from_result($query);
    }

    protected function download_xlsx()
    {
        $this->load->dbutil();

        $this->download_build();
        $query = $this->db->get();
        if ($query === FALSE) {
            return 'error';
        }
        return $this->download_xls_from_result($query, strtolower(get_class($this)));
    }
}
/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
