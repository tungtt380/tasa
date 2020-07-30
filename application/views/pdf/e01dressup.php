<?php
//	前提は、$data['foreign'] = $query->row_array();

	// インスタンス化してモジュールをロード
	$ci =& get_instance();
	// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
	// $ci->load->library('pdf');
	$ci->load->library('Pdf_lib');
	// Upgrade PHP7 - Rename class to make it loadable - End by TTM
	$pdf = new FPDI_EX(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A3', true, 'UTF-8', false);
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(12, 16, 12);
	$pdf->setPrintHeader(FALSE);
	$pdf->setPrintFooter(FALSE);
	$pdf->SetHeaderMargin(2);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 10);
	$pdf->SetDrawColor(32, 32, 32);
	$pdf->SetFillColor(128,128,128);

	$spacing = 0.127;
	$b = '1';

	foreach($foreign as $key=>$val) {
		$val = mb_convert_kana($val, 'KVsa', 'UTF-8');
		$foreign[$key] = str_replace("\n", ' ', $val);
	}

	// 投票番号が存在するのは、プロモーションコードが存在しないとき＆小間番号が存在する時
	if ($foreign['exhboothno'] != '' && $foreign['promotion'] == '') {
		$number = $foreign['exhboothno'] . str_pad($foreign['seqno'], '2', '0', STR_PAD_LEFT);
	} else {
		$number = '';
	}
	$enableroad = $foreign['enableroad'] ? '公道走行可':'公道走行不可';
	$sectionarr = array(
		1 => 'コンセプトカー',
		2 => 'ドレスアップカー',
		3 => 'チューニングカー',
		4 => 'セダン',
		5 => 'ミニバン/ワゴン',
		6 => 'SUV',
		7 => 'コンパクトカー',
		8 => 'インポートカー',
		9 => '参考出品',
	);
	$sectionstr = isset($sectionarr[$foreign['sectionno']]) ? $sectionarr[$foreign['sectionno']]:'';

	// ページを追加して、テンプレートを設定
	$pdf->AddPage();
	$pdf->setSourceFile(APPPATH . '/views/pdf/2012dressup-z.pdf');
	$tpl = $pdf->importPage(1);
	$pdf->useTemplate($tpl);

	$pdf->SetFont('kozgopromedium', 'B', 18);
	$pdf->SetTextColor(255);
	$pdf->SetFontStretching(80);
	$pdf->SetFontSpacing($spacing);
	$pdf->MultiCell(50, 0, $enableroad, $b, 'L', 0, 1, 250, 8);

	$pdf->SetFont('kozgoproheavy', '', 36);
	$pdf->SetFontStretching(80);
	$pdf->SetFontSpacing(0);
	$pdf->MultiCellEx(220, 0, $foreign['carname'], $b, 'L', 0, 1, 70, 16);

	$pdf->SetFont('helvetica', 'B', 32);
	$pdf->SetTextColor(220,20,60);
	$pdf->SetFontSpacing(0.127);
	$pdf->MultiCell(30, 0, $number, $b, 'C', 0, 1, 27, 43);

	$fw = 235;
	$hw = 115;
	$qw = 55;
	$line = 50;

	// 出展者名
	$pdf->SetFont('kozgopromedium', 'B', 20);
	$pdf->SetTextColor(0);
	$pdf->MultiCell(120, 8, $foreign['brandname'], $b, 'L', 0, 1, 60, $line-1);
	$pdf->MultiCell(80, 8, $sectionstr.'部門', $b, 'L', 0, 1, 186, $line-1);
	$pdf->MultiCell(30, 8, $foreign['exhboothno'], $b, 'L', 0, 1, 250, $line-1);

	// コンセプト
	$pdf->SetFont('kozgopromedium', 'B', 17);
	$pdf->SetFontStretching(80);
	$pdf->MultiCellEx($fw, 28, $foreign['concept'], $b, 'L', 0, 1, 36, $line+19.5);

	// ベース
	$pdf->MultiCellEx($hw, 8, $foreign['basecarname'], $b, 'L', 0, 1, 36, $line+60);
	$pdf->MultiCellEx(70, 8, $foreign['basecartype'], $b, 'L', 0, 1, 156, $line+60);
	$pdf->MultiCellEx(40, 8, $foreign['basecaryear'], $b, 'L', 0, 1, 233, $line+60);

	// 外装関係
	$pdf->MultiCellEx($hw, 8, $foreign['aeroname'], $b, 'L', 0, 1, 36, $line+79);
	$pdf->MultiCellEx($hw, 8, $foreign['bodycolor'], $b, 'L', 0, 1, 156, $line+79);
	$pdf->MultiCellEx($fw, 18, $foreign['dressupcomment'], $b, 'L', 0, 1, 36, $line+89);

	// 内装関係
	$pdf->MultiCellEx($hw, 8, $foreign['sheet'], $b, 'L', 0, 1, 36, $line+119);
	$pdf->MultiCellEx($hw, 8, $foreign['steering'], $b, 'L', 0, 1, 156, $line+119);
	$pdf->MultiCellEx($hw, 8, $foreign['meter'], $b, 'L', 0, 1, 36, $line+129);
	$pdf->MultiCellEx($hw, 8, $foreign['floormat'], $b, 'L', 0, 1, 156, $line+129);
	$pdf->MultiCellEx($hw, 8, $foreign['audio'], $b, 'L', 0, 1, 36, $line+139);
	$pdf->MultiCellEx($hw, 8, $foreign['carnavi'], $b, 'L', 0, 1, 156, $line+139);
	$pdf->MultiCellEx($fw, 38, $foreign['etc'], $b, 'L', 0, 1, 36, $line+149);

	// サスペンション
	$pdf->MultiCellEx($fw, 8, $foreign['suspension'], $b, 'L', 0, 1, 36, $line+199);
	$pdf->MultiCellEx($hw, 8, $foreign['absorber'], $b, 'L', 0, 1, 36, $line+209);
	$pdf->MultiCellEx($hw, 8, $foreign['spring'], $b, 'L', 0, 1, 156, $line+209);
	$pdf->MultiCellEx($hw, 8, $foreign['brake'], $b, 'L', 0, 1, 36, $line+219);
	$pdf->MultiCellEx($hw, 8, $foreign['suspensioncomment'], $b, 'L', 0, 1, 156, $line+219);

	// ホイール
	$pdf->MultiCellEx($hw, 8, $foreign['wheel'], $b, 'L', 0, 1, 36, $line+239);
	$pdf->MultiCellEx($qw, 8, $foreign['frontsize'], $b, 'L', 0, 1, 156, $line+239);
	$pdf->MultiCellEx($qw, 8, $foreign['rearsize'], $b, 'L', 0, 1, 215, $line+239);

	// タイヤ
	$pdf->MultiCellEx($hw, 8, $foreign['tire'], $b, 'L', 0, 1, 36, $line+259);
	$pdf->MultiCellEx($qw, 8, $foreign['fronttire'], $b, 'L', 0, 1, 156, $line+259);
	$pdf->MultiCellEx($qw, 8, $foreign['reartire'], $b, 'L', 0, 1, 215, $line+259);

	// エンジン系
	$pdf->MultiCellEx($hw, 8, $foreign['enginetype'], $b, 'L', 0, 1, 36, $line+279);
	$pdf->MultiCellEx(25, 8, $foreign['enginecc'], $b, 'R', 0, 1, 142, $line+279);
	if ($foreign['outputnum'] != '') {
		$pdf->SetTextColor(128,128,128);
		$pdf->SetFont('kozminproregular', '', 9.5);
		$pdf->MultiCellEx(25, 8, ($foreign['outputunit'] == 2 ? 'ps':'kw'), $b, 'R', 0, 1, 170, $line+283.5);
		$pdf->SetFont('kozgopromedium', 'B', 17);
		$pdf->SetTextColor(0);
	}
	$pdf->MultiCellEx(25, 8, $foreign['outputnum'], $b, 'R', 0, 1, 164, $line+279);
	$pdf->MultiCellEx(25, 8, $foreign['outputrpm'], $b, 'R', 0, 1, 187, $line+279);
	if ($foreign['torquenum'] != '') {
		$pdf->SetTextColor(128,128,128);
		$pdf->SetFont('kozminproregular', '', 9.5);
		$pdf->MultiCellEx(25, 8, ($foreign['torqueunit'] == 2 ? 'Nm':'kg'), $b, 'R', 0, 1, 216.5, $line+283.5);
		$pdf->SetFont('kozgopromedium', 'B', 17);
		$pdf->SetTextColor(0);
	}
	$pdf->MultiCellEx(25, 8, $foreign['torquenum'], $b, 'R', 0, 1, 211, $line+279);
	$pdf->MultiCellEx(25, 8, $foreign['torquerpm'], $b, 'R', 0, 1, 233, $line+279);
	$pdf->MultiCellEx($fw, 28, $foreign['enginecomment'], $b, 'L', 0, 1, 36, $line+289);

	// 排気系
	$pdf->MultiCellEx($fw, 8, $foreign['muffler'], $b, 'L', 0, 1, 36, $line+328);

	// その他チューニング(18pt/52letter)
	$pdf->MultiCellEx($fw, 8, $foreign['comment'], $b, 'L', 0, 1, 36, $line+348);

	$pdf->lastPage();

	// コミットしてダウンロード
	header('Content-Type: application/pdf');
	header('Cache-Control: max-age=0');
	if ($preview != '') {
		$pdf->Output();
	} else {
		$filename = 'e01car-'.($number=='' ? :$number.'-').date('YmdHi').'-dressup.pdf';
		$pdf->Output($filename, 'D');
	}
