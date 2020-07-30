<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
請求一覧
*/
class Invoice extends ExhOP_Controller
{
	protected $form_prefix	 = 'invoice';		// フォーム名
	protected $table_name	 = 'invoice';		// テーブル名
	protected $table_prefix  = 'V';				// テーブルの払出キー名(システムで一意)
	protected $table_expire  = TRUE;			// テーブルが論理削除の場合 TRUE
	protected $foreign_keyid = 'invoiceid';		// テーブルの主キー名
	protected $foreign_token = FALSE;		  // ２重更新・削除防止のための項目
//	protected $foreign_token = 'token';			// ２重更新・削除防止のための項目

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

	public function search()
	{
		$keyword = $this->input->post('q');
		$spaces = $this->input->post('s');
		$types = $this->input->post('t');
		$promo = $this->input->post('p');
		$querystring = '';
		if ($keyword != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'q=' . rawurlencode($keyword);
		}
		if ($spaces != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 's=' . rawurlencode($spaces);
		}
		if ($promo != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 'p=' . rawurlencode($promo);
		}
		if ($types != '') {
			$querystring .= ($querystring != '' ? '&':'?') . 't=' . rawurlencode($types);
		}
		if ($querystring != '') {
			redirect('/' . dirname(uri_string()) . '/' . $querystring);
		}
		redirect('/' . dirname(uri_string()) . '/./');
	}

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
		$this->setup_form($data);

		$keyword = $this->input->get('q');
		$spaces = $this->input->get('s');
		$promo = $this->input->get('p');
		$types = $this->input->get('t');
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($keyword !== FALSE) {
		if ($keyword !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['q'] = $keyword;
			$marches = '';
			if (preg_match('/^([^:]+):([^:]+)$/', $keyword, $matches)) {
				switch($matches[1]) {
				case 'promotion':
					$this->db->like('promotion', $matches[2]);
					break;
				default:
					$this->db->collate_like('scantext', $keyword);
					break;
				}
			} else {
				$this->db->collate_like('scantext', $keyword);
			}
		} else {
			$data['q'] = '';
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($spaces !== FALSE) {
		if ($spaces !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['s'] = $spaces;
			$this->db->like('scanboothid', '@'.$spaces.'@');
		} else {
			$data['s'] = '';
		}
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - Start by TTM
		// if ($promo !== FALSE) {
		if ($promo !== NULL) {
		// Upgrade CI3 - Step 10: Many functions now return NULL instead of FALSE on missing items - End by TTM
			$data['p'] = $promo;
			$this->db->like('promotion', $promo);
		} else {
			$data['p'] = '';
		}

		if ($this->foreign_order != '') {
			$this->db->order_by($this->foreign_order);
		}

		// 管理者からの一覧は、全て見えるようにする
		$this->db->select('iv.*, ip.suminvoiceamount, ip.sumpaymentamount, ip.sumprice, ip.paymentdate');
		$this->db->select('bb.b_corpname AS corpname, bb.corpkana AS corpkana, bb.scanbooth AS scanbooth');
		$this->db->from($this->table_name . ' iv');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = iv.billid');
		$this->db->join('v_invoice_payment ip', 'ip.invoiceid = iv.invoiceid', 'left');
		$this->db->where('iv.expired', 0);

		$data['lists'] = array();

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
		if (uri_folder_string() == '/iv') {
			$this->parser->parse('iv/'.$this->form_prefix.'_'.__FUNCTION__, $data);
		} else {
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	protected function download_build()
	{
		$this->db->select('iv.invoiceno 請求書No');
		$this->db->select('bb.b_corpname 出展者, bb.corpkana 出展者カナ, bb.promotion 媒体ブース');
		$this->db->select('iv.issuedate 請求日');
		$this->db->select('iv.amount 請求額, ip.paymentdate 入金日, ip.sumpaymentamount 消込額');
		$this->db->select('IF(ip.sumpaymentamount IS NULL,iv.amount,iv.amount-ip.sumpaymentamount) 残金', FALSE);
		$this->db->select('IF(iv.amount<=ip.sumpaymentamount,1,NULL) 入金済', FALSE);
		$this->db->select('iv.updated 更新日');
		$this->db->from($this->table_name . ' iv');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = iv.billid');
		$this->db->join('v_invoice_payment ip', 'ip.invoiceid = iv.invoiceid', 'left');
		$this->db->where('iv.expired', 0);
	}

	function setup_calc(&$data)
	{
		foreach($data['lists'] as $key=>$rec) {
			if (isset($rec['sumpaymentamount']) && $rec['sumpaymentamount'] != '') {
				if (intval($rec['sumpaymentamount']) >= $rec['subtotal']-$rec['discount']) {
					$data['lists'][$key]['paid'] = 1;
				}
			}
		}
	}

	function get_record(&$data, $uno)
	{
		$data['foreign'] = $this->invoice_model->read(FALSE, $uno);
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

	// Upgrade CI3 - Add function to prevent error if empty $uid - Start by TTM
	//【詳細画面】
	public function detail($uid='')
	{
		if(!empty($uid))
		{
			$data = $this->setup_data();
			$this->setup_form($data);
			$this->get_record($data, $uid);
			if (!isset($data['foreign'][$this->foreign_keyid])) {
				redirect(uri_redirect_string() . '/', 'location', 302);
			}
			$this->setup_form_ex($data);
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
		else 
		{
			redirect(uri_redirect_string() . '/');
		}
	}
	// Upgrade CI3 - Add function to prevent error if empty $uid - End by TTM

	public function quotation($uno='', $preview='')
	{
		$this->check_action();
		$post = $this->input->post();

        $data = $this->setup_data();
        $this->setup_form($data);
        $this->get_record($data, $uno);
        if (!isset($data['foreign'][$this->foreign_keyid])) {
            $this->parser->parse($this->form_prefix.'_nodata');
        } else {
            // 受理書を発行したら発行フラグと発行日を更新
            $foreign = $this->invoice_model->read(FALSE, $uno);
            $foreign['comment'] = '';

            // Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			// $this->load->library('pdf');
			$this->load->library('Pdf_lib');
			// Upgrade PHP7 - Rename class to make it loadable - End by TTM
            $pdf = new FPDI(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(12, 16, 8);
            $pdf->setPrintHeader(FALSE);
            $pdf->setPrintFooter(FALSE);
            $pdf->SetHeaderMargin(2);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 10);

            // Page 1
            $pdf->AddPage();
            $pdf->setSourceFile(APPPATH . 'views/pdf/quotation_1.pdf');
            $tpl = $pdf->importPage(1);
            $pdf->useTemplate($tpl);
            $this->quotation_detail($pdf, $foreign, APPPATH.'views/pdf/quotation_1.pdf');

//          // Page 2
//          $pdf->AddPage();
//          $pdf->setSourceFile(APPPATH . 'views/pdf/quotation_2.pdf');
//          $tpl = $pdf->importPage(1);
//          $pdf->useTemplate($tpl);
//          $this->issue_detail($pdf, $foreign, APPPATH.'views/pdf/quotation_2.pdf');

            // コミットしてダウンロード
            $pdf->lastPage();
            $filename = 'quotation-' . $foreign['invoiceno'] . '-' . $foreign['billid'] . '.pdf';

			header('Content-Type: application/pdf');
			header('Cache-Control: max-age=0');
			if ($preview != '') {
				$pdf->Output();
			} else {
				$pdf->Output($filename, 'D');
			}
		}
	}

	protected function quotation_detail(&$pdf, &$foreign, $templatefile)
	{
//		$defaultfont = 'kozminproregular';
//		$defaultfont = 'ms-mincho';
		$defaultfont = 'ipam';

        $this->config->load('tax', TRUE, TRUE);
        $tax = $this->config->item('tax', 'tax') / 100;

		// 宛先の表示
		$zip='〒'.$foreign['zip'];
		$dist_x=18;
		$dist_y=18;
		$line=4;
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(0, 0, $zip, 0, 'J', 0, 1, $dist_x, $dist_y);
		$pdf->MultiCell(0, 0, $foreign['prefecture'] . $foreign['address1'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*1));
		$pdf->MultiCell(0, 0, $foreign['address2'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*2));
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(0, 0, $foreign['corpname'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*4));
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(80, 0, $foreign['division'] . ($foreign['division']==''?'':' ') . $foreign['position'] , 0, 'L', 0, 1, $dist_x, $dist_y+($line*5.2));
		$pdf->SetFont($defaultfont, '', 14);
		$pdf->MultiCell(80, 0, $foreign['fullname'] . ' 様', 0, 'R', 0, 1, $dist_x, $dist_y+($line*7));

		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(182, 0, date('Y年m月d日'), 0, 'R', 0, 1, $dist_x, $dist_y+($line*4.5));
//		$pdf->MultiCell(182, 0, date('Y年m月d日', strtotime($foreign['issuedate'])), 0, 'R', 0, 1, $dist_x, $dist_y+($line*4.5));
		$pdf->MultiCell(182, 0, '見積書番号:' . $foreign['invoiceno'], 0, 'R', 0, 1, $dist_x, $dist_y+($line*5.5));

		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(190, 0, 'ページ: 1 / 1', 0, 'R', 0, 1, $dist_x, 255);

		if ($foreign['amount'] > 0) {
			$pdf->SetFont($defaultfont, '', 20);
			$pdf->writeHTMLCell( 58, 0, 9, 89.5, "￥".number_format($foreign['amount']), 0, 0, 0, true, 'C');
			$pdf->SetFont($defaultfont, '', 10);

            $amount = $foreign['amount'];
            $amount_ex_tax = ceil($amount / (1 + $tax));
            $intax  = $amount - $amount_ex_tax;
            $pdf->writeHTMLCell( 61, 0, 9, 98, "(内消費税 " . ($tax * 100) . "% ￥".number_format($intax) .")", 0, 0, 0, true, 'C');
		} else {
			$pdf->SetFont($defaultfont, '', 20);
			$pdf->writeHTMLCell( 58, 0, 9, 90, "＊＊＊", 0, 0, 0, true, 'C');
		}
		$pdf->SetFont($defaultfont, '', 9.5);
//		$pdf->MultiCell(138, 0, $foreign['comment'], 0, 'R', 0, 1, $dist_x+48, 103);
		$pdf->writeHTMLCell(138, 0, $dist_x+51, 96, $foreign['comment'], 0, 0, 0, true, 'L');
		$b = 0;
		$h = 4.575;
		$starty = 113;

		$posix1=8;
		$posix2=77.5;
		$posix3=128;
		$posix4=145;
		$posix6=174;

		$range=$h*3;
		$count=count($foreign['detail']);

		$y = $starty;
		$subtotal = 0;
		$discount = 0;
        $tax_include_sum = 0;
        $tax_exclude_sum = 0;
        $price_sum       = 0;
        $discount_sum    = 0;
        $amount_sum      = 0;
        $amount_include_sum = 0;
        $amount_exclude_sum = 0;
        $discount_include_sum = 0;
        $discount_exclude_sum = 0;

        $in_tax_exists = false;
        $out_tax_exists = false;

		for($i=0;$i<$count;$i++){
			$rec = $foreign['detail'][$i];

            $base_price      = $rec['price'];
            $base_discount   = $rec['discount'];
            $base_amount     = $rec['amount'];
            $base_unitprice  = $rec['unitprice'];
            $tax_exclude_flg = $rec['tax_exclude_flag'] * 1;
            $show_tax_include_flag  = (isset($_POST['uchizei_' . intval($rec['invoicedid'])]) && $_POST['uchizei_' . intval($rec['invoicedid'])] == '1');

            $quantity  = intval($rec['quantity']);
            if (!$quantity) {
                $quantity = 1;
            }
            if ($tax_exclude_flg) {
                $price_exclude    = $base_price;
                $discount_exclude = $base_discount;
                $amount_exclude   = $base_amount;
                $unitprice_exclude = $base_unitprice;
                $price_tax = ceil($price_exclude * $tax);
                $discount_tax = ceil($discount_exclude * $tax);
                $amount_tax = ceil($amount_exclude * $tax);
                $unitprice_tax = ceil($unitprice_exclude * $tax);
                $price_include = $price_exclude + $price_tax;
                $discount_include = $discount_exclude + $discount_tax;
                $amount_include = $amount_exclude + $amount_tax;
                $unitprice_include = $unitprice_exclude + $unitprice_tax;
            } else {
                $price_include = $base_price;
                $discount_include = $base_discount;
                $amount_include = $base_amount;
                $unitprice_include = $base_unitprice;
                $discount_exclude = floor($discount_include / ($tax + 1));
                $unitprice_exclude = $unitprice_include / ($tax + 1);
                $price_exclude = $unitprice_exclude * $quantity;
                $amount_exclude = $price_exclude - $discount_exclude;
                $price_tax = $price_include - $price_exclude;
                $discount_tax = $discount_include - $discount_exclude;
                $amount_tax = $amount_include - $amount_exclude;
                $unitprice_tax = $unitprice_include - $unitprice_exclude;
            }
            if ($show_tax_include_flag) {
                $in_tax_exists = true;
                $show_price  = $price_include;
                $show_discount = $discount_include;
                $show_amount = $amount_include;
                $show_unitprice = $unitprice_include;
                $tax_include_sum    += $amount_tax;
                $discount_include_sum += $discount_include;
            } else {
                $out_tax_exists = true;
                $show_price  = $price_exclude;
                $show_discount = $discount_exclude;
                $show_amount  = $amount_exclude;
                $show_unitprice = $unitprice_exclude;
                $tax_exclude_sum    += $amount_tax;
                $discount_exclude_sum += $discount_exclude;
            }

            $price_sum += $show_price;
            $discount_sum += $show_discount;
            $amount_sum += $show_amount;
            $amount_include_sum += $amount_include;
            $amount_exclude_sum += $amount_exclude;

//			$pdf->SetFont($defaultfont, '', 9);
//			$pdf->MultiCell( 69, $h, $rec['itemname'],	 $b, 'L', 0, 1, $posix1, $y+0.5);
			$pdf->SetFont($defaultfont, '', 8);
			$pdf->MultiCell( 69, $h, $rec['itemname'],	 $b, 'L', 0, 1, $posix1, $y+0.75);
			$pdf->SetFont($defaultfont, '', 8);
			$pdf->MultiCell(124, $h, '東京オートサロン 2020', $b, 'L', 0, 1, $posix2, $y+0.5);
            $detail = $rec['itemdetail'];
            if ($show_tax_include_flag) {
                $detail .= ' (税込 ' . ($tax * 100) . '%)';
            } else {
                $detail .= ' (税抜)';
            }
            $pdf->MultiCell( 49, $h, $detail,  $b, 'L', 0, 1, $posix2, $y+$h+0.5);
			$pdf->SetFont($defaultfont, '', 11);
			if (intval($rec['quantity']) == floatval($rec['quantity'])) {
				$rec['quantity'] = intval($rec['quantity']);
			}
//            $unitprice = $rec['unitprice'];
//            $price = $rec['price'];
//            $disc  = $rec['discount'];
			$pdf->MultiCell( 19, $h, $rec['quantity'], $b, 'C', 0, 1, $posix3, $y+$h);
            $pdf->writeHTMLCell( 29, 0, $posix4, $y+$h, "￥".$this->showNumber($show_unitprice), $b, 0, 0, true, 'R');
            $pdf->writeHTMLCell( 29, 0, $posix6, $y+$h, "￥".$this->showNumber($show_price), $b, 0, 0, true, 'R');
//			$subtotal += $price;
//			$discount += $disc;
			if ($count == $i+1) {
				$pdf->SetFont($defaultfont, '', 9);
				$pdf->MultiCell( 24, $h, "小計", $b, 'R', 0, 1, $posix4, $y+$h*2+0.5);
				$pdf->SetFont($defaultfont, '', 11);
				$pdf->writeHTMLCell( 29, 0, $posix6, $y+$h*2, "￥".number_format($price_sum), $b, 0, 0, true, 'R');
			}
			$y += $range;

			if ($y >= $starty + ($h * 27)) {
				$pdf->AddPage();
				$pdf->setSourceFile($templatefile);
				$tpl = $pdf->importPage(2);
				$pdf->useTemplate($tpl);
				$y = 29.5;
			}
		}
		$y += $range;
		$pdf->SetFont($defaultfont, '', 9);
        $pdf->MultiCell(46.5, 0, "明細計", 0, 'R', 0, 1, $posix3, $y+0.5);
		$pdf->SetFont($defaultfont, '', 11);
        $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($price_sum), 0, 0, 0, true, 'R');

        if ($discount_include_sum > 0) {
			$y += $h;
			$pdf->SetFont($defaultfont, '', 7);
            $pdf->MultiCell(46.5, 0, "特別割引(税込 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3, $y+0.5);
			$pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "▲￥".number_format($discount_include_sum), 0, 0, 0, true, 'R');
		}
        if ($discount_exclude_sum > 0) {
            $y += $h;
            $pdf->SetFont($defaultfont, '', 8);
            $pdf->MultiCell(46.5, 0, "特別割引(税抜)", 0, 'R', 0, 1, $posix3, $y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "▲￥".number_format($discount_exclude_sum), 0, 0, 0, true, 'R');
        }

        if ($out_tax_exists) {
            $y += $h * 2;
            $pdf->SetFont($defaultfont, '', 9);
            $pdf->MultiCell(46.5, 0, "合計", 0, 'R', 0, 1, $posix3,$y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($amount_sum), 0, 0, 0, true, 'R');

            if ($tax_include_sum) {
                $y += $h;
                $pdf->SetFont($defaultfont, '', 7);
                $pdf->MultiCell(46.5, 0, "消費税(税込明細分 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
                $pdf->SetFont($defaultfont, '', 11);
                $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($tax_include_sum), 0, 0, 0, true, 'R');
            }
            if ($tax_exclude_sum) {
                $y += $h;
                $pdf->SetFont($defaultfont, '', 7);
                $pdf->MultiCell(46.5, 0, "消費税(税抜明細分 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
                $pdf->SetFont($defaultfont, '', 11);
                $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($tax_exclude_sum), 0, 0, 0, true, 'R');
            }
            $y += $h;
            $pdf->SetFont($defaultfont, '', 7);
            $pdf->MultiCell(46.5, 0, "今回御見積額(税込 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($amount_include_sum), 0, 0, 0, true, 'R');
        } else {
            $y += $h;
            $pdf->SetFont($defaultfont, '', 7);
            $pdf->MultiCell(46.5, 0, "今回御見積額(税込 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($amount_include_sum), 0, 0, 0, true, 'R');
        }

//        if ($out_tax) {
//            $y += $h * 2;
//            $pdf->SetFont($defaultfont, '', 9);
//            $pdf->MultiCell(46.5, 0, "今回御見積額(税抜)", 0, 'R', 0, 1, $posix3,$y+0.5);
//            $pdf->SetFont($defaultfont, '', 11);
//            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($subtotal-$discount), 0, 0, 0, true, 'R');
//
//            $y += $h;
//            $pdf->SetFont($defaultfont, '', 9);
//            $pdf->MultiCell(46.5, 0, "消費税", 0, 'R', 0, 1, $posix3,$y+0.5);
//            $pdf->SetFont($defaultfont, '', 11);
//            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format(($subtotal-$discount) * 0.1), 0, 0, 0, true, 'R');
//
//            $y += $h;
//            $pdf->SetFont($defaultfont, '', 9);
//            $pdf->MultiCell(46.5, 0, "今回御見積額(税込)", 0, 'R', 0, 1, $posix3,$y+0.5);
//            $pdf->SetFont($defaultfont, '', 11);
//            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format(($subtotal-$discount) * 1.1), 0, 0, 0, true, 'R');
//        }

	}

	public function issue($uno='', $preview='')
	{
		$this->check_action();
		$post = $this->input->post();

		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uno);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix.'_nodata');
		} else {
			// 受理書を発行したら発行フラグと発行日を更新
			$this->invoice_model->issue(FALSE, $uno);

			$foreign = $this->invoice_model->read(FALSE, $uno);
			$foreign['comment'] = $post['comment'];
			if (trim($foreign['comment']) == '') {
				$foreign['comment'] = '※下記口座に<span style="color:#c00"><b>2019年11月15日(金)</b></span>までにお振込みくださいますよう、お願い致します。<br>※出展料が指定期日までに振り込まれない場合は、ご出展をお断りさせて頂きます。';
			}

			// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
			// $this->load->library('pdf');
			$this->load->library('Pdf_lib');
			// Upgrade PHP7 - Rename class to make it loadable - End by TTM
			$pdf = new FPDI(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(12, 16, 8);
			$pdf->setPrintHeader(FALSE);
			$pdf->setPrintFooter(FALSE);
			$pdf->SetHeaderMargin(2);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetAutoPageBreak(TRUE, 10);

			// Page 1
			$pdf->AddPage();
			$pdf->setSourceFile(APPPATH . 'views/pdf/invoice_1.pdf');
			$tpl = $pdf->importPage(1);
			$pdf->useTemplate($tpl);
			$this->issue_detail($pdf, $foreign, APPPATH.'views/pdf/invoice_1.pdf');

			// Page 2
			$pdf->AddPage();
			$pdf->setSourceFile(APPPATH . 'views/pdf/invoice_2.pdf');
			$tpl = $pdf->importPage(1);
			$pdf->useTemplate($tpl);
			$this->issue_detail($pdf, $foreign, APPPATH.'views/pdf/invoice_2.pdf');

			// コミットしてダウンロード
			$pdf->lastPage();
//			$filename = 'invoice-' . $foreign['invoiceno'] . '.pdf';
			$filename = 'invoice-' . $foreign['invoiceno'] . '-' . $foreign['billid'] . '.pdf';
//die(var_export($foreign) . $filename);

			header('Content-Type: application/pdf');
			header('Cache-Control: max-age=0');
			if ($preview != '') {
				$pdf->Output();
			} else {
				$pdf->Output($filename, 'D');
			}
		}
	}

	protected function issue_detail(&$pdf, &$foreign, $templatefile)
	{
//		$defaultfont = 'kozminproregular';
//		$defaultfont = 'ms-mincho';
		$defaultfont = 'ipam';

        $this->config->load('tax', TRUE, TRUE);
        $tax = $this->config->item('tax', 'tax') / 100;

		// 宛先の表示
		$zip='〒'.$foreign['zip'];
		$dist_x=18;
		$dist_y=18;
		$line=4;
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(0, 0, $zip, 0, 'J', 0, 1, $dist_x, $dist_y);
		$pdf->MultiCell(0, 0, $foreign['prefecture'] . $foreign['address1'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*1));
		$pdf->MultiCell(0, 0, $foreign['address2'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*2));
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(0, 0, $foreign['corpname'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*4));
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(80, 0, $foreign['division'] . ($foreign['division']==''?'':' ') . $foreign['position'] , 0, 'L', 0, 1, $dist_x, $dist_y+($line*5.2));
		$pdf->SetFont($defaultfont, '', 14);
		$pdf->MultiCell(80, 0, $foreign['fullname'] . ' 様', 0, 'R', 0, 1, $dist_x, $dist_y+($line*7));

		$pdf->SetFont($defaultfont, '', 11);
		$pdf->MultiCell(182, 0, date('Y年m月d日', strtotime($foreign['issuedate'])), 0, 'R', 0, 1, $dist_x, $dist_y+($line*4.5));
		$pdf->MultiCell(182, 0, '請求書番号:' . $foreign['invoiceno'], 0, 'R', 0, 1, $dist_x, $dist_y+($line*5.5));

		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(190, 0, 'ページ: 1 / 1', 0, 'R', 0, 1, $dist_x, 255);

		if ($foreign['amount'] > 0) {
			$pdf->SetFont($defaultfont, '', 20);
			$pdf->writeHTMLCell( 58, 0, 9, 89.5, "￥".number_format($foreign['amount']), 0, 0, 0, true, 'C');
			$pdf->SetFont($defaultfont, '', 10);

			$amount = $foreign['amount'];
			$amount_ex_tax = ceil($amount / (1 + $tax));
			$intax  = $amount - $amount_ex_tax;
			$pdf->writeHTMLCell( 61, 0, 9, 98, "(内消費税 " . ($tax * 100) . "% ￥".number_format($intax) .")", 0, 0, 0, true, 'C');
		} else {
			$pdf->SetFont($defaultfont, '', 20);
			$pdf->writeHTMLCell( 58, 0, 9, 90, "＊＊＊", 0, 0, 0, true, 'C');
		}
		$pdf->SetFont($defaultfont, '', 9.5);
//		$pdf->MultiCell(138, 0, $foreign['comment'], 0, 'R', 0, 1, $dist_x+48, 103);
		$pdf->writeHTMLCell(138, 0, $dist_x+51, 96, $foreign['comment'], 0, 0, 0, true, 'L');
		$b = 0;
		$h = 4.575;
		$starty = 113;

		$posix1=8;
		$posix2=77.5;
		$posix3=128;
		$posix4=145;
		$posix6=174;

		$range=$h*3;
		$count=count($foreign['detail']);

		$y = $starty;
		$subtotal = 0;
		$discount = 0;
        $tax_include_sum = 0;
        $tax_exclude_sum = 0;
        $price_sum       = 0;
        $discount_sum    = 0;
        $amount_sum      = 0;
        $amount_include_sum = 0;
        $amount_exclude_sum = 0;
        $discount_include_sum = 0;
        $discount_exclude_sum = 0;

        $in_tax_exists = false;
        $out_tax_exists = false;

		for($i=0;$i<$count;$i++){
			$rec = $foreign['detail'][$i];

            $base_price      = $rec['price'];
            $base_discount   = $rec['discount'];
            $base_amount     = $rec['amount'];
            $base_unitprice  = $rec['unitprice'];
            $tax_exclude_flg = $rec['tax_exclude_flag'] * 1;
            $show_tax_include_flag  = (isset($_POST['uchizei_' . intval($rec['invoicedid'])]) && $_POST['uchizei_' . intval($rec['invoicedid'])] == '1');

            $quantity  = intval($rec['quantity']);
            if (!$quantity) {
                $quantity = 1;
            }
            if ($tax_exclude_flg) {
                $price_exclude    = $base_price;
                $discount_exclude = $base_discount;
                $amount_exclude   = $base_amount;
                $unitprice_exclude = $base_unitprice;
                $price_tax = ceil($price_exclude * $tax);
                $discount_tax = ceil($discount_exclude * $tax);
                $amount_tax = ceil($amount_exclude * $tax);
                $unitprice_tax = ceil($unitprice_exclude * $tax);
                $price_include = $price_exclude + $price_tax;
                $discount_include = $discount_exclude + $discount_tax;
                $amount_include = $amount_exclude + $amount_tax;
                $unitprice_include = $unitprice_exclude + $unitprice_tax;
            } else {
                $price_include = $base_price;
                $discount_include = $base_discount;
                $amount_include = $base_amount;
                $unitprice_include = $base_unitprice;
                $discount_exclude = floor($discount_include / ($tax + 1));
                $unitprice_exclude = $unitprice_include / ($tax + 1);
                $price_exclude = $unitprice_exclude * $quantity;
                $amount_exclude = $price_exclude - $discount_exclude;
                $price_tax = $price_include - $price_exclude;
                $discount_tax = $discount_include - $discount_exclude;
                $amount_tax = $amount_include - $amount_exclude;
                $unitprice_tax = $unitprice_include - $unitprice_exclude;
            }
            if ($show_tax_include_flag) {
                $in_tax_exists = true;
                $show_price  = $price_include;
                $show_discount = $discount_include;
                $show_amount = $amount_include;
                $show_unitprice = $unitprice_include;
                $tax_include_sum    += $amount_tax;
                $discount_include_sum += $discount_include;
            } else {
                $out_tax_exists = true;
                $show_price  = $price_exclude;
                $show_discount = $discount_exclude;
                $show_amount  = $amount_exclude;
                $show_unitprice = $unitprice_exclude;
                $tax_exclude_sum    += $amount_tax;
                $discount_exclude_sum += $discount_exclude;
            }

            $price_sum += $show_price;
            $discount_sum += $show_discount;
            $amount_sum += $show_amount;
            $amount_include_sum += $amount_include;
            $amount_exclude_sum += $amount_exclude;

//			$pdf->SetFont($defaultfont, '', 9);
//			$pdf->MultiCell( 69, $h, $rec['itemname'],	 $b, 'L', 0, 1, $posix1, $y+0.5);
			$pdf->SetFont($defaultfont, '', 8);
			$pdf->MultiCell( 69, $h, $rec['itemname'],	 $b, 'L', 0, 1, $posix1, $y+0.75);
			$pdf->SetFont($defaultfont, '', 8);
			$pdf->MultiCell(124, $h, '東京オートサロン 2020', $b, 'L', 0, 1, $posix2, $y+0.5);

			$detail = $rec['itemdetail'];
			if ($show_tax_include_flag) {
                $detail .= ' (税込 ' . ($tax * 100) . '%)';
            } else {
                $detail .= ' (税抜)';
            }
			$pdf->MultiCell( 49, $h, $detail,  $b, 'L', 0, 1, $posix2, $y+$h+0.5);
			$pdf->SetFont($defaultfont, '', 11);
			if (intval($rec['quantity']) == floatval($rec['quantity'])) {
				$rec['quantity'] = intval($rec['quantity']);
			}
//            $unitprice = $rec['unitprice'];
//			$price = $rec['price'];
//			$disc  = $rec['discount'];

//            $this->config->load('tax', TRUE, TRUE);
//            $tax = $this->config->item('tax', 'tax');
			$pdf->MultiCell( 19, $h, $rec['quantity'], $b, 'C', 0, 1, $posix3, $y+$h);
			$pdf->writeHTMLCell( 29, 0, $posix4, $y+$h, "￥".$this->showNumber($show_unitprice), $b, 0, 0, true, 'R');
			$pdf->writeHTMLCell( 29, 0, $posix6, $y+$h, "￥".$this->showNumber($show_price), $b, 0, 0, true, 'R');
//			$subtotal += $price;
//			$discount += $disc;
			if ($count == $i+1) {
				$pdf->SetFont($defaultfont, '', 9);
				$pdf->MultiCell( 29, $h, "小計", $b, 'R', 0, 1, $posix4, $y+$h*2+0.5);
				$pdf->SetFont($defaultfont, '', 11);
				$pdf->writeHTMLCell( 29, 0, $posix6, $y+$h*2, "￥".number_format($price_sum), $b, 0, 0, true, 'R');
			}
			$y += $range;

			if ($y >= $starty + ($h * 27)) {
				$pdf->AddPage();
				$pdf->setSourceFile($templatefile);
				$tpl = $pdf->importPage(2);
				$pdf->useTemplate($tpl);
				$y = 29.5;
			}
		}
		$y += $range;
		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(46.5, 0, "明細計", 0, 'R', 0, 1, $posix3, $y+0.5);
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($price_sum), 0, 0, 0, true, 'R');

		if ($discount_include_sum > 0) {
			$y += $h;
			$pdf->SetFont($defaultfont, '', 7);
			$pdf->MultiCell(46.5, 0, "特別割引(税込 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3, $y+0.5);
			$pdf->SetFont($defaultfont, '', 11);
			$pdf->writeHTMLCell(29, 0, $posix6, $y, "▲￥".number_format($discount_include_sum), 0, 0, 0, true, 'R');
		}
        if ($discount_exclude_sum > 0) {
            $y += $h;
            $pdf->SetFont($defaultfont, '', 8);
            $pdf->MultiCell(46.5, 0, "特別割引(税抜)", 0, 'R', 0, 1, $posix3, $y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "▲￥".number_format($discount_exclude_sum), 0, 0, 0, true, 'R');
        }

		if ($out_tax_exists) {
            $y += $h * 2;
            $pdf->SetFont($defaultfont, '', 9);
            $pdf->MultiCell(46.5, 0, "合計", 0, 'R', 0, 1, $posix3,$y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($amount_sum), 0, 0, 0, true, 'R');

            if ($tax_include_sum) {
                $y += $h;
                $pdf->SetFont($defaultfont, '', 7);
                $pdf->MultiCell(46.5, 0, "消費税(税込明細分 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
                $pdf->SetFont($defaultfont, '', 11);
                $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($tax_include_sum), 0, 0, 0, true, 'R');
            }
            if ($tax_exclude_sum) {
                $y += $h;
                $pdf->SetFont($defaultfont, '', 7);
                $pdf->MultiCell(46.5, 0, "消費税(税抜明細分 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
                $pdf->SetFont($defaultfont, '', 11);
                $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($tax_exclude_sum), 0, 0, 0, true, 'R');
            }
            $y += $h;
            $pdf->SetFont($defaultfont, '', 7);
            $pdf->MultiCell(46.5, 0, "今回ご請求額(税込 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($amount_include_sum), 0, 0, 0, true, 'R');
        } else {
            $y += $h;
            $pdf->SetFont($defaultfont, '', 7);
            $pdf->MultiCell(46.5, 0, "今回ご請求額(税込 " . ($tax * 100) . "%)", 0, 'R', 0, 1, $posix3,$y+0.5);
            $pdf->SetFont($defaultfont, '', 11);
            $pdf->writeHTMLCell(29, 0, $posix6, $y, "￥".number_format($amount_include_sum), 0, 0, 0, true, 'R');
       }

	}

	function showNumber($number)
    {
        $syousuu = (string) ((round($number * 100) / 100) - floor(round($number, 5)));
        if ($syousuu !== '0' && $syousuu) {
            $syousuu = preg_replace('/^0\.?/', '', $syousuu);
            $length = strlen($syousuu);
            if ($length > 2) {
                $length = 2;
            }
        } else {
            $length = 0;
        }
        return number_format($number, $length);
    }

	function expire($uid)
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$this->get_record($data, $uid);
		if (!isset($data['foreign'][$this->foreign_keyid])) {
			$this->parser->parse($this->form_prefix . '_nodata');
		} else {
			$this->session->set_flashdata('foreign', $data['foreign']);
			$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
		}
	}

	function expire_in()
	{
		$this->check_action();
		$data['foreign'] = $this->input->post();
		$data['post'] = $this->input->post();
		$this->session->keep_flashdata('foreign');

		// レコードの削除
		$result = $this->delete_record($data['foreign']);
		$line = $this->lang->line($result !== FALSE ? 'M2003':'N4003');
		$message = explode("\n", $line);
		$this->session->set_flashdata('message', $message);
		$this->session->set_flashdata('result', ($result !== FALSE) ? '1':'0');

		if ($result !== FALSE) {
			$this->log_history('削除', $data['foreign'][$this->foreign_keyid]);
			$this->after_delete($data);
		}

		redirect(uri_redirect_string() . '/expired');
	}
	function expired()
	{
		$this->completed($this->form_prefix.'_'.__FUNCTION__);
	}

	public function download_detail($mode='')
	{
		$data = $this->setup_data();
		$this->setup_form($data);

		// 防御
		if (uri_folder_string() == '/ex') {
			die('Prohibited');
		}

		if ($mode == 'csv') {
			$datestr = date('YmdHi');
			$filename = strtolower(get_class($this)).'-all-'.$datestr.'.csv';
			$data = $this->download_detail_csv();
			$data = mb_convert_encoding($data,'SJIS-win','UTF-8');
			$this->load->helper('download');
			force_download($filename, $data);
		} else if ($mode == 'xlsx') {
			$data = $this->download_detail_xlsx();
		}
	}
	protected function download_detail_csv()
	{
		$this->load->dbutil();

		$this->download_detail_build();
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		$this->load->helper('form');
		return deco_csv_from_result($query);
	}

	protected function download_detail_xlsx()
	{
		$this->load->dbutil();

		$this->download_detail_build();
		$query = $this->db->get();
		if ($query === FALSE) {
			return 'error';
		}
		return $this->download_xls_from_result($query, strtolower(get_class($this)));
	}

	protected function download_detail_build()
	{
        $this->config->load('tax', TRUE, TRUE);
        $tax = 1 + ($this->config->item('tax', 'tax') / 100);

		$this->db->select('vv.invoiceno 請求書No');
		$this->db->select('bb.b_corpname 出展者, bb.corpkana 出展者カナ, bb.brandname 表示名, bb.brandkana 表示名カナ,  bb.promotion 媒体ブース');
		$this->db->select('vv.issuedate 請求日');
		$this->db->select('iv.itemname 会社名, iv.itemdetail 項目');
        $this->db->select('IF(iv.tax_exclude_flag=1,iv.unitprice*' . $tax . ',iv.unitprice) 単価', FALSE);
        $this->db->select('iv.quantity 数量');
		$this->db->select('IF(iv.tax_exclude_flag=1,iv.price*' . $tax . ',iv.price) 金額', FALSE);
        $this->db->select('IF(iv.tax_exclude_flag=1,iv.discount*' . $tax . ',iv.discount) 値引額', FALSE);
		$this->db->select('IF(iv.tax_exclude_flag=1,iv.amount*' . $tax . ',iv.amount) 請求額', FALSE);
        $this->db->select('ip.paymentdate 入金日, ip.sumpaymentamount 消込額');
		$this->db->select('IF(iv.tax_exclude_flag=1,iv.amount*' . $tax . '-ip.sumpaymentamount,iv.amount-ip.sumpaymentamount) 残金', FALSE);
		$this->db->select('IF(iv.tax_exclude_flag=1,IF((iv.amount*' . $tax . ')<=ip.sumpaymentamount,1,NULL),IF(iv.amount<=ip.sumpaymentamount,1,NULL)) 入金済', FALSE);
		$this->db->select('iv.updated 更新日');
		$this->db->from('invoice_detail iv');
		$this->db->join('invoice vv', 'vv.invoiceid = iv.invoiceid AND vv.expired = 0');
		$this->db->join('v_billing_ex_search bb', 'bb.billid = vv.billid');
		$this->db->join('v_invoice_payment_detail ip', 'ip.invoicedid = iv.invoicedid', 'left');
		$this->db->where('iv.expired', 0);
		$this->db->order_by('vv.invoiceid, iv.seqno');
	}
}
// vim:ts=4
// End of file controllers/op/invoice.php
