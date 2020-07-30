<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
請求一覧
*/
class Sales extends ExhOP_Controller
{
    protected $form_prefix   = 'sales';       // フォーム名
    protected $table_name    = 'invoice';     // テーブル名
    protected $table_prefix  = 'V';           // テーブルの払出キー名(システムで一意)
    protected $table_expire  = TRUE;          // テーブルが論理削除の場合 TRUE
    protected $foreign_keyid = 'invoiceid';   // テーブルの主キー名
	protected $foreign_token = FALSE;         // ２重更新・削除防止のための項目

    function __construct() {
        parent::__construct();
        $this->load->model('invoice_model');
    }

    protected function setup_form(&$data)
    {
        $this->load->helper('form');
        $this->load->model('space_model');
        $data['space'] = $this->space_model->get_dropdown();
    }

	function index()
	{
        $this->slash_complete();
        $data = $this->setup_data();
        $this->setup_form($data);

        if ($this->foreign_order != '') {
            $this->db->order_by($this->foreign_order);
        }

		$this->db->select('id.*, pd.*');
        $this->db->from('invoice_detail id');
        $this->db->join('v_invoice_payment_detail pd', 'id.invoicedid = pd.invoicedid');

		$data['lists'] = array();

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
			$result = $query->result_array();
			$itemnamelist = array(
				'出展料(Aスペース)'=>'出展料(Aスペース',
				'出展料(Bスペース)'=>'出展料(Bスペース',
				'出展料(Cスペース)'=>'出展料(Cスペース',
				'出展料(Dスペース)'=>'出展料(Dスペース',
				'出展料(Eスペース)'=>'出展料(Eスペース',
				'出展料(Fスペース)'=>'出展料(Fスペース',
				'出展料(Sスペース)'=>'出展料(S(',
				'出展料(NAPAC-C)'=>'出展料(NAPAC-C',
				'出展料(NAPAC-D)'=>'出展料(NAPAC-D',
				'出展料(NAPAC-E)'=>'出展料(NAPAC-E',
				'出展料(JASMA-E)'=>'出展料(JASMA-E',
			);
			foreach($itemnamelist as $key=>$val) {
				$data['lists'][$key] = array('itemdetail'=>$key);
			}
			foreach($result as $record) {
				$itemname = $record['itemdetail'];
				$itemname = str_replace("（", "(", $itemname);
				foreach($itemnamelist as $key=>$val) {
					if (strstr($itemname,$val) !== FALSE) {
						$itemname = $key;
						break;
					}
				}
                if (intval($record['tax_exclude_flag'])) {
                    $price = $record['price'] * 1.1;
                    $discount = $record['discount'] * 1.1;
                    $suminvoiceamount = $record['suminvoiceamount'] * 1.1;
//                    $sumprice = $record['sumprice'] * 1.1;
                } else {
                    $price = $record['price'];
                    $discount = $record['discount'];
                    $suminvoiceamount = $record['suminvoiceamount'];
//                    $sumprice = $record['sumprice'];
                }
                $sumpaymentamount = $record['sumpaymentamount'];
                $sumprice = $suminvoiceamount - $sumpaymentamount;

				if (isset($data['lists'][$itemname]) && isset($data['lists'][$itemname]['price'])) {
	            	$data['lists'][$itemname]['price'] += $price;
	            	$data['lists'][$itemname]['discount'] += $discount;
	            	$data['lists'][$itemname]['suminvoiceamount'] += $suminvoiceamount;
	            	$data['lists'][$itemname]['sumpaymentamount'] += $record['sumpaymentamount'];
	            	$data['lists'][$itemname]['sumprice'] += $sumprice;
				} else {
	            	$data['lists'][$itemname] = $record;
                    $data['lists'][$itemname]['price'] = $price;
                    $data['lists'][$itemname]['discount'] = $discount;
                    $data['lists'][$itemname]['suminvoiceamount'] = $suminvoiceamount;
                    $data['lists'][$itemname]['sumprice'] = $sumprice;
	            	$data['lists'][$itemname]['itemdetail'] = $itemname;
				}
			}
        }

		


	    $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	protected function download_build()
	{
	}

	function regist() {
	}
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function create() {
	function create($uid = '') {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	}
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// function delete() {
	function delete($uid = '') {
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	}
}

/* End of file sales.php */
/* Location: ./application/controllers/office/sales.php */
