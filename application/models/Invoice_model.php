<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Invoice_model extends CI_Model
{
	protected $CI;
	private $table_name = 'invoice';
	private $data = array(
				'invoiceid'  =>'exhid',
				'invoiceno'  =>'',
				'billid'	 =>'',
				'subtotal'	 =>'',
				'discount'	 =>'',
				'amount'	 =>'',
				'intax'		 =>'',
				'comment'	 =>'',
	);
	private $detail = array(
				'invoiceid'  =>'',
				'seqno'		 =>'',
				'itemtype'	 =>'',
				'itemcode'	 =>'',
				'itemname'	 =>'',
				'itemdetail' =>'',
				'price'		 =>'',
				'discount'	 =>'',
				'amount'	 =>'',
				'tax_exclude_flag'	 =>'',
				'token'		 =>'',
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
		$this->db->from('v_billing_exhibitorfee_test ef');
		$this->db->join('exhibitors e', 'e.exhid = ef.cusexhid');
		$this->db->join('customers c', 'c.exhid = ef.cusexhid AND c.expired = 0');
		$this->db->where('ef.mergebillid',$uid);
		$query = $this->db->get();
		if ($query !== FALSE) {
			if ($query->num_rows() > 0) {
				$data['lists'] = $query->result_array();
			}
		}

		// トランザクションを貼る前に払出の識別子は先にとっておく
		$this->db->select("nextuid('invoice.invoiceid','V') AS invoiceid", FALSE);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			$foreign['invoiceid'] = $row['invoiceid'];
		} else {
			$result = FALSE;
		}
		$query->free_result();

		// トークンの作成
		$foreign['token'] = $this->create_token($foreign['invoiceid']);

		// 請求書番号は、通常発行日時となる
		$foreign['invoiceno'] = date('Ymd') . ($result !== FALSE ? substr($foreign['invoiceid'], -6): date('His'));
		$foreign['comment'] = '※下記口座に2019年11月15日(金)までにお振込みくださいますよう、お願い致します。<br>※出展料が指定期日までに振り込まれない場合は、ご出展をお断りさせて頂きます。';

		$this->load->model('promotion_model');
		$dropdown = $this->promotion_model->get_dropdown();

		// トランザクションの開始
		$this->db->trans_start();

		// 詳細の作成
		if ($result === TRUE) {
			foreach($data['lists'] as $index=>$rec) {
				$key = $rec['exhboothid'];
				$boothabbr =  ($rec['boothabbr'] == '-') ? '':' '.$rec['boothabbr'];
				if (isset($rec['promotion']) && $rec['promotion'] != '') {
					 $itemdetail = $dropdown[$rec['promotion']] . '出展料';
				} else {
					 $itemdetail = '出展料('.$rec['spacename'].$boothabbr.')';
				}
				$detail[] = array(
					'invoiceid'  => $foreign['invoiceid'],
					'seqno'		 => $index+1,
					'itemtype'	 => 0,
					'itemcode'	 => $rec['exhid'],
					'itemname'	 => $rec['corpname'],
					'itemdetail' => $itemdetail,
					'unitprice'  => intval($foreign['unitprice'.$key]),
					'quantity'	 => intval($foreign['quantity'.$key]),
					'price'		 => intval($foreign['unitprice'.$key] * $foreign['quantity'.$key]),
					'discount'	 => intval($foreign['discount'.$key]),
					'amount'	 => intval($foreign['unitprice'.$key] * $foreign['quantity'.$key]) - intval($foreign['discount'.$key]),
                    'tax_exclude_flag'	 => 1,
					'token'		 => $foreign['token'],
				);
			}
			$this->db->insert_batch('invoice_detail', $detail);
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

	function createitem(&$foreign, $itemtype=FALSE, $force_include_tax = true)
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

		// ちょっとログ取り(2013-01-18)
		log_message('notice', 'CREATEITEM::' . var_export($foreign,TRUE));

		// トランザクションを貼る前に払出の識別子は先にとっておく
		$this->db->select("nextuid('invoice.invoiceid','V') AS invoiceid", FALSE);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			$foreign['invoiceid'] = $row['invoiceid'];
		} else {
			$result = FALSE;
		}
		$query->free_result();

		// トークンの作成
		$foreign['token'] = $this->create_token($foreign['invoiceid']);

		// 請求書番号は、通常発行日時となる
		$foreign['invoiceno'] = date('Ymd') . ($result !== FALSE ? substr($foreign['invoiceid'], -6): date('His'));
		$foreign['comment'] = '※下記口座に2019年11月15日(金)までにお振込みくださいますよう、お願い致します。<br>※出展料が指定期日までに振り込まれない場合は、ご出展をお断りさせて頂きます。';

		// トランザクションの開始
		$this->db->trans_start();

		// 詳細の作成
		if ($result === TRUE) {
			if (isset($foreign['items'])) {
				$key = 0;
				foreach($foreign['items'] as $item) {
					if (isset($foreign['itemname'.$item]) && $foreign['itemname'.$item] != '') {
						$record = array(
							'invoiceid'  => $foreign['invoiceid'],
							'seqno'		 => $key,
							'itemtype'	 => '999',
							'itemcode'	 => '9999',
							'itemname'	 => $foreign['itemname'.$item],
							'itemdetail' => $foreign['itemdetail'.$item],
							'unitprice'  => intval($foreign['unitprice'.$item]),
							'quantity'	 => floatval($foreign['quantity'.$item]),
							'price'		 => intval($foreign['unitprice'.$item] * $foreign['quantity'.$item]),
							'discount'	 => intval($foreign['discount'.$item]),
							'amount'	 => intval($foreign['unitprice'.$item] * $foreign['quantity'.$item]) - intval($foreign['discount'.$item]),
							'token'		 => $foreign['token'],
						);
						if ($itemtype !== FALSE || $force_include_tax) {
							if ($itemtype == 32) {
								$record['itemtype'] = $itemtype;
								$record['itemcode'] = $foreign['exhid'.$item]; //出展者コード
								$record['itemname'] = $foreign['itemname'.$item]; //出展者名
								$record['itemdetail'] = '有料残業代('. $foreign['itemdetail'.$item] .')';
							} else if ($itemtype == 31 || $itemtype == 5) {
								$record['itemtype'] = $itemtype;
								$record['itemcode'] = $foreign['exhid'.$item]; //出展者コード
								$record['itemname'] = $foreign['itemname'.$item]; //出展者名
								$record['itemdetail'] = $foreign['itemdetail'.$item];
							}
							$record['tax_exclude_flag'] = 0;
						} else {
                            $record['tax_exclude_flag'] = 1;
                        }
						if (intval($foreign['unitprice'.$item]) > 0) {
							$detail[] = $record;
						}
						$key++;
					}
				}
			} else {
				for($key=0; $key<6; $key++) {
					if (isset($foreign['itemname'.$key]) && $foreign['itemname'.$key] != '') {
						$record = array(
							'invoiceid'  => $foreign['invoiceid'],
							'seqno'		 => $key,
							'itemtype'	 => '999',
							'itemcode'	 => '9999',
							'itemname'	 => $foreign['itemname'.$key],
							'itemdetail' => $foreign['itemdetail'.$key],
							'unitprice'  => intval($foreign['unitprice'.$key]),
							'quantity'	 => floatval($foreign['quantity'.$key]),
							'price'		 => intval($foreign['unitprice'.$key] * $foreign['quantity'.$key]),
							'discount'	 => intval($foreign['discount'.$key]),
							'amount'	 => intval($foreign['unitprice'.$key] * $foreign['quantity'.$key]) - intval($foreign['discount'.$key]),
							'token'		 => $foreign['token'],
						);
						if ($itemtype !== FALSE || $force_include_tax) {
							if ($itemtype == 32) {
								$record['itemtype'] = $itemtype;
								// Upgrade CI3 - Fix "A non-numeric value encountered" - Start by TTM
								if(empty($foreign['exhid'.$key])) $foreign['exhid'.$key] = '';
								// Upgrade CI3 - Fix "A non-numeric value encountered" - End by TTM
								$record['itemcode'] = $foreign['exhid'.$key]; //出展者コード
								$record['itemname'] = $foreign['itemname'.$key]; //出展者名
								$record['itemdetail'] = '有料残業代('. $foreign['itemdetail'.$key] .')';
							} else if ($itemtype == 31 || $itemtype == 5) {
								$record['itemtype'] = $itemtype;
								// Upgrade CI3 - Fix "A non-numeric value encountered" - Start by TTM
								if(empty($foreign['exhid'.$key])) $foreign['exhid'.$key] = '';
								// Upgrade CI3 - Fix "A non-numeric value encountered" - End by TTM
								$record['itemcode'] = $foreign['exhid'.$key]; //出展者コード
								$record['itemname'] = $foreign['itemname'.$key]; //出展者名
								$record['itemdetail'] = $foreign['itemdetail'.$key];
							}
                            $record['tax_exclude_flag'] = 0;
                        } else {
                            $record['tax_exclude_flag'] = 1;
                        }
						if (intval($foreign['unitprice'.$key]) > 0) {
							$detail[] = $record;
						}
					}
				}
			}
			$this->db->insert_batch('invoice_detail', $detail);
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
			$this->db->where('invoiceid', $uid);
		} else {
			$this->db->where('invoiceno', $uno);
		}
		$this->db->from($this->table_name.' iv');
		$this->db->join('exhibitor_bill eb', 'eb.billid = iv.billid');
		$this->db->where('iv.expired', '0');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$data['foreign'] = $query->row_array();
		}
		$uid = $data['foreign']['invoiceid'];

		// 請求明細
		$this->db->select('id.*, pd.suminvoiceamount, pd.sumpaymentamount');
		$this->db->where('id.invoiceid', $uid);
		$this->db->where('id.expired', 0);
		$this->db->from('invoice_detail id');
		$this->db->join('v_invoice_payment_detail pd', 'pd.invoicedid = id.invoicedid', 'left');
		$this->db->join('customers c', 'c.exhid = id.itemcode AND id.itemtype=0 AND c.expired = 0', 'left');
		$this->db->join('exhibitors e', 'e.exhid = c.exhid', 'left');
		$query = $this->db->get();
		if ($query !== FALSE) {
			if ($query->num_rows() > 0) {
				$data['foreign']['detail'] = $query->result_array();
			}
		} else {
			log_message('notice', $this->db->last_query());
		}
		// 入金
/*
		$itemprice = 0;
		$itemdisc = 0;
		foreach($data['foreign']['detail'] as $item) {
			$itemprice += $item['price'];
			$itemdisc += $item['discount'];
		}
		$data['foreign']['sumofprice'] = $itemdisc;
		$data['foreign']['sumofdiscount'] = $itemdisc;
*/
		return $data['foreign'];
	}

	function issue($uid, $uno=FALSE)
	{
		$result = TRUE;

		if ($uno === FALSE) {
			$this->db->where('invoiceid', $uid);
		} else {
			$this->db->where('invoiceno', $uno);
		}
		if ($result === TRUE) {
			$this->db->set('issuedate', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('issued', '1');
			$this->db->where('issued', '0');
			$this->db->where('expired', '0');
			if (!$this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
		return $result;
	}

	function update($foreign)
	{
die();
		$result = TRUE;

		// 今回使用するキーは予めとっておく
		$keyid = $foreign['invoiceid'];
		$token = $foreign['token'];

		// 新規に詰める
		$foreign['token'] = $this->create_token($keyid);
		$foreign['seqno'] = 0;

		// トランザクションの開始
		$this->db->trans_start();

		if ($result === TRUE) {
			// 請求先
			$this->db->set(array_intersect_key($foreign, $this->data_e));
			$this->db->where('invoiceid', $keyid);
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
						'invoiceid'=>$foreign['invoiceid'],
						'seqno'=>$i,
						'boothid'=>$foreign['q_booth'.$i],
						'count'=>1,
						'expired'=>0,
					);
					$this->db->set($record);
					$this->db->set('updated', 'CURRENT_TIMESTAMP', FALSE);
					$this->db->where('invoiceid', $keyid);
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
					$this->db->where('invoiceid', $keyid);
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
			$this->db->where('invoiceid', $key);
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
			$this->db->where('invoiceid', $key);
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('expired', '0');
			if ($this->db->update('invoice_detail')) {
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

// vim:ts=4
// End of file invoice_model.php
// Location: ./application/models/invoice_model.php
