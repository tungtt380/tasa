<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
* 残業請求一覧
*/
class Overtimefee extends MemOP_Controller
{
    protected $form_prefix = 'overtimefee';
	protected $foreign_value = array();

    protected function setup_form(&$data)
    {
        $this->load->helper('form');
    }

    // 本来は違うのだが、ここではindexと同じ
    function index()
    {
        $this->slash_complete();

        $data = $this->setup_data();
        $this->setup_form($data);

        $keyword = $this->input->get('q');
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
        // if ($keyword !== FALSE) {
        if ($keyword !== NULL) {
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
            $data['q'] = $keyword;
            $this->db->collate_like('scantext', $keyword);
        } else {
            $data['q'] = '';
        }

		// 有料残業請求の合成方法
//		$this->db->select("et.mergebillid AS billid, et.price");
		$this->db->select("iv.billid, bb.corpname, bb.corpkana");
		$this->db->select("iv.invoiceid, iv.invoiceno, iv.pricetotal, iv.discounttotal, iv.discount");
//      $this->db->from('v_billing_overtimefee_total et');
//      $this->db->join('v_billing_search bb', 'bb.billid = et.mergebillid AND bb.seqno = 0');
        $this->db->from('v_billing_ex_search bb');
		$this->db->join('v_invoice_overtimefee iv', 'iv.billid = bb.billid AND iv.expired = 0');
        $this->db->where('bb.expired', 0);
        $this->db->where_in('bb.statusno', array('400','401','500'));

        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

    public function searchc()
    {
        $keyword = $this->input->post('q');
        if ($keyword != '') {
            redirect(uri_redirect_string() . '/create?q=' . rawurlencode($keyword));
        }
        redirect(uri_redirect_string() . '/create');
    }

	function create($uid='')
	{
        if ($uid != '') {
            redirect(uri_redirect_string() . '/../regist/' . $uid);
        }

        $data = $this->setup_data();
        $this->setup_form($data);

        $keyword = $this->input->get('q');

        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
        // if ($keyword !== FALSE) {
        if ($keyword !== NULL) {
        // Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
            $data['q'] = $keyword;
            $this->db->collate_like('scantext', $keyword);
        } else {
            $data['q'] = '';
        }

        // 新規請求先の一覧を表示
        $this->db->select("eb.*, vb.corpname as v_corpname, vb.brandname");
        $this->db->from('exhibitor_bill eb');
		$this->db->join('exhibitors e', "eb.exhid = e.exhid AND e.statusno IN (400,401,500) AND e.expired = 0");
        $this->db->join('v_billing_ex_search vb', 'vb.billid = eb.billid', 'left');
        $this->db->where('eb.parentbillid IS NULL', NULL, FALSE);
        $this->db->where('eb.expired', 0);

        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['lists'] = $query->result_array();
            }
        }
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function regist($uid)
	function regist($uid = null)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
    {
        $data = $this->setup_data();
        $this->setup_form($data);
        $data['foreign'] = $this->session->flashdata('foreign');
        $data['message'] = $this->session->flashdata('message');
        $this->session->keep_flashdata('foreign');

        $this->db->select('eb.*, e.corpname, e.brandname');
        $this->db->from('exhibitor_bill eb');
        $this->db->join('exhibitors e', 'eb.exhid = e.exhid AND e.expired = 0', 'left');
        $this->db->where('eb.billid',$uid);
        $this->db->where('eb.expired','0');
        $query = $this->db->get();
        if ($query !== FALSE) {
            if ($query->num_rows() > 0) {
                $data['billing'] = $query->row_array();
            }
        }

        $data['lists'] = array();
        for ($i=0; $i<5; $i++) {
            $data['lists'][$i] = array(
                'no'         => $i,
                'corpname'   => $data['billing']['corpname'],
                'brandname'  => $data['billing']['brandname'],
                'itemname'   => (isset($data['foreign']['itemname'.$i]) ? $data['foreign']['itemname'.$i]:''),
                'itemdetail' => (isset($data['foreign']['itemdetail'.$i]) ? $data['foreign']['itemdetail'.$i]:''),
                'quantity'   => (isset($data['foreign']['quantity'.$i]) ? $data['foreign']['quantity'.$i]:''),
                'unitprice'  => (isset($data['foreign']['unitprice'.$i]) ? $data['foreign']['unitprice'.$i]:''),
                'amount'     => (isset($data['foreign']['amount'.$i]) ? $data['foreign']['amount'.$i]:0),
                'discount'   => (isset($data['foreign']['discount'.$i]) ? $data['foreign']['discount'.$i]:''),
            );
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
            if (
//				(isset($data['foreign']['itemname'.$key])   && $data['foreign']['itemname'.$key] != '') ||
                (isset($data['foreign']['itemdetail'.$key]) && $data['foreign']['itemdetail'.$key] != '') ||
                (isset($data['foreign']['unitprice'.$key])  && $data['foreign']['unitprice'.$key] != '') ||
                (isset($data['foreign']['quantity'.$key])   && $data['foreign']['quantity'.$key] != '') ||
                (isset($data['foreign']['discount'.$key]))  && $data['foreign']['discount'.$key] != '') {
                $this->form_validation->set_rules('itemname'.$key, 'lang:itemname', 'trim|required');
                $this->form_validation->set_rules('itemdetail'.$key, 'lang:itemdetail', 'trim|required');
                $this->form_validation->set_rules('unitprice'.$key, 'lang:unitprice', 'trim|required|numeric|is_natural_no_zero');
                $this->form_validation->set_rules('quantity'.$key, 'lang:quantity', 'trim|required|numeric');
                $this->form_validation->set_rules('discount'.$key, 'lang:discount', 'trim|numeric|is_natural');
            } else {
				unset($data['foreign']['itemname'.$key]);
				unset($data['foreign']['itemdetail'.$key]);
				unset($data['foreign']['unitprice'.$key]);
				unset($data['foreign']['quantity'.$key]);
				unset($data['foreign']['discount'.$key]);
			}
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
                // Upgrade CI3 - Fix "A non-numeric value encountered" - Start by TTM
                // $data['foreign']['subtotal'] += intval($data['foreign']['unitprice'.$key]) * floatval($data['foreign']['quantity'.$key]);
                // $data['foreign']['discount'] += intval($data['foreign']['discount'.$key]);
                $data['foreign']['subtotal'] += intval((empty($data['foreign']['unitprice'.$key])?0:$data['foreign']['unitprice'.$key])) * floatval((empty($data['foreign']['quantity'.$key])?0:$data['foreign']['quantity'.$key]));
                $data['foreign']['discount'] += intval((empty($data['foreign']['discount'.$key])?0:$data['foreign']['discount'.$key]));
                // Upgrade CI3 - Fix "A non-numeric value encountered" - End by TTM
            }
            $data['foreign']['amount'] = intval($data['foreign']['subtotal']) - intval($data['foreign']['discount']);

            $this->config->load('tax', TRUE, TRUE);
            $tax = $this->config->item('tax', 'tax');
            $tax_rate = 100 + $tax;
            $data['foreign']['intax'] = intval($data['foreign']['amount']) - (intval($data['foreign']['amount']*100)/$tax_rate);
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

        $data['lists'] = array();
        for ($i=0; $i<5; $i++) {
            if (isset($data['foreign']['itemname'.$i]) && $data['foreign']['itemname'.$i] != '') {
            $data['lists'][$i] = array(
                'no'        => $i,
                'itemname'  => (isset($data['foreign']['itemname'.$i]) ? $data['foreign']['itemname'.$i]:''),
                'itemdetail'=> (isset($data['foreign']['itemdetail'.$i]) ? $data['foreign']['itemdetail'.$i]:''),
                'quantity'  => (isset($data['foreign']['quantity'.$i]) ? $data['foreign']['quantity'.$i]:''),
                'unitprice' => (isset($data['foreign']['unitprice'.$i]) ? $data['foreign']['unitprice'.$i]:''),
                'amount'    => (isset($data['foreign']['amount'.$i]) ? $data['foreign']['amount'.$i]:0),
                'discount'  => (isset($data['foreign']['discount'.$i]) ? $data['foreign']['discount'.$i]:''),
            );
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

    function registed()
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

        $data['lists'] = array();
        for ($i=0; $i<5; $i++) {
            if (isset($data['foreign']['itemname'.$i]) && $data['foreign']['itemname'.$i] != '') {
            $data['lists'][$i] = array(
                'no'        => $i,
                'itemname'  => (isset($data['foreign']['itemname'.$i]) ? $data['foreign']['itemname'.$i]:''),
                'itemdetail'=> (isset($data['foreign']['itemdetail'.$i]) ? $data['foreign']['itemdetail'.$i]:''),
                'quantity'  => (isset($data['foreign']['quantity'.$i]) ? $data['foreign']['quantity'.$i]:''),
                'unitprice' => (isset($data['foreign']['unitprice'.$i]) ? $data['foreign']['unitprice'.$i]:''),
                'amount'    => (isset($data['foreign']['amount'.$i]) ? $data['foreign']['amount'.$i]:0),
                'discount'  => (isset($data['foreign']['discount'.$i]) ? $data['foreign']['discount'.$i]:''),
            );
            }
        }

        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
    }

    function create_record(&$foreign)
    {
        $this->load->model('invoice_model');
        return $this->invoice_model->createitem($foreign, 32);
    }

    function autocomplete()
    {
        $source = array();

        $this->db->select("eb.*");
        $this->db->from('exhibitor_bill eb');
        $this->db->where('eb.parentbillid IS NULL', NULL, FALSE);
        $this->db->where('eb.expired', 0);

        $query = $this->db->get();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $list = $query->result_array();
            foreach($list as $record) {
                if (isset($record['corpname']) && $record['corpname'] != '') {
                    $source[] = $record['corpname'];
                }
            }
        }
        sort($source);

        $json = json_encode($source);
        header('Content-Type: application/json');
        header('Content-Length: '.strlen($json));
        echo $json;
    }
}

/* End of file overtimefee.php */
/* Location: ./application/controllers/office/overtimefee.php */
