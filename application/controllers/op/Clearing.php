<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
請求一覧
*/
class Clearing extends MemOP_Controller
{
    protected $form_prefix   = 'clearing';       // フォーム名
    protected $table_name    = 'invoice_detail'; // テーブル名
    protected $table_prefix  = FALSE;            // テーブルの払出キー名(システムで一意)
    protected $table_expire  = TRUE;             // テーブルが論理削除の場合 TRUE
    protected $foreign_keyid = 'paymentdid';     // テーブルの主キー名
    protected $foreign_token = 'token';          // ２重更新・削除防止のための項目
	protected $foreign_value = array(
		'paymentno' => 'required',
		'invoiceno' => 'required',
	);

    function __construct() {
        parent::__construct();
        $this->load->model('payment_model');
        $this->load->model('invoice_model');
    }

	function create($uid)
	{
        $data = $this->setup_data();
        $this->setup_form($data);

        if ($this->foreign_order != '') {
            $this->db->order_by($this->foreign_order);
        }

		$this->db->select('pa.*');
		$this->db->from('payment pa');
		$this->db->where('pa.paymentno', $uid);
		$query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data['payment'] = $query->row_array();
        } else {
die('Prohibited.');
		}

        $keyword = $this->input->get('q');
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
        // if ($keyword !== FALSE) {
        if ($keyword !== NULL) {
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
            $data['q'] = $keyword;
            $this->db->collate_like('scantext', $keyword);
        } else {
            $data['q'] = $keyword = $data['payment']['corpname'];
            $this->db->collate_like('scantext', $keyword);
        }

        // 管理者からの一覧は、全て見えるようにする
        $this->db->select('iv.*');
        $this->db->select('bb.b_corpname corpname, bb.b_corpkana corpkana');
        $this->db->from('v_invoice_clearing iv');
        $this->db->join('v_billing_ex_search bb', 'bb.billid = iv.billid');
        $this->db->where('iv.expired', 0);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            foreach($result as $record) {
                $data['lists'][$record['invoiceid']] = $record;
            }
        }

        // 詳細から出展料などの値を掴む(本来はSQLで上記をやるほうが望ましい)
        $this->db->select('id.invoiceid, id.itemtype');
        $this->db->from('invoice_detail id');
        $this->db->join('invoice iv', 'id.invoiceid = iv.invoiceid AND iv.expired = 0');
        $this->db->where('id.expired', 0);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $detail = $query->result_array();
            foreach($detail as $record) {
                if (isset($data['lists'][$record['invoiceid']])) {
                    $data['lists'][$record['invoiceid']]['itemtype'] = $record['itemtype'];
                }
            }
        }

        $this->setup_calc($data);
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

    public function search($uid)
    {
        $keyword = $this->input->post('q');
        if ($keyword != '') {
            redirect(uri_redirect_string() . '../../create/' . $uid . '?q=' . rawurlencode($keyword));
        }
        redirect(uri_redirect_string() . '../../create/'. $uid);
    }

	public function regist($uid='', $uno='')
	{
		if ($uid != '' && $uno != '') {
	        $data['foreign']['paymentno'] = $uid;
	        $data['foreign']['invoiceno'] = $uno;

			// データ
			$data['payment'] = $this->payment_model->read(FALSE, $data['foreign']['paymentno']);
			$data['invoice'] = $this->invoice_model->read(FALSE, $data['foreign']['invoiceno']);
			if (!isset($data['payment']['paymentno']) || !isset($data['invoice']['invoiceno'])) {
				die('Prohibited');
			}
			$charge = intval($data['payment']['deposit']) - intval($data['payment']['charge']) - intval($data['payment']['amount']);
			foreach($data['invoice']['detail'] as $record) {
			    if (intval($record['tax_exclude_flag'])) {
			        $amount = $record['amount'] * 1.1;
                } else {
			        $amount = $record['amount'];
                }
				if ($amount > $record['sumpaymentamount']) {
					if ($charge > $amount-10000) {
						$data['foreign']['iv'.$record['invoicedid']] = $amount;
						$charge -= intval($amount);
					} else {
						$data['foreign']['iv'.$record['invoicedid']] = $charge;
						$charge = 0;
					}
				}
			}
			$data['foreign']['charge'] = -$charge;
	        $this->session->set_flashdata('foreign', $data['foreign']);
	        redirect(uri_redirect_string() . '../../../regist');
		}

        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $data['message'] = $this->session->flashdata('message');
        $this->session->keep_flashdata('foreign');

		// データ
		$data['payment'] = $this->payment_model->read(FALSE, $data['foreign']['paymentno']);
		$data['invoice'] = $this->invoice_model->read(FALSE, $data['foreign']['invoiceno']);

		// 表示
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function regist_in()
	{
        $this->check_action();

        $data = $this->setup_data();
        $data['foreign'] = $this->input->post();
        $data['foreign']['invoicedids'] = array();

        // まず、quantityから数値を抜き出す
        foreach($data['foreign'] as $key=>$val) {
            if (strncmp('iv', $key, 2) == 0) {
                $data['foreign']['invoicedids'][] = substr($key, 2);
            }
        }

        // 入力チェック用に使用するカラムとパターン
        foreach($this->foreign_value as $key=>$val) {
			$this->form_validation->set_rules($key, 'lang'.$key, $val);
        }
        foreach($data['foreign']['invoicedids'] as $key) {
            $this->form_validation->set_rules('iv'.$key, 'lang:amount', 'trim|numeric|is_natural_no_zero');
        }
        $this->form_validation->set_rules('charge', 'lang:charge', 'trim|required|numeric|is_natural');

        // 入力値をチェック
        if ($this->form_validation->run() == FALSE) {
            $msgall = validation_errors();
            $msgarr = explode("\n", $msgall);
            if (count($msgarr) > 5) {
                $msgarr = array_slice($msgarr, 0, 4);
                $msgarr[] = "<p>この他にも入力不備があります。<p>";
            }
            $data['message']['__all'] = implode("\n", $msgarr);
            foreach($this->foreign_value as $key=>$val) {
                $data['message'][$key] = strip_tags(form_error($key));
            }
        }
 
		// 上記チェック中にフィルタもかけるため、チェック後に格納する
        foreach($this->foreign_value as $key=>$val) {
            $data['foreign'][$key] = $this->form_validation->set_value($key);
        }

        // 入力成功後のロジックチェックしたい場合
        if (!isset($data['message']) || empty($data['message'])) {
            $this->check_logic($data);
        }

        $this->session->set_flashdata('foreign', $data['foreign']);

        // 入力不備ならリダイレクト
        if (isset($data['message']) && !empty($data['message'])) {
            $this->session->set_flashdata('message', $data['message']);
            log_message('notice', var_export($data,TRUE));
            redirect(uri_redirect_string() . '/regist');
        }

        // 確認画面にリダイレクト
        redirect(uri_redirect_string() . '/regist_confirm');
	}

	protected function check_logic(&$data)
	{
		$data['payment'] = $this->payment_model->read(FALSE, $data['foreign']['paymentno']);
		$data['invoice'] = $this->invoice_model->read(FALSE, $data['foreign']['invoiceno']);

		$deposit = intval($data['payment']['deposit']) - intval($data['payment']['charge']) - intval($data['payment']['amount']);
		$subtotal = 0;
		$checked = FALSE;
        foreach($data['foreign']['invoicedids'] as $key) {
			if (isset($data['foreign']['iv'.$key]) && intval($data['foreign']['iv'.$key]) > 0) {
				$checked = TRUE;
				$subtotal += $data['foreign']['iv'.$key];
			}
		}
        if ($checked == FALSE) {
            $data['message']['__all'] = '<p>必須項目がありません。</p>';
            $result = FALSE;
        }
        if ($data['foreign']['multi'] != '1') {
			if ($deposit != $subtotal - intval($data['foreign']['charge'])) {
	            $data['message']['__all'] = '<p>消込可能額(&yen;'.number_format($deposit).')と入金額-手数料(&yen;'.number_format($subtotal).'-&yen;'.number_format($data['foreign']['charge']).')が一致しません</p>';
	            $result = FALSE;
			}
        } else {
			if ($deposit < $subtotal - intval($data['foreign']['charge'])) {
	            $data['message']['__all'] = '<p>消込可能額(&yen;'.number_format($deposit).')より入金額-手数料(&yen;'.number_format($subtotal).'-&yen;'.number_format($data['foreign']['charge']).')が大きいので処理できません</p>';
	            $result = FALSE;
			}
		}
        if ($result === FALSE) {
            log_message('notice', $data['message']['__all']);
        } else {
			$data['foreign']['subtotal'] = $subtotal;
		}
        return $result;
	}

	public function regist_confirm()
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $data['message'] = $this->session->flashdata('message');
        $this->session->keep_flashdata('foreign');

		// データ
		$data['payment'] = $this->payment_model->read(FALSE, $data['foreign']['paymentno']);
		$data['invoice'] = $this->invoice_model->read(FALSE, $data['foreign']['invoiceno']);
        if (!isset($data['payment']['paymentno']) || !isset($data['invoice']['invoiceno'])) {
            die('Prohibited');
        }

		// 表示
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function regist_confirm_in()
	{
        $this->check_action();

        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $this->session->keep_flashdata('foreign');

        log_message('notice', var_export($data['foreign'],TRUE));

        // データベースに登録
        $result = $this->create_record($data['foreign']);

        $line = $this->lang->line($result !== FALSE ? 'M2001':'N4001');
        $message = explode("\n", $line);
        $this->session->set_flashdata('message', $message);

        if ($result !== FALSE) {
            $this->load->model('histories_model');
            $state = $data['foreign']['paymentid'];
            // Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
			if(!isset($action)) $action = NULL;
			// Upgrade PHP7 - Fix bug "Undefined variable" - End by TTM
            $this->histories_model->log(0, $action, '入金消込('.$state.')');
        }

        // 登録完了画面へ
        redirect(uri_redirect_string() . '/registed');
    }

    function create_record(&$foreign)
    {
        $this->load->model('clearing_model');
        return $this->clearing_model->create($foreign);
    }
}

// vim:ts=4
