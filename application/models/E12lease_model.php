<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class E12lease_model extends CI_Model
{
	protected $CI;
	private $table_name = 'v_exapply_12';
	private $data_main = array(
		'exhboothid' =>'exhboothid',
		'appno'   	 =>'appno',
		'seqno'      =>'seqno',
		'billid'	 =>'billid',
		'token'      =>'',
	);
	private $data_detail = array(
		'exhboothid'  =>'exhboothid',
		'appno'       =>'appno',
		'seqno'       =>'seqno',
		'unitcode'    =>'unitcode',
		'unitname'    =>'unitname',
		'unitprice'   =>'unitprice',
		'quantity'    =>'quantity',
		'addquantity' =>'addquantity',
		'price'		  =>'price',
		'token'       =>'',
	);
	private $unitdb = array(
		 '11' => array('AS-5',			'応接セット',					'42000'),
		 '21' => array('AS-29A',		'応接セット',					'10000'),
		 '22' => array('AS-29C',		'応接セット',					'10000'),
		 '31' => array('AS-38',			'ティーレストセット',			'18000'),
		 '41' => array('AS-111W',		'ティーレストセット',			'13000'),
		 '42' => array('AS-111BK',		'ティーレストセット',			'13000'),
		 '51' => array('TS-960',		'ハイカウンターセット',			'16000'),//2016
		 '61' => array('BC-38-AW',		'スタンド椅子A',				'3500'),
		 '62' => array('BC-38-ABK',		'スタンド椅子A',				'3500'),
		 '63' => array('BC-38-BW',		'スタンド椅子B',				'3500'),
		 '64' => array('BC-38-BBK',		'スタンド椅子B',				'3500'),
		 '65' => array('BC-38-CW',		'スタンド椅子C',				'3500'),
		 '66' => array('BC-38-CBK',		'スタンド椅子C',				'3500'),
		 '67' => array('BC-38-DW',		'スタンド椅子D',				'3500'),
		 '68' => array('BC-38-DBK',		'スタンド椅子D',				'3500'),
		 '71' => array('BC-107-BR',		'パイプ椅子',					'600'),
		 '72' => array('BC-107-BL',		'パイプ椅子',					'600'),
		 '73' => array('BC-107-W',		'パイプ椅子',					'600'),
		 '81' => array('GR-633-A',		'受付カウンター(黒)',			'9000'),//2016
		 '82' => array('GR-633-B',		'受付カウンター(黒)',			'10000'),//2016
		 '91' => array('GR-601-A',		'受付カウンター',				'6000'),
		 '92' => array('GR-601-B',		'受付カウンター',				'7000'),
		'101' => array('GR-602-A',		'ユニットカウンター',			'12000'),
		'102' => array('GR-602-B',		'ユニットカウンター',			'12000'),
		'103' => array('GR-635-A',		'ユニットカウンター',			'13000'),
		'104' => array('GR-635-B',		'ユニットカウンター',			'13000'),
		'111' => array('DT-332',		'会議用テーブル',				'3500'),
		'112' => array('DT-334',		'会議用テーブル',				'3500'),
		'113' => array('DT-335',		'会議用テーブル',				'3500'),
		'121' => array('DT-330-S',		'丸テーブル(白)',				'4000'),//2016価格変更
		'122' => array('DT-330-M',		'丸テーブル(白)',				'4000'),//2016価格変更
		'123' => array('DT-330-L',		'丸テーブル(白)',				'4000'),//2016価格変更
		'131' => array('DT-326',		'商談テーブル',					'1500'),
		'141' => array('GR-328-S',		'角テーブル(黒)',				'3000'),
		'142' => array('GR-328-L',		'角テーブル(黒)',				'3000'),
		'151' => array('EP-488-R',		'ベルトパーティーション(赤)',	'6500'),//2016
		'152' => array('EP-488-BL',		'ベルトパーティーション(青)',	'6500'),//2016
		'153' => array('EP-488-BR',		'ベルトパーティーション(黒)',	'6500'),//2016
		'161' => array('EP-8488-R',		'ローパーティーション(赤)',		'6500'),//2016
		'162' => array('EP-8488-BL',	'ローパーティーション(青)',		'6500'),//2016
		'163' => array('EP-8488-BR',	'ローパーティーション(黒)',		'6500'),//2016
		'171' => array('EP-421',		'ポールパーティーション',		'2000'),//2016
		'181' => array('EP-422',		'ローパーティーション',			'2000'),//2016
		'191' => array('EP-425-C',		'ポール用チェーン 1m',			'300'),//2016名称・価格変更
		'192' => array('EP-425-D',		'ポール用チェーン 1m',			'300'),//2016名称・価格変更
		'193' => array('EP-425-E',		'ポール用チェーン 1m',			'300'),//2016名称・価格変更
		'194' => array('EP-425-F',		'ポール用チェーン 1m',			'300'),//2016名称・価格変更
		'195' => array('EP-425-G',		'ポール用チェーン 1m',			'300'),//2016名称・価格変更
		'196' => array('EP-425-H',		'ポール用チェーン 1m',			'300'),//2016名称・価格変更
		'201' => array('FF-559',		'姿見',							'4500'),
		'211' => array('FF-549-A',		'卓上鏡',						'2000'),
		'221' => array('ES-446-A',		'カタログスタンド',				'5500'),//2016型番変更
		'222' => array('ES-447',		'カタログスタンド',				'5500'),
		'231' => array('ES-469-A',		'卓上カタログスタンド',			'1500'),//2016型番変更
		'241' => array('ES-439-A',		'パネルスタンド',				'2500'),
		'242' => array('ES-439-B',		'パネルスタンド',				'2500'),
		'251' => array('FF-553',		'シングルハンガー',				'4500'),
		'261' => array('FF-502-M',		'平台',							'6500'),//2016追加
		'262' => array('FF-502-L',		'平台',							'6500'),//2016追加
		'271' => array('FF-510-M',		'Gケース',						'16000'),
		'272' => array('FF-510-L',		'Gケース',						'19000'),
		'281' => array('FF-508',		'カウンターケース',				'23000'),
		'291' => array('FF-7538-A',		'スーパーエレクター',			'5000'),
		'292' => array('FF-7538-B',		'スーパーエレクター',			'7000'),
		'301' => array('FF-532-SW',		'メッシュパネル',				'2500'),
		'302' => array('FF-532-SBK',	'メッシュパネル(黒)',			'2500'),
		'303' => array('FF-532-LW',		'メッシュパネル',				'5500'),
		'304' => array('FF-532-LBK',	'メッシュパネル(黒)',			'5500'),
		'311' => array('FF-555',		'メッシュパネル用フックL150',	'200'),
		'321' => array('CO-230-S',		'スチール棚',					'6500'),
		'322' => array('CO-230-L',		'スチール棚',					'6500'),
		'331' => array('JQ-856',		'ダストボックス',				'2500'),
		'341' => array('JQ-832',		'貴名受け',						'1500'),
		'351' => array('JQ-892',		'白布1350巾1m',					'800'),
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

		$foreign['appno'] = 12;
		$foreign['token'] = $this->create_token($foreign['exhboothid']);

		// 品番、品名、単価
		$unitcode = array();
		$unitname = array();
		$unitprice = array();
		foreach ($this->unitdb as $key=>$val) {
			$unitcode[$key] = $val[0];
			$unitname[$key] = $val[1];
			$unitprice[$key] = $val[2];
		}

		// 登録用トークンを先に作る
		$token=$this->create_token();

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'quantity')){
				if($foreign_data!=NULL){
					$quantity_array=explode("_",$foreign_key);
					$foreign2[$quantity_array[1]]['unitcode']=$unitcode[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitname']=$unitcode[$quantity_array[1]];
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
			$this->db->set('token', $token);
			$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('v_exapply_12');
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
				$this->db->set('appno', 12);
				$this->db->set('seqno', $seqno);
				$this->db->set('token', $token);
				$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
				$this->db->insert('v_exapply_12_detail');
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

		// 品番、品名、単価
		$unitcode = array();
		$unitname = array();
		$unitprice = array();
		foreach ($this->unitdb as $key=>$val) {
			$unitcode[$key] = $val[0];
			$unitname[$key] = $val[1];
			$unitprice[$key] = $val[2];
		}

		// データ整理
		foreach($foreign as $foreign_key=>$foreign_data){
			if(strstr($foreign_key,'quantity')){
				if($foreign_data!=NULL){
					$quantity_array=explode("_",$foreign_key);
					$foreign2[$quantity_array[1]]['unitcode']=$unitcode[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitname']=$unitcode[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['unitprice']=$unitprice[$quantity_array[1]];
					$foreign2[$quantity_array[1]]['quantity']=$foreign_data;
					$foreign2[$quantity_array[1]]['price']=$foreign_data * $unitprice[$quantity_array[1]];
				}
			}
		}

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			$this->db->set(array_intersect_key($foreign, $this->data_main));
			$this->db->where('exhboothid', $keyid);
//			$this->db->where('token', $token);
			$this->db->where('expired', '0');
			if (!$this->db->update('v_exapply_12')) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		$seqno=1;
		if ($result === TRUE) {
			foreach($foreign2 as $foreign2_key=>$foreign2_data){
				$seqdata=array();

				$this->db->select('seqno');
				$this->db->where('exhboothid', $keyid);
				$this->db->where('unitcode', $foreign2_key);
				$this->db->where('expired', '0');
				$this->db->where('seqno', $seqno);

				$query = $this->db->get('v_exapply_12_detail');
				if ($query !== FALSE && $query->num_rows() > 0) {
					$seqdata = $query->row_array();
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->where('exhboothid', $keyid);
					$this->db->where('unitcode', $foreign2_key);
					$this->db->where('expired', '0');
					if (!$this->db->update('v_exapply_12_detail')) {
						log_message('notice', $this->db->last_query());
						$result = FALSE;
					}
				}else{
					$this->db->set($this->filter_column($foreign2_data, $this->data_detail));
					$this->db->set('exhboothid', $keyid);
					$this->db->set('appno', 12);
					$this->db->set('seqno', $seqno);
					$this->db->set('token', $token);
					$this->db->set('created', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->insert('v_exapply_12_detail');
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
		$this->db->update('v_exapply_12');

		if ($this->db->affected_rows() <= 0) {
			$result = FALSE;
		}

		$this->db->where('exhboothid', $key['exhboothid']);
		$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
		$this->db->set('expired', '1');
		$this->db->where('expired', '0');
		$this->db->update('v_exapply_12_detail');

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
	* ユニットコード一覧の取得
	*/
	function get_unitcode($prefix = '')
	{
		$ar = array();
		foreach($this->unitdb as $key=>$rec) {
			$ar[$rec[0]] = $prefix . $key;
		}
		return $ar;
	}
}

// vim:ts=4
/* End of file exhibitors_model.php */
/* Location: ./application/models/exhibitors_model.php */
