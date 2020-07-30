<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class E13equipment_model extends CI_Model
{
	protected $CI;
	private $table_name = 'v_exapply_13';
	private $data_main = array(
				'exhboothid'      =>'exhboothid',
				'appno'   =>'appno',
				'seqno'   =>'seqno',
				'billid'=>'billid',
				'token'      =>'',
	);
	private $data_detail = array(
				'exhboothid'      =>'exhboothid',
				'appno'   =>'appno',
				'seqno'   =>'seqno',
				'unitcode'   =>'unitcode',
				'unitname'   =>'unitname',
				'unitprice'   =>'unitprice',
				'quantity'   =>'quantity',
				'price'=>'price',
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

		$foreign['appno']=13;
		$foreign['token'] = $this->create_token($foreign['exhboothid']);

		// 品番
		$unitcode=array(
			'1'=>"GR-615",
			'2'=>"BC-38-A",
			'3'=>"BC-38-B",
			'4'=>"BC-38-C",
			'5'=>"BC-38-D",
			'6'=>"A4",
			'7'=>"B4",
		);

		// 品名
		$unitname=array(
			'1'=>"インフォメーションカウンター[カギつき]",
			'2'=>"スタンド椅子A",
			'3'=>"スタンド椅子B",
			'4'=>"スタンド椅子C",
			'5'=>"スタンド椅子D",
			'6'=>"カタログスタンドA4",
			'7'=>"カタログスタンドB4",
		);

		// 単価
		$unitprice=array(
			'1'=>15000,
			'2'=>3500,
			'3'=>3500,
			'4'=>3500,
			'5'=>3500,
			'6'=>5500,
			'7'=>5500,
		);

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'quantity')){
				if($foreign_data!=NULL){
					// Upgrade PHP7 - Fix "Undefined index error" - Start by TTM
					// $quantity_array=split("_",$foreign_key);
					$quantity_array=explode("_",$foreign_key);
					// Upgrade PHP7 - Fix "Undefined index error" - End by TTM
					$foreign2[$quantity_array[1]]['unitcode']=$unitcode[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitname']=$unitname[$quantity_array[1]];
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
			$this->db->insert('v_exapply_13');
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
				$this->db->set('appno', 13);
				$this->db->set('seqno', $seqno);
				$this->db->set('token', $this->create_token());
				$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
				$this->db->insert('v_exapply_13_detail');
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

		// 新規に詰める
        $foreign['token'] = $this->create_token($keyid);
	    $foreign['seqno'] = 0;

		// 品番
		$unitcode=array(
			'1'=>"GR-615",
			'2'=>"BC-38-A",
			'3'=>"BC-38-B",
			'4'=>"BC-38-C",
			'5'=>"BC-38-D",
			'6'=>"A4",
			'7'=>"B4",
		);

		// 単価
		$unitprice=array(
			'1'=>15000,
			'2'=>3500,
			'3'=>3500,
			'4'=>3500,
			'5'=>3500,
			'6'=>5500,
			'7'=>5500,
		);

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'quantity')){
				if($foreign_data != NULL){
					// Upgrade PHP7 - Silence “Call to undefined function split” error in PHP 7 - Start by TTM
					// $quantity_array=split("_",$foreign_key);
					$quantity_array=explode("_",$foreign_key);
					// Upgrade PHP7 - Silence “Call to undefined function split” error in PHP 7 - End by TTM
					$foreign2[$unitcode[$quantity_array[1]]]['unitcode']=$unitcode[$quantity_array[1]];
					// Upgrade PHP7 - Silence “Undefined variable” error in PHP 7 - Start by TTM
					// $foreign2[$unitcode[$quantity_array[1]]]['unitprice']=$unitprice[$quantity_array[1]];
					if(!empty($unitname))
						$foreign2[$unitcode[$quantity_array[1]]]['unitname']=$unitname[$quantity_array[1]];
					else 
						$foreign2[$unitcode[$quantity_array[1]]]['unitname']= NULL;		
					// Upgrade PHP7 - Silence “Undefined variable” error in PHP 7 - End by TTM
					$foreign2[$unitcode[$quantity_array[1]]]['unitprice']=$unitprice[$quantity_array[1]];
					$foreign2[$unitcode[$quantity_array[1]]]['quantity']=$foreign_data;
					$foreign2[$unitcode[$quantity_array[1]]]['price']=$foreign_data * $unitprice[$quantity_array[1]];
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

		$seqno = 1;
		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$this->db->select('seqno');
				$this->db->where('exhboothid', $keyid);
				$this->db->where('expired', '0');
				$this->db->where('seqno', $seqno);

				$query = $this->db->get('v_exapply_13_detail');
				if ($query->num_rows() > 0) {
					$seqdata = $query->row_array();
				} else {
					$seqdata = array();
				}

				if(isset($seqdata['seqno']) && $seqdata['seqno'] != '') {
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->where('exhboothid', $keyid);
					$this->db->where('unitcode', $foreign2_key);
					$this->db->where('expired', '0');
					if (!$this->db->update('v_exapply_13_detail')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
					}
				}else{
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->set('exhboothid', $foreign['exhboothid']);
					$this->db->set('appno', 13);
					$this->db->set('seqno', $seqno);
					$this->db->set('token', $this->create_token());
					$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->insert('v_exapply_13_detail');
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
		$this->db->update('v_exapply_13');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_13_detail');

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
