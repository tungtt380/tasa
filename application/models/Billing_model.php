<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Billing_model extends CI_Model
{
	protected $CI;
	private $table_name = 'exhibitor_bill';
	private $data = array(
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

	function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
		$this->table_name = $this->CI->config->item('dbprefix') . $this->table_name;
	}

	function create(&$foreign)
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

		// トークンの作成
		$foreign['token'] = $this->create_token($foreign['exhid']);

		// トランザクションの開始
		$this->db->trans_start();

		// 請求先：入力値とカラムの共通項のみデータに登録する
		if ($result === TRUE) {
			$this->db->set($this->filter_column($foreign, $this->data));
			$this->db->set('billid', $foreign['billid']);
			$this->db->set('seqno', 999);
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('exhibitor_bill');
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

		// 請求先
		$this->db->where('billid', $uid);
		$this->db->where('expired', 0);
		$query = $this->db->get('exhibitor_bill');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			foreach ($row as $key=>$val) {
				$data['foreign'][$key] = $val;
			}
		}

		return $data['foreign'];
	}

	function readExhibitors($exhid)
	{
        $this->db->where('exhid', $exhid);
        $this->db->order_by('seqno', 'asc');
        $query = $this->db->get('exhibitor_bill');
        $data['lists'] = array();
        if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				$data['lists'][$row['billid']] = $row;
			}
		}
		return $data['lists'];
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
        $keyid = $foreign['billid'];
        $token = $foreign['token'];

		// 新規に詰める
        $foreign['token'] = $this->create_token($keyid);

		// トランザクションの開始
		$this->db->trans_start();

		// 請求先：入力値とカラムの共通項のみデータに登録する
		if ($result === TRUE) {
			$this->db->set($this->filter_column($foreign, $this->data));
			$this->db->where('billid', $keyid);
			if (!$this->db->update('exhibitor_bill')) {
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

	function delete($key, $token='')
	{
		// トランザクションの開始
		$this->db->trans_start();

		// 出展者の削除
		if ($result === TRUE) {
			$this->db->where('billid', $key);
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

		// すべてうまくいったならコミットする
		if ($result == FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		return $result;
	}

    function get_dropdown()
    {
        $this->db->order_by('seqno');
        $query = $this->db->get($this->table_name);
        $res = array();
        foreach($query->result_array() as $row) {
            $res[$row['spaceid']] = $row['spacename'];
        }
        return $res;
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

/* End of file billing_model.php */
/* Location: ./application/models/billing_model.php */
