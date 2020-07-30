<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class E03present_model extends CI_Model
{
	protected $CI;
	private $table_name = 'v_exapply_03';
	private $data_main = array(
				'exhboothid'      =>'exhboothid',
				'appno'   =>'appno',
				'seqno'   =>'seqno',
				'corpname'   => 'corpname',
				'zip'           => 'zip',
				'prefecture'   => 'prefecture',
				'address1'   => 'address1',
				'address2'   => 'address2',
				'phone'   => 'phone',
				'fax'   => 'fax',
				'token'      =>'',
	);
	private $data_detail = array(
				'exhboothid'      =>'exhboothid',
				'appno'   =>'appno',
				'seqno'   =>'seqno',
				'itemname'   =>'itemname',
				'itemprice'   =>'itemprice',
				'quantity'   =>'quantity',
				'itemphoto'   =>'itemphoto',
				'token'      =>'',
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

		$foreign['appno'] = 3;
		$foreign['token'] = $this->create_token($foreign['exhboothid']);

		$param = array(
			'itemname',
			'itemprice',
			'quantity',
//			'itemphoto',
		);
		$param_count=count($param)-1;

		// データ整理
		$a=1;
		$max_a=1;
		foreach($foreign as $foreign_key=>$foreign_data){
			foreach($param as $param_key=>$param_data){
				if($foreign_key==$param_data.$a){
					if($foreign_data){
						$foreign2[$a][$param_data]=$foreign_data;
					}
					if($param_key==$param_count){
						$a++;
					}
                    if ($a > $max_a) {
                        $max_a = $a;
                    }
					break;
				}

			}
		}
        for ($b = 1; $b < $max_a; $b++) {
            if (isset($foreign["itemphoto".$b])) {
                $value = $foreign["itemphoto".$b];
                if ($value) {
                    $foreign2[$b]['itemphoto']=$foreign["itemphoto".$b];
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
			$this->db->insert('v_exapply_03');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			} else {
				$foreign['appid'] = $this->db->insert_id();
			}
		}

		// seqno(仮の値。さらなる追加処理が入った場合は改定する。)
		$seqno=1;

		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
				$this->db->set('exhboothid', $foreign['exhboothid']);
				$this->db->set('appno', 3);
				$this->db->set('seqno', $seqno);
				$this->db->set('token', $this->create_token());
				$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
				$this->db->insert('v_exapply_03_detail');
				if ($this->db->affected_rows() <= 0) {
					$result = FALSE;
					break;
				}
				$seqno++;
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

		$param = array(
			'itemname',
			'itemprice',
			'quantity',
//			'itemphoto',
		);
		$param_count=count($param)-1;

		// データ整理
		$a=1;
		$max_a = 1;
		foreach($foreign as $foreign_key=>$foreign_data){
			foreach($param as $param_key=>$param_data){
				if($foreign_key==$param_data.$a){
					if($foreign_data){
						$foreign2[$a][$param_data]=$foreign_data;
					}
					if($param_key==$param_count){
						$a++;
					}
					if ($a > $max_a) {
					    $max_a = $a;
                    }

					break;
				}


			}
		}
		for ($b = 1; $b < $max_a; $b++) {
		    if (isset($foreign["itemphoto".$b])) {
		        $value = $foreign["itemphoto".$b];
		        if ($value) {
                    $foreign2[$b]['itemphoto']=$foreign["itemphoto".$b];
                }
            }
        }


		// トランザクションの開始
		$this->db->trans_start();

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

		$seqno=1;
		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$seqdata=array();

				$this->db->select('appid,seqno');
				$this->db->where('exhboothid', $keyid);
				$this->db->where('expired', '0');
				$this->db->where('seqno', $seqno);

				$query = $this->db->get('v_exapply_03_detail');
				if ($query->num_rows() > 0) {
					$seqdata = $query->result_array();
				}

				if($seqdata[0]['seqno']!=NULL){
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->where('exhboothid', $keyid);
					$this->db->where('seqno', $seqno);
					$this->db->where('expired', '0');
					if (!$this->db->update('v_exapply_03_detail')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
					}
				}else{
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->set('exhboothid', $foreign['exhboothid']);
					$this->db->set('appno', 3);
					$this->db->set('seqno', $seqno);
					$this->db->set('token', $this->create_token());
					$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->insert('v_exapply_03_detail');
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

	function delete($key, $token='')
	{
		// トランザクションの開始
		$this->db->trans_start();

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_03');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_03_detail');

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
}

/* End of file exhibitors_model.php */
/* Location: ./application/models/exhibitors_model.php */
