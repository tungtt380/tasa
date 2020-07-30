<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Clearing_model extends CI_Model
{
	protected $CI;
	private $table_name = 'payment_detail';
	private $data = array(
				'paymentdid'    =>'',
				'paymentid'     =>'',
				'seqno'         =>'',
				'invoiceid'     =>'',
				'invoicedid'    =>'',
				'invoiceamount' =>'',
				'paymentamount' =>'',
				'price'         =>'',
				'comment'       =>'',
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

		// その他データを事前に取得しておく
        $data['payment'] = $this->payment_model->read(FALSE, $foreign['paymentno']);
        $data['invoice'] = $this->invoice_model->read(FALSE, $foreign['invoiceno']);

		// トークンの作成
		$foreign['token'] = $this->create_token($data['payment']['paymentid']);

		// トランザクションの開始
//		die(var_export($foreign).'<br/><br/>'.var_export($data));
		$this->db->trans_start();

		// 詳細の作成
		if ($result === TRUE) {
			foreach($data['invoice']['detail'] as $index=>$rec) {
				$detail[] = array(
					'paymentid'     => $data['payment']['paymentid'],
					'seqno'         => $index+1,
					'invoicedid'    => $rec['invoicedid'],
					'invoiceamount' => $rec['amount'],
					'paymentamount' => $foreign['iv'.$rec['invoicedid']],
					'price'         => intval($rec['amount']) - $foreign['iv'.$rec['invoicedid']],
					'comment'       => $foreign['comment'],
					'token'         => $foreign['token'],
				);
			}
			$this->db->insert_batch('payment_detail', $detail);
            if ($this->db->affected_rows() <= 0) {
                log_message('notice', $this->db->last_query());
                $result = FALSE;
            } else {
                log_message('info', $this->db->last_query());
			}
		}
		// 総額のアップデート
		if ($result === TRUE) {
            // 請求先
			$this->db->set('billid', $data['invoice']['billid']);
            $this->db->set('charge', 'charge + ' . $foreign['charge'], FALSE);
            $this->db->set('amount', 'amount + ' . $foreign['subtotal'], FALSE);
            $this->db->set('comment', $foreign['comment']);
            $this->db->where('paymentid', $data['payment']['paymentid']);
            if (!$this->db->update('payment')) {
                log_message('notice', $this->db->last_query());
                $result = FALSE;
            } else {
                log_message('info', $this->db->last_query());
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

	function delete($key, $token='')
	{
		// トランザクションの開始
		$this->db->trans_start();

		// 入金詳細の削除
		if ($result === TRUE) {
			$this->db->where('paymentdid', $key);
			$this->db->set('token', $this->create_token());
			$this->db->set('deleted', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->set('expired', '1');
			$this->db->where('token', $token);
			$this->db->where('expired', '0');
			if ($this->db->update($this->table_name)) {
				log_message('notice', $this->db->last_query());
				$result = FALSE;
			}
		}
	}

    protected function create_token($seed = 'ZYX')
    {
        return base64_encode(sha1(uniqid(rand() . $seed), TRUE) . 'A');
    }
}

/* End of file payment_model.php */
/* Location: ./application/models/payment_model.php */
