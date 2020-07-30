<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class E05passticket_model extends CI_Model
{
	protected $CI;
	private $table_name = 'v_exapply_05';
	private $data_main = array(
		'exhboothid'  =>'exhboothid',
		'appno'  =>'appno',
		'seqno'  =>'seqno',
		'billid' =>'billid',
		'token'  =>'',
	);
	private $data_detail = array(
		'exhboothid' =>'exhboothid',
		'appno'      =>'appno',
		'seqno'      =>'seqno',
		'itemcode'   =>'itemcode',
		'itemname'   =>'itemname',
		'quantity'   =>'quantity',
		'addquantity'=>'addquantity',
		'token'      =>'',
	);

	private $itemname = array(
		'1' => '搬入出リボン',
		'2' => '搬入出車輌証',
		'3' => '出展者パス',
		'4' => '特別招待券',
		'5' => '一般招待券',
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

		$foreign['appno']  = 5;
//		$foreign['billid'] = 0;
		$foreign['token']  = $this->create_token($foreign['exhboothid']);

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data)
		{
			if(strstr($foreign_key,'addquantity')) {
				if($foreign_data != NULL) {
					$addq_array = explode('_', $foreign_key);
					$foreign2[$addq_array[1]]['itemcode']=$addq_array[1];
					$foreign2[$addq_array[1]]['itemname']=$this->itemname[$addq_array[1]];
					$foreign2[$addq_array[1]]['quantity']=$foreign_data;
					$foreign2[$addq_array[1]]['addquantity']=$foreign_data;
				}
			}
		}

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			$this->db->set($this->filter_column($foreign, $this->data_main));
			$this->db->set('seqno', 0);
			$this->db->set('token', $this->create_token());
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('v_exapply_05');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			} else {
				$foreign['appid'] = $this->db->insert_id();
			}
			log_message('notice', $this->db->last_query());
		}

		// seqno(仮の値。さらなる追加処理が入った場合は改定する。)
		$seqno = 1;

		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data) {
				$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
				$this->db->set('exhboothid', $foreign['exhboothid']);
				$this->db->set('appno', 5);
				$this->db->set('seqno', $seqno);
				$this->db->set('token', $this->create_token());
				$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
				$this->db->insert('v_exapply_05_detail');
				if ($this->db->affected_rows() <= 0) {
					$result = FALSE;
					break;
				}
				$seqno++;
				log_message('notice', $this->db->last_query());
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

	function update($foreign)
	{
		$result = TRUE;

		// 今回使用するキーは予めとっておく
		$keyid = $foreign['exhboothid'];
		$token = $foreign['token'];

		// 新規に詰める
		$foreign['token'] = $this->create_token($keyid);
		$foreign['seqno'] = 0;

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'addquantity')){
				if($foreign_data != NULL){
					$addq_array = explode("_",$foreign_key);
					$foreign2[$addq_array[1]]['itemcode']=$addq_array[1];
					$foreign2[$addq_array[1]]['itemname']=$this->itemname[$addq_array[1]];
					$foreign2[$addq_array[1]]['quantity']=$foreign['quantity_'.$addq_array[1]]+$foreign_data;
					$foreign2[$addq_array[1]]['addquantity']=$foreign_data;
				}
			}
		}

		// トランザクションの開始
		$this->db->trans_start();

		// 
		$seqno=1;
		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$seqdata=array();

				$this->db->select('seqno');
				$this->db->where('exhboothid', $keyid);
				$this->db->where('expired', '0');
				$this->db->where('itemcode', $foreign2_key);

				$query = $this->db->get('v_exapply_05_detail');
				if ($query->num_rows() > 0) {
					$seqdata = $query->result_array();
				}

				if(isset($seqdata[0]['seqno'])){
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->set('sent', '0');
					$this->db->set('sentdate', null);
					$this->db->where('exhboothid', $keyid);
					$this->db->where('itemcode', $foreign2_key);
					$this->db->where('expired', '0');
					if (!$this->db->update('v_exapply_05_detail')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
					}
				}else{
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->set('sent', '0');
					$this->db->set('sentdate', null);
					$this->db->set('exhboothid', $foreign['exhboothid']);
					$this->db->set('appno', 5);
					$this->db->set('seqno', $foreign2_key);
					$this->db->set('token', $this->create_token());
					$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->insert('v_exapply_05_detail');
					if ($this->db->affected_rows() <= 0) {
						$result = FALSE;
						break;
					}
				}

				$seqno++;
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

	function sentupdate($key, $token='')
	{
		// トランザクションの開始
		$this->db->trans_start();

		$this->db->set('sent', '1');
		$this->db->set('sentdate', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_05');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		$this->db->set('sent', '1');
		$this->db->set('sentdate', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_05_detail');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		// すべてうまくいったならコミットする
		if ($result === FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}

		return $this->db->affected_rows();
	}

	function delete($key, $token='')
	{
		// トランザクションの開始
		$this->db->trans_start();

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_05');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_05_detail');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		// すべてうまくいったならコミットする
		if ($result === FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}

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
			if (isset($foreign[$val])) {
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

	/*
	* e05passticketより呼出
	* 2011-11-12追加
	*/	
	function read_detail()
	{
        $tickets = array();

        $this->db->select('appid, exhboothid, seqno, quantity, itemcode, itemname, updated');
        $query = $this->db->get('v_exapply_05_detail_ro');
        if ($query !== FALSE && $query->num_rows() > 0) {
            $result = $query->result_array();
            foreach($result as $record) {
                $tickets[$record['appid']] = $record;
            }
		}
		return $tickets;
	}
}

/* End of file e05passticket_model.php */
/* Location: ./application/models/e05passticket_model.php */
