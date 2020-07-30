<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_model extends CI_Model
{
	protected $CI;
	private $table_name = 'payment';
	private $data = array(
				'paymentid'  =>'',
				'paymentno'  =>'',
				'billid'     =>'',
				'subtotal'   =>'',
				'discount'   =>'',
				'amount'     =>'',
				'intax'      =>'',
				'comment'    =>'',
	);
	private $detail = array(
				'paymentid'  =>'',
				'seqno'      =>'',
				'itemtype'   =>'',
				'itemcode'   =>'',
				'itemname'   =>'',
				'itemdetail' =>'',
				'price'      =>'',
				'discount'   =>'',
				'amount'     =>'',
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
		$uid = $foreign['billid'];

		// その他データを事前に取得しておく
        $this->db->select('eb.*');
        $this->db->from('exhibitor_bill eb');
        $this->db->where('eb.billid',$uid);
        $this->db->where('eb.expired','0');
        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['billing'] = $query->row_array();
            }
        }
        $this->db->select("ef.*");
        $this->db->select('e.promotion, e.comment');
        $this->db->select('c.customerid, c.corpname as c_corpname, c.tas, c.napac, c.tascount');
        $this->db->from('v_billing_exhibitorfee ef');
        $this->db->join('exhibitors e', 'e.exhid = ef.cusexhid');
        $this->db->join('customers c', 'c.exhid = ef.cusexhid');
        $this->db->where('ef.mergebillid',$uid);
        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }

		// トランザクションを貼る前に払出の識別子は先にとっておく
		$this->db->select("nextuid('payment.paymentid','V') AS paymentid", FALSE);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			$foreign['paymentid'] = $row['paymentid'];
		} else {
			$result = FALSE;
		}
		$query->free_result();

		// トークンの作成
		$foreign['token'] = $this->create_token($foreign['paymentid']);

		// 請求書番号は、通常発行日時となる
		$foreign['paymentno'] = date('YmdHis');
		$foreign['comment'] = '※下記口座に2017年11月24日(金)までにお振込みくださいますよう、お願い致します。';
//die(var_export($foreign).var_export($data));

		// トランザクションの開始
		$this->db->trans_start();

		// 詳細の作成
		if ($result === TRUE) {
			foreach($data['lists'] as $index=>$rec) {
				$key = $rec['exhboothid'];
				$boothabbr =  ($rec['boothabbr'] == '-') ? '':' '.$rec['boothabbr'];
				$detail[] = array(
					'paymentid'  => $foreign['paymentid'],
					'seqno'      => $index+1,
					'itemtype'   => 0,
					'itemcode'   => $rec['exhid'],
					'itemname'   => $rec['corpname'],
					'itemdetail' => '出展料('.$rec['spacename'].$boothabbr.')',
					'unitprice'  => intval($foreign['unitprice'.$key]),
					'quantity'   => intval($foreign['quantity'.$key]),
					'price'      => intval($foreign['unitprice'.$key] * $foreign['quantity'.$key]),
					'discount'   => intval($foreign['discount'.$key]),
					'amount'     => intval($foreign['unitprice'.$key] * $foreign['quantity'.$key]) - intval($foreign['discount'.$key]),
					'token'      => $foreign['token'],
				);
			}
			$this->db->insert_batch('payment_detail', $detail);
            if ($this->db->affected_rows() <= 0) {
                $result = FALSE;
            }
		}

		// 請求書の作成
		if ($result === TRUE) {
			$this->db->set(array_intersect_key($foreign, $this->data));
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

	function read($uid,$uno=FALSE)
	{
		$data = array();
		$data['foreign'] = array();

		// 請求書
		if ($uno === FALSE) {
			$this->db->where('paymentid', $uid);
		} else {
			$this->db->where('paymentno', $uno);
		}
		$this->db->select('pa.*, eb.*, pa.corpname AS i_corpname');
		$this->db->from($this->table_name.' pa');
		$this->db->join('exhibitor_bill eb', 'eb.billid = pa.billid', 'left');
		$this->db->where('pa.expired', '0');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['foreign'] = $query->row_array();
		}
		$uid = $data['foreign']['paymentid'];

		// 請求明細
		$this->db->select('pd.*, id.invoiceid, id.itemname, id.itemdetail, id.itemtype, id.unitprice, id.quantity, id.price, iv.invoiceno');
		$this->db->where('pd.paymentid', $uid);
		$this->db->where('pd.expired', 0);
		$this->db->from('payment_detail pd');
		$this->db->join('invoice_detail id', 'id.invoicedid = pd.invoicedid AND id.expired = 0');
		$this->db->join('invoice iv', 'id.invoiceid = iv.invoiceid AND iv.expired = 0');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['foreign']['detail'] = $query->result_array();
		}
		return $data['foreign'];
	}

	function update($foreign)
	{
die();
		$result = TRUE;

		// 今回使用するキーは予めとっておく
        $keyid = $foreign['paymentid'];
        $token = $foreign['token'];

		// 新規に詰める
        $foreign['token'] = $this->create_token($keyid);
        $foreign['seqno'] = 0;

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			// 請求先
			$this->db->set(array_intersect_key($foreign, $this->data_e));
			$this->db->where('paymentid', $keyid);
			$this->db->where('token', $token);
			$this->db->where('expired', '0');
			if (!$this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}

		if ($result === TRUE) {
			// 請求明細
			for ($i=0; $i<=5; $i++) {
				if (isset($foreign['q_boothcount'.$i]) && $foreign['q_boothcount'.$i] > 0) {
					// こちらは、挿入か更新をかならず行う
					$record = array(
						'paymentid'=>$foreign['paymentid'],
						'seqno'=>$i,
						'boothid'=>$foreign['q_booth'.$i],
						'count'=>1,
						'expired'=>0,
					);
					$this->db->set($record);
					$this->db->set('updated', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->where('paymentid', $keyid);
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
					$this->db->where('paymentid', $keyid);
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

		// 請求先の削除
		if ($result === TRUE) {
			$this->db->where('paymentid', $key);
//			$this->db->set('token', $this->create_token());
//			$this->db->where('token', $token);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		// 請求先の他情報の削除
		if ($result === TRUE) {
			$this->db->where('paymentid', $key);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update('payment_detail')) {
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

/* End of file payment_model.php */
/* Location: ./application/models/payment_model.php */
