<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
請求一覧
*/
class Exhibitorfee extends MemOP_Controller
{
    protected $form_prefix = 'exhibitorfee';
	protected $foreign_value = array();

    // 本来は違うのだが、ここではindexと同じ
    function index()
    {
        $this->slash_complete();

        $data = $this->setup_data();
        $this->setup_form($data);

        $keyword = $this->input->get('q');
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
        // if ($keyword !== NULL) {
        if ($keyword !== NULL) {
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
            $data['q'] = $keyword;
            $this->db->collate_like('scantext', $keyword);
        } else {
            $data['q'] = '';
        }

		// 合成方法
		$this->db->select("et.mergebillid AS billid, et.price");
		$this->db->select("bb.corpname, bb.corpkana");
		$this->db->select("iv.invoiceid, iv.invoiceno, iv.pricetotal, iv.discounttotal, iv.discount");
        $this->db->from('v_billing_exhibitorfee_total et');
        $this->db->join('v_billing_search bb', 'bb.billid = et.mergebillid AND bb.seqno = 0');
		$this->db->join('v_invoice_exhibitorfee iv', 'iv.billid = bb.billid AND iv.expired = 0', 'left');
        $this->db->where('bb.expired', 0);

        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

	function regist($uid)
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $data['message'] = $this->session->flashdata('message');
        $this->session->keep_flashdata('foreign');

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
		$this->db->select("ef.*, ef.price unitprice");
		$this->db->select('e.promotion, e.comment');
		$this->db->select('c.customerid, c.corpname, c.tas, c.napac, c.tascount');
        $this->db->from('v_billing_exhibitorfee ef');
		$this->db->join('exhibitors e', 'e.exhid = ef.exhid');
		$this->db->join('customers c', 'c.exhid = ef.exhid');
		$this->db->where('ef.mergebillid',$uid);
        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function regist_in()
	{
        $this->check_action();

        $data = $this->setup_data();
        $data['foreign'] = $this->input->post();
		$data['foreign']['booths'] = array();

		// まず、quantityから数値を抜き出す
		foreach($data['foreign'] as $key=>$val) {
			if (strncmp('quantity', $key, 8) == 0) {
				$data['foreign']['booths'][] = substr($key, 8);
			}
		}

        // 入力チェック用に使用するカラムとパターン
		foreach($data['foreign']['booths'] as $key) {
            $this->form_validation->set_rules('unitprice'.$key, 'lang:unitprice', 'trim|required|numeric|is_natural_no_zero');
            $this->form_validation->set_rules('quantity'.$key, 'lang:quantity', 'trim|required|numeric|is_natural');
            $this->form_validation->set_rules('discount'.$key, 'lang:discount', 'trim|numeric|is_natural');
		}

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

        // 入力値の合計
        if (!isset($data['message']) || empty($data['message'])) {
			$data['foreign']['subtotal'] = 0;
			$data['foreign']['discount'] = 0;
			foreach($data['foreign']['booths'] as $key) {
				$data['foreign']['subtotal'] += intval($data['foreign']['unitprice'.$key]) * intval($data['foreign']['quantity'.$key]);
				$data['foreign']['discount'] += intval($data['foreign']['discount'.$key]);
			}
			$data['foreign']['amount'] = intval($data['foreign']['subtotal']) - intval($data['foreign']['discount']);
			$data['foreign']['intax'] = intval($data['foreign']['amount']) - (intval($data['foreign']['amount']*100)/105);
		}

        // 上記チェック中にフィルタもかけるため、チェック後に格納する
        foreach($this->foreign_value as $key=>$val) {
            $data['foreign'][$key] = $this->form_validation->set_value($key);
        }
        $this->session->set_flashdata('foreign', $data['foreign']);

        // 入力成功後のロジックチェックしたい場合
        if (!isset($data['message']) || empty($data['message'])) {
            $this->check_logic($data);
        }

        // 入力不備ならリダイレクト
        if (isset($data['message']) && !empty($data['message'])) {
            $this->session->set_flashdata('message', $data['message']);
            log_message('notice', var_export($data,TRUE));
            redirect(uri_redirect_string() . '/regist/' . $data['foreign']['billid']);
        }

        // 確認画面にリダイレクト
        redirect(uri_redirect_string() . '/regist_confirm');
	}

	function regist_confirm()
	{
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $this->session->keep_flashdata('foreign');

		$uid = $data['foreign']['billid'];

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
		$this->db->select('c.customerid, c.corpname, c.tas, c.napac, c.tascount');
        $this->db->from('v_billing_exhibitorfee ef');
		$this->db->join('exhibitors e', 'e.exhid = ef.exhid');
		$this->db->join('customers c', 'c.exhid = ef.exhid');
		$this->db->where('ef.mergebillid',$uid);
        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }

        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function regist_confirm_in()
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
            $state = $data['foreign']['billid'] . ':' . $data['foreign']['invoiceid'];
            // Upgrade PHP7 - Fix bug "Undefined variable" - Start by TTM
			if(!isset($action)) $action = NULL;
			// Upgrade PHP7 - Fix bug "Undefined variable" - End by TTM
	        $this->histories_model->log(0, $action, '請求書発行('.$state.')');
        }

        // 登録完了画面へ
        redirect(uri_redirect_string() . '/registed');
	}

	function create_record(&$foreign)
	{
		$this->load->model('invoice_model');
		return $this->invoice_model->create($foreign);
	}
}
// vim:ts=4
/* End of file invoice.php */
/* Location: ./application/controllers/office/invoice.php */
