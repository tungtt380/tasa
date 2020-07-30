<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class E11avrental_model extends CI_Model
{
	protected $CI;
	private $table_name = 'v_exapply_11';
	private $data_main = array(
				'exhboothid'    =>'exhboothid',
				'appno'  	 	=>'appno',
				'seqno'     	=>'seqno',
				'billid'		=>'billid',
				'contact'		=>'contact',
				'c_corpname'    =>'corpname',
				'c_zip'         =>'zip',
				'c_prefecture'  =>'prefecture',
				'c_address1'    =>'address1',
				'c_address2'    =>'address2',
				'c_fullname'    =>'fullname',
				'c_phone'       =>'phone',
				'c_fax'         =>'fax',
				'token'         =>'',
	);
	private $data_detail = array(
				'exhboothid'    =>'exhboothid',
				'appno'         =>'appno',
				'seqno'         =>'seqno',
				'unitcode'      =>'unitcode',
				'unitname'      =>'unitname',
				'unitprice'     =>'unitprice',
				'quantity'      =>'quantity',
				'price'	      	=>'price',
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

		$foreign['appno'] = 11;
		$foreign['token'] = $this->create_token($foreign['exhboothid']);

		$itemname=array(
			'1'=>"PDP40",
			'2'=>"PDP30",
			'3'=>"PDP20",
			'4'=>"PASET-A",
			'5'=>"PASET-B",
		);

		$unitprice=array(
			'1'=>105000,
			'2'=>85000,
			'3'=>40000,
			'4'=>80000,
			'5'=>130000,
		);

		$token=$this->create_token();

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'quantity')){
				if($foreign_data!=NULL){
					$quantity_array=split("_",$foreign_key);
					$foreign2[$quantity_array[1]]['unitcode']=$itemname[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitname']=$itemname[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitprice']=$unitprice[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['quantity']=$foreign_data;
					$foreign2[$quantity_array[1]]['price']=$foreign_data * $unitprice[$quantity_array[1]];
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
			$this->db->insert('v_exapply_11');
			if ($this->db->affected_rows() <= 0) {
				$result = FALSE;
			}
		}

		// seqno(仮の値。さらなる追加処理が入った場合は改定する。)
		$seqno=1;

		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
				$this->db->set('exhboothid', $foreign['exhboothid']);
				$this->db->set('appno', 11);
				$this->db->set('seqno', $seqno);
				$this->db->set('token', $this->create_token());
				$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
				$this->db->insert('v_exapply_11_detail');
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
		log_message('notice', var_export($foreign,TRUE));

		// 今回使用するキーは予めとっておく
	        $keyid = $foreign['exhboothid'];
        	$token = $foreign['token'];
			
		$foreign['appno']=11;
		$foreign['token'] = $this->create_token($foreign['exhboothid']);

		$itemname=array(
			'1'=>"PDP40",
			'2'=>"PDP30",
			'3'=>"PDP20",
			'4'=>"PASET-A",
			'5'=>"PASET-B",
		);

		$unitprice=array(
			'1'=>105000,
			'2'=>85000,
			'3'=>40000,
			'4'=>80000,
			'5'=>130000,
		);

		$token=$this->create_token();

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'quantity')){
				if($foreign_data!=NULL){
					$quantity_array=split("_",$foreign_key);
					$foreign2[$quantity_array[1]]['unitcode']=$itemname[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitname']=$itemname[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitprice']=$unitprice[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['quantity']=$foreign_data;
					$foreign2[$quantity_array[1]]['price']=$foreign_data * $unitprice[$quantity_array[1]];
				}
			}
		}

		// 新規に詰める
       	$foreign['token'] = $this->create_token($keyid);
//	    $foreign['seqno'] = 0;

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			$this->db->set(array_intersect_key($foreign, $this->data_main));
			$this->db->where('exhboothid', $keyid);
			$this->db->where('token', $token);
			$this->db->where('expired', '0');
			if (!$this->db->update('v_exapply_11')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		// seqno(仮の値。さらなる追加処理が入った場合は改定する。)
		$seqno=1;

		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$seqdata=array();

				$this->db->select('seqno');
				$this->db->where('exhboothid', $keyid);
				$this->db->where('unitcode', $foreign2_data['unitcode']);
				$this->db->where('expired', '0');
				$this->db->where('seqno', $seqno);

				$query = $this->db->get('v_exapply_11_detail');
				if ($query->num_rows() > 0) {
					$seqdata = $query->result_array();
				}
				if($seqdata[0]['seqno']!=NULL){
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->where('exhboothid', $keyid);
					$this->db->where('unitcode', $foreign2_data['unitcode']);
					$this->db->where('expired', '0');

					if (!$this->db->update('v_exapply_11_detail')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
					}
				}else{
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->set('exhboothid', $foreign['exhboothid']);
					$this->db->set('appno', 11);
					$this->db->set('seqno', $seqno);
					$this->db->set('token', $this->create_token());
					$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->insert('v_exapply_11_detail');
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
		$this->db->update('v_exapply_11');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_11_detail');

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
