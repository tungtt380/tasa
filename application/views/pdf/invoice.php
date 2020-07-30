<?php
//	$foreign = $this->invoice_model->read(FALSE, $uno);
//	$foreign['comment'] = $post['comment'];
	if (! isset($foreign['comment']) || trim($foreign['comment']) == '') {
		$foreign['comment'] = '※下記口座に<span style="color:#c00"><b>2015年11月27日(金)</b></span>までにお振込みくださいますよう、お願い致します。';
	}

//	$defaultfont = 'kozminproregular';
//	$defaultfont = 'ms-mincho';
    $defaultfont = 'ipam';

    // インスタンス化してモジュールをロード
    $ci =& get_instance();
    // Upgrade PHP7 - Rename class to make it loadable - Start by TTM
	// $ci->load->library('pdf');
	$ci->load->library('Pdf_lib');
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

	// Page 1 - 宛先の表示
	$zip = '〒'.$foreign['zip'];
	$dist_x = 18;
	$dist_y = 18;
	$line   = 4;
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $zip, 0, 'J', 0, 1, $dist_x, $dist_y);
	$pdf->MultiCell(0, 0, $foreign['prefecture'] . $foreign['address1'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*1));
	$pdf->MultiCell(0, 0, $foreign['address2'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*2));
	$pdf->MultiCell(0, 0, $foreign['corpname'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*4));
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
		$pdf->writeHTMLCell( 58, 0, 9, 89.5, '￥'.number_format($foreign['amount']), 0, 0, 0, true, 'C');
		$pdf->SetFont($defaultfont, '', 10);
		$pdf->writeHTMLCell( 61, 0, 9, 98, '(内消費税 ￥'.number_format($foreign['intax']).')', 0, 0, 0, true, 'C');
	} else {
		$pdf->SetFont($defaultfont, '', 20);
		$pdf->writeHTMLCell( 58, 0, 9, 90, '＊＊＊', 0, 0, 0, true, 'C');
	}
	$pdf->SetFont($defaultfont, '', 9.5);
//	$pdf->MultiCell(138, 0, $foreign['comment'], 0, 'R', 0, 1, $dist_x+48, 103);
	$pdf->writeHTMLCell(138, 0, $dist_x+48, 103, $foreign['comment'], 0, 0, 0, true, 'R');

	$b = 0;
	$h = 4.575;
	$y = $ypos = 113;

	$xpos1 = 8;
	$xpos2 = 77.5;
	$xpos3 = 128;
	$xpos4 = 149;
	$xpos6 = 174;

	$range = $h*3;
	$count = count($foreign['detail']);

	$subtotal = 0;
	$discount = 0;

	for($i=0;$i<$count;$i++){
		$rec = $foreign['detail'][$i];
		$pdf->SetFont($defaultfont, '', 8.5);
		$pdf->MultiCell( 69, $h, $rec['itemname'],   $b, 'L', 0, 1, $xpos1, $y+0.75);
		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(124, $h, '東京オートサロン 2013 with NAPAC', $b, 'L', 0, 1, $xpos2, $y+0.5);
		$pdf->MultiCell( 49, $h, $rec['itemdetail'],  $b, 'L', 0, 1, $xpos2, $y+$h+0.5);
		$pdf->SetFont($defaultfont, '', 11);
		if (intval($rec['quantity']) == floatval($rec['quantity'])) {	// 4.0 => 4
			$rec['quantity'] = intval($rec['quantity']);
		}
		$pdf->MultiCell( 19, $h, $rec['quantity'], $b, 'C', 0, 1, $xpos3, $y+$h);
		$pdf->writeHTMLCell( 25, 0, $xpos4, $y+$h, '￥'.number_format($rec['unitprice']), $b, 0, 0, true, 'R');
		$pdf->writeHTMLCell( 29, 0, $xpos6, $y+$h, '￥'.number_format($rec['price']), $b, 0, 0, true, 'R');
		$subtotal += $rec['price'];
		$discount += $rec['discount'];
		if ($count == $i+1) {
			$pdf->SetFont($defaultfont, '', 9);
			$pdf->MultiCell( 24, $h, '小計', $b, 'R', 0, 1, $xpos4, $y+$h*2+0.5);
			$pdf->SetFont($defaultfont, '', 11);
			$pdf->writeHTMLCell( 29, 0, $xpos6, $y+$h*2, '￥'.number_format($subtotal), $b, 0, 0, true, 'R');
		}
		$y += $range;
	}
	$y += $range;
	$pdf->SetFont($defaultfont, '', 9);
	$pdf->MultiCell(45, 0, '合計', 0, 'R', 0, 1, $xpos3, $y+0.5);
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->writeHTMLCell(29, 0, $xpos6, $y, '￥'.number_format($subtotal), 0, 0, 0, true, 'R');

	if ($discount > 0) {
		$y += $h;
		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(45, 0, '特別割引', 0, 'R', 0, 1, $xpos3, $y+0.5);
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->writeHTMLCell(29, 0, $xpos6, $y, '▲￥'.number_format($discount), 0, 0, 0, true, 'R');
	}
	$y += $h;
	$pdf->SetFont($defaultfont, '', 9);
	$pdf->MultiCell(46.5, 0, '今回ご請求額(税込)', 0, 'R', 0, 1, $xpos3,$y+0.5);
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->writeHTMLCell(29, 0, $xpos6, $y, '￥'.number_format($subtotal-$discount), 0, 0, 0, true, 'R');

	// Page 2 - Page 1 と出力位置は同じ
	$pdf->AddPage();
	$pdf->setSourceFile(APPPATH . 'views/pdf/invoice_2.pdf');
	$tpl = $pdf->importPage(1);
	$pdf->useTemplate($tpl);

	$zip = '〒'.$foreign['zip'];
	$dist_x = 18;
	$dist_y = 18;
	$line   = 4;
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $zip, 0, 'J', 0, 1, $dist_x, $dist_y);
	$pdf->MultiCell(0, 0, $foreign['prefecture'] . $foreign['address1'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*1));
	$pdf->MultiCell(0, 0, $foreign['address2'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*2));
	$pdf->MultiCell(0, 0, $foreign['corpname'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*4));
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
		$pdf->writeHTMLCell( 58, 0, 9, 89.5, '￥'.number_format($foreign['amount']), 0, 0, 0, true, 'C');
		$pdf->SetFont($defaultfont, '', 10);
		$pdf->writeHTMLCell( 61, 0, 9, 98, '(内消費税 ￥'.number_format($foreign['intax']).')', 0, 0, 0, true, 'C');
	} else {
		$pdf->SetFont($defaultfont, '', 20);
		$pdf->writeHTMLCell( 58, 0, 9, 90, '＊＊＊', 0, 0, 0, true, 'C');
	}
	$pdf->SetFont($defaultfont, '', 9.5);
//	$pdf->MultiCell(138, 0, $foreign['comment'], 0, 'R', 0, 1, $dist_x+48, 103);
	$pdf->writeHTMLCell(138, 0, $dist_x+48, 103, $foreign['comment'], 0, 0, 0, true, 'R');

	$b = 0;
	$h = 4.575;
	$y = $ypos = 113;

	$xpos1 = 8;
	$xpos2 = 77.5;
	$xpos3 = 128;
	$xpos4 = 149;
	$xpos6 = 174;

	$range = $h*3;
	$count = count($foreign['detail']);

	$subtotal = 0;
	$discount = 0;

	for($i=0;$i<$count;$i++){
		$rec = $foreign['detail'][$i];
		$pdf->SetFont($defaultfont, '', 8.5);
		$pdf->MultiCell( 69, $h, $rec['itemname'],   $b, 'L', 0, 1, $xpos1, $y+0.75);
		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(124, $h, '東京オートサロン 2013 with NAPAC', $b, 'L', 0, 1, $xpos2, $y+0.5);
		$pdf->MultiCell( 49, $h, $rec['itemdetail'],  $b, 'L', 0, 1, $xpos2, $y+$h+0.5);
		$pdf->SetFont($defaultfont, '', 11);
		if (intval($rec['quantity']) == floatval($rec['quantity'])) {	// 4.0 => 4
			$rec['quantity'] = intval($rec['quantity']);
		}
		$pdf->MultiCell( 19, $h, $rec['quantity'], $b, 'C', 0, 1, $xpos3, $y+$h);
		$pdf->writeHTMLCell( 25, 0, $xpos4, $y+$h, '￥'.number_format($rec['unitprice']), $b, 0, 0, true, 'R');
		$pdf->writeHTMLCell( 29, 0, $xpos6, $y+$h, '￥'.number_format($rec['price']), $b, 0, 0, true, 'R');
		$subtotal += $rec['price'];
		$discount += $rec['discount'];
		if ($count == $i+1) {
			$pdf->SetFont($defaultfont, '', 9);
			$pdf->MultiCell( 24, $h, '小計', $b, 'R', 0, 1, $xpos4, $y+$h*2+0.5);
			$pdf->SetFont($defaultfont, '', 11);
			$pdf->writeHTMLCell( 29, 0, $xpos6, $y+$h*2, '￥'.number_format($subtotal), $b, 0, 0, true, 'R');
		}
		$y += $range;
	}
	$y += $range;
	$pdf->SetFont($defaultfont, '', 9);
	$pdf->MultiCell(45, 0, '合計', 0, 'R', 0, 1, $xpos3, $y+0.5);
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->writeHTMLCell(29, 0, $xpos6, $y, '￥'.number_format($subtotal), 0, 0, 0, true, 'R');

	if ($discount > 0) {
		$y += $h;
		$pdf->SetFont($defaultfont, '', 9);
		$pdf->MultiCell(45, 0, '特別割引', 0, 'R', 0, 1, $xpos3, $y+0.5);
		$pdf->SetFont($defaultfont, '', 11);
		$pdf->writeHTMLCell(29, 0, $xpos6, $y, '▲￥'.number_format($discount), 0, 0, 0, true, 'R');
	}
	$y += $h;
	$pdf->SetFont($defaultfont, '', 9);
	$pdf->MultiCell(46.5, 0, '今回ご請求額(税込)', 0, 'R', 0, 1, $xpos3,$y+0.5);
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->writeHTMLCell(29, 0, $xpos6, $y, '￥'.number_format($subtotal-$discount), 0, 0, 0, true, 'R');

	// ページはここで終了
	$pdf->lastPage();

	// コミットしてダウンロード
	header('Content-Type: application/pdf');
	header('Cache-Control: max-age=0');
	if ($preview != '') {
		$pdf->Output();
	} else {
		$filename = 'invoice-' . $foreign['invoiceno'] . '-' . $foreign['billid'] . '.pdf';
		$pdf->Output($filename, 'D');
	}

	// 受理書を発行したら発行フラグと発行日を更新(これはコントローラでやりたい)
//	$this->invoice_model->issue(FALSE, $uno);
