<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Exhibitors_model extends CI_Model
{
	protected $CI;
	private $table_name = 'exhibitors';
	private $data_e = array(
				'exhid'      =>'exhid',
				'corpname'   =>'',
				'corpkana'   =>'',
				'countrycode'=>'',
				'zip'        =>'',
				'prefecture' =>'',
				'address1'   =>'',
				'address2'   =>'',
				'position'   =>'',
				'fullname'   =>'',
				'fullkana'   =>'',
				'phone'      =>'',
				'fax'        =>'',
				'url'        =>'',
				'brandname'  =>'',
				'brandkana'  =>'',
				'promotion'  =>'',
				'comment'    =>'',
				'remark'    =>'',
				'accepted'   =>'',
				'token'      =>'',
	);
	private $data_m = array(
				'exhid'      =>'exhid',
				'corpname'   =>'m_corpname',
				'corpkana'   =>'m_corpkana',
				'countrycode'=>'m_countrycode',
				'zip'        =>'m_zip',
				'prefecture' =>'m_prefecture',
				'address1'   =>'m_address1',
				'address2'   =>'m_address2',
				'division'   =>'m_division',
				'position'   =>'m_position',
				'fullname'   =>'m_fullname',
				'fullkana'   =>'m_fullkana',
				'phone'      =>'m_phone',
				'fax'        =>'m_fax',
				'mobile'     =>'m_mobile',
				'email'      =>'m_email',
	);
	private $data_b = array(
				'exhid'      =>'exhid',
				'seqno'      =>'seqno',
				'corpname'   =>'b_corpname',
				'corpkana'   =>'b_corpkana',
				'countrycode'=>'b_countrycode',
				'zip'        =>'b_zip',
				'prefecture' =>'b_prefecture',
				'address1'   =>'b_address1',
				'address2'   =>'b_address2',
				'division'   =>'b_division',
				'position'   =>'b_position',
				'fullname'   =>'b_fullname',
				'fullkana'   =>'b_fullkana',
				'phone'      =>'b_phone',
				'fax'        =>'b_fax',
	);
	private $data_c = array(
				'exhid'      =>'exhid',
				'corpname'   =>'c_corpname',
				'corpkana'   =>'c_corpkana',
				'countrycode'=>'c_countrycode',
				'zip'        =>'c_zip',
				'prefecture' =>'c_prefecture',
				'address1'   =>'c_address1',
				'address2'   =>'c_address2',
				'division'   =>'c_division',
				'position'   =>'c_position',
				'fullname'   =>'c_fullname',
				'fullkana'   =>'c_fullkana',
				'phone'      =>'c_phone',
				'fax'        =>'c_fax',
				'mobile'     =>'c_mobile',
				'email'      =>'c_email',
	);
	private $data_d = array(
				'exhid'      =>'exhid',
				'corpname'   =>'d_corpname',
				'corpkana'   =>'d_corpkana',
				'countrycode'=>'d_countrycode',
				'zip'        =>'d_zip',
				'prefecture' =>'d_prefecture',
				'address1'   =>'d_address1',
				'address2'   =>'d_address2',
				'division'   =>'d_division',
				'position'   =>'d_position',
				'fullname'   =>'d_fullname',
				'fullkana'   =>'d_fullkana',
				'phone'      =>'d_phone',
				'fax'        =>'d_fax',
	);

	function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
		$this->table_name = $this->CI->config->item('dbprefix') . $this->table_name;
	}

	function create(&$foreign, $statusno=200, $route='W')
	{
		$result = TRUE;

		// トランザクションを貼る前に払出の識別子は先にとっておく
		$this->db->select("nextuid('exhibitor_bill.billid','B') AS billid", FALSE);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			$foreign['billid'] = $row['billid'];
		} else {
			$result = FALSE;
		}
		$query->free_result();

		$this->db->select("nextuid('exhibitors.exhid','S') AS exhid", FALSE);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			$foreign['exhid'] = $row['exhid'];
		} else {
			$result = FALSE;
		}
		$query->free_result();

		$foreign['token'] = $this->create_token($foreign['exhid']);

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			// 請求先：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_b));
			$this->db->set('billid', $foreign['billid']);
			$this->db->set('seqno', 0);
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('exhibitor_bill');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 責任者：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_m));
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('exhibitor_manager');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 連絡先：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_c));
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('exhibitor_contact');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 送付先：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_d));
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('exhibitor_dist');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		// カテゴリ、エントリ部門、展示予定台数、物販予定
		if ($result === TRUE) {
			if (!isset($foreign['q_entrycars']) || $foreign['q_entrycars'] == '') {
				$foreign['q_entrycars'] = 0;
			}
			if (!isset($foreign['q_salesitem'])) {
				$foreign['q_salesitem'] = '';
			}
			$this->db->set('exhid', $foreign['exhid']);
			$this->db->set('category', $this->filter_category($foreign));
			$this->db->set('section', $this->filter_section($foreign));
			$this->db->set('entrycars', $foreign['q_entrycars']);
			$this->db->set('salesitem', $foreign['q_salesitem']);
			$this->db->insert('exhibitor_application');
		}

		// 小間形状
		if ($result === TRUE) {
			$record = array();
			for ($i=1; $i<=5; $i++) {
				if (isset($foreign['q_booth'.$i]) && $foreign['q_booth'.$i]  != '') {
					$record[] = array(
						'exhid'  => $foreign['exhid'],
						'seqno'  => $i,
						'boothid'=> $foreign['q_booth'.$i],
						'count'  => 1,
						'expired'=> 0,
					);
				}
			}
			// Upgrade CI3 - Fix error message not displayed in CI3 - Start by TTM
			if(empty($record)) $record = NULL;
			// Upgrade CI3 - Fix error message not displayed in CI3 - End by TTM
			$this->db->insert_batch('exhibitor_booth', $record);
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 出展者：入力値とカラムの共通項のみデータに登録する
			$data_e = $this->data_e;
			if (!isset($foreign['accepted']) || $foreign['accepted'] == '') {
				$this->db->set('accepted', 'CURRENT_TIMESTAMP', FALSE);
				unset($data_e['accepted']);
			}
			$this->db->set(array_intersect_key($foreign, $data_e));
			$this->db->set('statusno', $statusno);
			$this->db->set('route', $route);
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert($this->table_name);
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		// すべてうまくいったならコミットする
		if ($result === FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}

		return $result;
	}

	function read($uid)
	{
		$data = array();
		$data['foreign'] = array();

		// 出展者
		$this->db->where('exhid', $uid);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitors');
		if ($query->num_rows() <= 0) {
			return array();
		} 
		$data['foreign'] = $query->row_array();

		// 責任者
		$this->db->where('exhid', $uid);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_manager');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			foreach ($row as $key=>$val) {
				$data['foreign']['m_'.$key] = $val;
			}
		}

		// 請求先
		$this->db->where('exhid', $uid);
		$this->db->where('expired', 0);
				$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			foreach ($row as $key=>$val) {
				$data['foreign']['b_'.$key] = $val;
			}
		}

		// 連絡先
		$this->db->where('exhid', $uid);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_contact');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			foreach ($row as $key=>$val) {
				$data['foreign']['c_'.$key] = $val;
			}
		}

		// 送付先
		$this->db->where('exhid', $uid);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_dist');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			foreach ($row as $key=>$val) {
				$data['foreign']['d_'.$key] = $val;
			}
		}

		// カテゴリ、エントリ部門、展示予定台数、物販予定
		$this->db->where('exhid', $uid);
		$query = $this->db->get('exhibitor_application');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$data['foreign']['q_entrycars'] = $row['entrycars'];
			$data['foreign']['q_salesitem'] = $row['salesitem'];
			foreach(explode(',', $row['category']) as $val) {
				$data['foreign']['q_category_'.$val] = 'on';
			}
			foreach(explode(',', $row['section']) as $val) {
				$data['foreign']['q_section_'.$val] = 'on';
			}
		}

		// 小間形状
		$this->db->where('exhid', $uid);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_booth');
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				$i = $row['seqno'];
				$data['foreign']['q_booth'.$i] = $row['boothid'];
				$data['foreign']['q_boothid'.$i] = $row['exhboothid'];
				$data['foreign']['q_boothno'.$i] = $row['exhboothno'];
				$data['foreign']['q_boothcount'.$i] = $row['count'];
			}
		}

		return $data['foreign'];
	}

	function readAll()
	{
		$this->db->where('expired', '0');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($foreign)
	{
		$result = TRUE;

		// 今回使用するキーは予めとっておく
        $keyid = $foreign['exhid'];
        $token = $foreign['token'];

		// 新規に詰める
        $foreign['token'] = $this->create_token($keyid);
        $foreign['seqno'] = 0;

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			// 出展者：入力値とカラムの共通項のみデータに登録する
			$this->db->set(array_intersect_key($foreign, $this->data_e));
			$this->db->where('exhid', $keyid);
			$this->db->where('token', $token);
			$this->db->where('expired', '0');
			if (!$this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		if ($result === TRUE) {
			// 請求先：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_b));
			$this->db->where('exhid', $keyid);
			$this->db->where('seqno', '0');
			if (!$this->db->update('exhibitor_bill')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 責任者：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_m));
			$this->db->where('exhid', $keyid);
			if (!$this->db->update('exhibitor_manager')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 連絡先：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_c));
			$this->db->where('exhid', $keyid);
			if (!$this->db->update('exhibitor_contact')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 送付先：入力値とカラムの共通項のみデータに登録する
			$this->db->set($this->filter_column($foreign, $this->data_d));
			$this->db->where('exhid', $keyid);
			if (!$this->db->update('exhibitor_dist')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 申込内容(カテゴリ、エントリ、その他予定台数、物販予定)
			if ($foreign['q_entrycars'] == '') {
				$foreign['q_entrycars'] = 0;
			}
			$this->db->set('category', $this->filter_category($foreign));
			$this->db->set('section', $this->filter_section($foreign));
			$this->db->set('entrycars', $foreign['q_entrycars']);
			$this->db->set('salesitem', $foreign['q_salesitem']);
			$this->db->where('exhid', $keyid);
			if (!$this->db->update('exhibitor_application')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 小間形状
			for ($i=0; $i<=5; $i++) {
				if (isset($foreign['q_booth'.$i]) && $foreign['q_booth'.$i] != '') {
					// こちらは、挿入か更新をかならず行う
					$record = array(
						'exhid'=>$foreign['exhid'],
						'seqno'=>$i,
						'boothid'=>$foreign['q_booth'.$i],
						'count'=>1,
						'expired'=>0,
					);
					$this->db->set($record);
					$this->db->set('updated', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->where('exhid', $keyid);
					$this->db->where('seqno', $i);
					if($this->db->update('exhibitor_booth')) {
						if($this->db->affected_rows() == 0) {
							$this->db->set($record);
							if (!$this->db->insert('exhibitor_booth')) {
								log_message('notice', $this->db->last_query());
								$result = FALSE;
								break;
							}
						}
					} else {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
						break;
					}
				} else {
					// こちらは、空振りすることもある
					$this->db->set('expired', 1);
					$this->db->where('exhid', $keyid);
					$this->db->where('seqno', $i);
					if (!$this->db->update('exhibitor_booth')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
						break;
					}
				}
			}
		}

		// すべてうまくいったならコミットする
		if ($result == FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		return $result;
	}

	function delete($key, $token='')
	{
		// トランザクションの開始
		$this->db->trans_start();

		// 出展者の削除
		if ($result === TRUE) {
			$this->db->where('exhid', $key);
			$this->db->set('token', $this->create_token());
			$this->db->where('token', $token);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		// 出展者の他情報の削除
		if ($result === TRUE) {
			$this->db->where('exhid', $key);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update('exhibitor_bill')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		if ($result === TRUE) {
			$this->db->where('exhid', $key);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update('exhibitor_contact')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		if ($result === TRUE) {
			$this->db->where('exhid', $key);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update('exhibitor_manager')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		if ($result === TRUE) {
			$this->db->where('exhid', $key);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update('exhibitor_dist')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		// すべてうまくいったならコミットする
		if ($result == FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		return $result;
	}

	/**
	 * カラムのフィルタリング
	 * @param $foreign
	 * @param $column
	 */
	protected function filter_column($foreign, $column) {
		$record = array();
		foreach($column as $key=>$val) {
//			if (isset($foreign[$val]) || is_null($foreign[$val])) {
			if (array_key_exists($val, $foreign)) {
				$record[$key] = $foreign[$val];
			}
		}
		return $record;
	}

	/**
	 * カテゴリのフィルタリング
	 * @param $foreign
	 * @param $column
	 */
	protected function filter_category($foreign) {
        $this->CI->load->model('category_model');
        $category = $this->CI->category_model->get_dropdown();

		$record = array();
		foreach($category as $key=>$val) {
			if (isset($foreign['q_category_' . $key]) && $foreign['q_category_'.$key] != '') {
				$record[] = $key;
			}
		}
		return implode(',', $record);
	}

	/**
	 * エントリ部門のフィルタリング
	 * @param $foreign
	 * @param $column
	 */
	protected function filter_section($foreign) {
        $this->CI->load->model('section_model');
        $section = $this->CI->section_model->get_dropdown();

		$record = array();
		foreach($section as $key=>$val) {
			if (isset($foreign['q_section_' . $key]) && $foreign['q_section_'.$key] != '') {
				$record[] = $key;
			}
		}
		return implode(',', $record);
	}

	/*
	* トークンの作成
	*/
    protected function create_token($seed = 'ZYX')
    {
        return base64_encode(sha1(uniqid(rand() . $seed), TRUE) . 'A');
    }
}

/* End of file exhibitors_model.php */
/* Location: ./application/models/exhibitors_model.php */
