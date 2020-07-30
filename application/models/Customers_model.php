<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customers_model extends CI_Model
{
	protected $CI;
	private $table_name = 'customers';
	private $data = array(
				'customerid' =>'customerid',
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
				'token'      =>'',
	);

	function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
		$this->table_name = $this->CI->config->item('dbprefix') . $this->table_name;
	}

	function create(&$foreign, $eventid=FALSE)
	{
		$result = TRUE;

		// トランザクションを貼る前に払出の識別子は先にとっておく
		$this->db->select("nextuid('customers.customerid','C') AS customerid", FALSE);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			$foreign['customerid'] = $row['customerid'];
		} else {
			$result = FALSE;
		}
		$query->free_result();

		$foreign['token'] = $this->create_token($foreign['customerid']);

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			// 顧客：入力値とカラムの共通項のみデータに登録する
			$this->db->set(array_intersect_key($foreign, $this->data));
			$this->db->set('tascount', 1);
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert($this->table_name);
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}
		if ($result === TRUE && $eventid !== FALSE) {
			// 展示ヒストリ：
			$this->db->set('customerid', $foreign['customerid']);
			$this->db->set('eventid', $eventid);
			$this->db->insert('customer_history');
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

		// 顧客
		$this->db->where('customerid', $uid);
		$this->db->where('expired', '0');
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() > 0) {
			$data['foreign'] = $query->row_array();
		}

		return $data['foreign'];
	}

	function readAll()
	{
		$this->db->where('expired', '0');

		$query = $this->db->get($this->table_name);

		return $query;
	}

	function update($foreign, $eventid=FALSE)
	{
		$result = TRUE;

		// 今回使用するキーは予めとっておく
        $keyid = $foreign['customerid'];
		if (array_key_exists('token', $foreign)) {
			$token = $foreign['token'];
		}

		// 新規に詰める
        $foreign['token'] = $this->create_token($keyid);

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			// 出展者：入力値とカラムの共通項のみデータに登録する
			$this->db->set(array_intersect_key($foreign, $this->data));
			if (!isset($foreign['promotion']) || $foreign['promotion'] == '') {
				$this->db->set('tascount', 'tascount+1', FALSE);
				$this->db->set('tas', 'IF((tascount>2||tas>0),1,0)', FALSE);
			}
			$this->db->where('customerid', $keyid);
			if (isset($token) && $token != '') {
				$this->db->where('token', $token);
			}
			$this->db->where('expired', '0');
			if (!$this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		if ($result === TRUE && $eventid !== FALSE) {
			// 出展ヒストリ：何年に出展したかのメンテナンス
			// TODO: データベースに出展するがカウントに含めないものを入れる必要あり
			$this->db->set('customerid', $foreign['customerid']);
			if (!isset($foreign['promotion']) || $foreign['promotion'] == '') {
				$this->db->set('eventid', $eventid);
			} else {
				$this->db->set('eventid', $eventid);
				$this->db->set('promotion', $foreign['promotion']);
			}
			$this->db->insert('customer_history');
			if ($this->db->affected_rows() <= 0) {
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

	function delete($key, $token='')
	{
		$this->db->where('customerid', $key);
		$this->db->set('token', $this->create_token());
		$this->db->where('token', $token);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update($this->table_name);

		return $this->db->affected_rows();
	}

	/**
	 * カラムのフィルタリング
	 * @param $foreign
	 * @param $column
	 */
	protected function filter_column($foreign, $column) {
		$record = array();
		foreach($column as $key=>$val) {
			if (array_key_exists($val, $foreign)) {
				$record[$key] = $foreign[$val];
			}
		}
		return $record;
	}

	/*
	* トークンの作成
	*/
    protected function create_token($seed = 'ZYX')
    {
        return base64_encode(sha1(uniqid(rand() . $seed), TRUE) . 'A');
    }
}

/* End of file customers_model.php */
/* Location: ./application/models/customers_model.php */
