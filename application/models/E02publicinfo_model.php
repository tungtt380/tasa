<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class E02publicinfo_model extends CI_Model
{
	protected $CI;
	private $table_name = 'v_exapply_02';
	private $data_main = array(
		'exhboothid'    => 'exhboothid',
		'appno'         => 'appno',
		'seqno'         => 'seqno',
		'brandnameen'   => 'brandnameen',
		'zip'           => 'zip',
		'prefecture'    => 'prefecture',
		'address1'      => 'address1',
		'address2'      => 'address2',
		'phone'         => 'phone',
		'fax'           => 'fax',
		'email'         => 'email',
		'url'           => 'url',
		'prcomment'     => 'prcomment',
		'publicaddress' => 'publicaddress',
		'publicphone'   => 'publicphone',
		'publicfax'     => 'publicfax',
		'publicurl'     => 'publicurl',
		'publicemail'   => 'publicemail',
		'token'         => '',
	);
	private $data_detail = array(
		'exhboothid'         =>'exhboothid',
		'appno'         =>'appno',
		'seqno'         =>'seqno',
		'photo'         =>'photo',
		'token'         =>'',
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

		// データ整理(概要レコード)
		$foreign['appno'] = 2;

		// データ整理(詳細レコード)
		$fdetail = array();
		$fdetail[] = array(
			'photo' => $foreign['photo'],
		);

		// トランザクションの開始
		$this->db->trans_start();

		// 出展者公開データの登録
		if ($result === TRUE) {
			$this->db->set($this->filter_column($foreign, $this->data_main));
			$this->db->set('seqno', 0);
			$this->db->set('token', $this->create_token());
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('v_exapply_02');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			} else {
				// Upgrade CI3 - Fix "Undefined variable" - Start by TTM
				// $foreign['appid'] = $appid;
				$foreign['appid'] = empty($appid)?NULL:$appid;
				// Upgrade CI3 - Fix "Undefined variable" - End by TTM
			}
		}

		// 写真の登録(現在は１つの写真のみ)
		if ($result === TRUE) {
			$seqno = 1;
			foreach($fdetail as $key=>$val) {
				if ($val['photo']) {
					$this->db->set($this->filter_column($val, $this->data_detail));
					$this->db->set('exhboothid', $foreign['exhboothid']);
					$this->db->set('appno', 2);
					$this->db->set('seqno', $seqno);
					$this->db->set('token', $this->create_token());
					$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
					if (!$this->db->insert('v_exapply_02_detail')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
						break;
					}
					if ($this->db->affected_rows() <= 0) {
						$result = FALSE;
						break;
					}
					$seqno++;
				}
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
		$fdetail['photo'] = $foreign['photo'];

		// トランザクションの開始
		$this->db->trans_start();

		// 出展者公開データの登録
		if ($result === TRUE) {
			$this->db->set(array_intersect_key($foreign, $this->data_main));
			$this->db->where('exhboothid', $keyid);
			$this->db->where('token', $token);
			$this->db->where('expired', '0');
			if (!$this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		// 写真の登録(現在は１つの写真のみ)
		if ($result === TRUE) {
			$seqno = 1;
//			foreach($fdetail as $fdetail_key=>$fdetail_data){
				if ($foreign['photo']) {
					// (Sato):この方法はI/Oに負荷をかけやすいので却下。
//					$this->db->select('seqno');
//					$this->db->where('exhboothid', $keyid);
//					$this->db->where('expired', '0');
//					$this->db->where('seqno', '1');
//
//					$query = $this->db->get('v_exapply_02_detail');
//					if ($query->num_rows() > 0) {
//						$seqdata = $query->result_array();
//					}
//					if (isset($seqdata[0]['seqno']) && $seqdata[0]['seqno'] != '') {
					if ($result === TRUE) {
						$this->db->set($this->filter_column($fdetail, $this->data_detail));
						$this->db->where('exhboothid', $keyid);
						$this->db->where('expired', '0');
						if (!$this->db->update('v_exapply_02_detail')) {
							log_message('notice', $this->db->last_query());
							$result = FALSE;
						} else if ($this->db->affected_rows() <= 0) {
							$this->db->set($this->filter_column($fdetail, $this->data_detail));
							$this->db->set('exhboothid', $foreign['exhboothid']);
							$this->db->set('appno', 2);
							$this->db->set('seqno', 1);
							$this->db->set('token', $this->create_token());
							$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
							$this->db->insert('v_exapply_02_detail');
							if ($this->db->affected_rows() <= 0) {
								$result = FALSE;
								// Upgrade PHP7 - 'break' not in the 'loop' or 'switch' context - Start by TTM
								// break;
								// Upgrade PHP7 - 'break' not in the 'loop' or 'switch' context - End by TTM
							}
						}
					}
					$seqno++;
				}
//			}
		}

		// すべてうまくいったならコミットする
		if ($result === FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		return $result;
	}

	function delete($key, $token='')
	{
		// トランザクションの開始
		$result = TRUE;
		$this->db->trans_start();

		if ($result === TRUE) {
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('exhboothid', $key['exhboothid']);
			$this->db->where('expired', '0');
			if (!$this->db->update('v_exapply_02')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('exhboothid', $key['exhboothid']);
			$this->db->where('expired', '0');
			if (!$this->db->update('v_exapply_02_detail')) {
				log_message('notice', $this->db->last_query());
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
}

/* End of file e02publicinfo_model.php */
/* Location: ./application/models/e02publicinfo_model.php */
