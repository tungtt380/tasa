<?php
//	前提は、$foreign = $data['foreign'];

//	$defaultfont = 'kozminproregular';
//	$defaultfont = 'ms-mincho';
	$defaultfont = 'ipam';

	// インスタンス化してモジュールをロード
	$ci =& get_instance();
	// Upgrade PHP7 - Rename class to make it loadable - Start by TTM
	// $ci->load->library('pdf');
	$ci->load->library('Pdf_lib');
	// Upgrade PHP7 - Rename class to make it loadable - End by TTM

	$pdf = new FPDI_EX(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(12, 16, 8);
	$pdf->setPrintHeader(FALSE);
	$pdf->setPrintFooter(FALSE);
	$pdf->SetHeaderMargin(2);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 10);

	$pdf->AddPage();

	$pdf->setSourceFile(APPPATH . '/views/pdf/acceptance_1.pdf');
	$tpl = $pdf->importPage(1);
	$pdf->useTemplate($tpl);

	// 印刷日の表示
	$printdate = date('Y年m月d日');
	$date_x = 160;
	$date_y = 12;
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $printdate, 0, 'R', 0, 1, $date_x, $date_y);

	// ここは本来ならばモデルから逆算するのが筋
	$foreign['zip']      = deco_zip($foreign['countrycode'], $foreign['zip']);
	$foreign['m_zip']    = deco_zip($foreign['countrycode'], $foreign['m_zip']);
	$foreign['b_zip']    = deco_zip($foreign['countrycode'], $foreign['b_zip']);
	$foreign['c_zip']    = deco_zip($foreign['countrycode'], $foreign['c_zip']);
	$foreign['d_zip']    = deco_zip($foreign['countrycode'], $foreign['d_zip']);
	$foreign['phone']    = deco_phone($foreign['countrycode'], $foreign['phone']);
	$foreign['fax']      = deco_phone($foreign['countrycode'], $foreign['fax']);
	$foreign['m_phone']  = deco_phone($foreign['countrycode'], $foreign['m_phone']);
	$foreign['m_fax']    = deco_phone($foreign['countrycode'], $foreign['m_fax']);
	$foreign['m_mobile'] = deco_phone($foreign['countrycode'], $foreign['m_mobile']);
	$foreign['b_phone']  = deco_phone($foreign['countrycode'], $foreign['b_phone']);
	$foreign['b_fax']    = deco_phone($foreign['countrycode'], $foreign['b_fax']);
	$foreign['c_phone']  = deco_phone($foreign['countrycode'], $foreign['c_phone']);
	$foreign['c_fax']    = deco_phone($foreign['countrycode'], $foreign['c_fax']);
	$foreign['c_mobile'] = deco_phone($foreign['countrycode'], $foreign['c_mobile']);
	$foreign['d_phone']  = deco_phone($foreign['countrycode'], $foreign['d_phone']);
	$foreign['d_fax']    = deco_phone($foreign['countrycode'], $foreign['d_fax']);

	// 宛先の表示
	$zip = '〒' . $foreign['m_zip'];
	$dist_x = 18;
	$dist_y = 58;
	$line = 4;

	$fulladdr = $foreign['m_prefecture'] . $foreign['m_address1'];
	$pdf->SetFont($defaultfont, '', 11);
	if (strlen($fulladdr) > 90) {
		$pdf->MultiCell(0, 0, $zip, 0, 'J', 0, 1, $dist_x, $dist_y-4.7);
		$pdf->MultiCell(103, 0, $fulladdr, 0, 'L', 0, 1, $dist_x, $dist_y+($line*0));
	} else {
		$pdf->MultiCell(0, 0, $zip, 0, 'J', 0, 1, $dist_x, $dist_y-2);
		$pdf->MultiCell(96, 0, $fulladdr, 0, 'L', 0, 1, $dist_x, $dist_y+($line*1));
	}
	$pdf->MultiCell(96, 0, $foreign['m_address2'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*2));
	$pdf->MultiCell(95, 0, $foreign['m_corpname'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*4));
	$pdf->MultiCell(95, 0, $foreign['m_position'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*6.5));
	$pdf->MultiCell(95, 0, $foreign['m_fullname'] . ' 様', 0, 'L', 0, 1, $dist_x, $dist_y+($line*8));

	// 出展会社名の表示
	$corp_xx = 52;
	$corp_x = 36;
	$corp_y = 154.3;
	$line   = 4.7;

	$fulladdr = '〒'.$foreign['zip'].'  '.$foreign['prefecture'].$foreign['address1'].' '.$foreign['address2'];
	$url = 'http://' . $foreign['url'];
	$pdf->MultiCellEx(186, 0, $foreign['corpname'], 0, 'L', 0, 1, $corp_x, $corp_y);
	$pdf->MultiCellEx(186, 0, $foreign['corpkana'], 0, 'L', 0, 1, $corp_x, $corp_y+($line*1));
	if (strlen($fulladdr) > 100) {
		$pdf->SetFont($defaultfont, '', 8.6);
		$pdf->MultiCell(186, 0, $fulladdr, 0, 'L', 0, 1, $corp_x, $corp_y+($line*2)+0.5);
	} else {
		$pdf->MultiCell(186, 0, $fulladdr, 0, 'L', 0, 1, $corp_x, $corp_y+($line*2));
	}
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(186, 0, $foreign['phone'], 0, 'L', 0, 1, $corp_x, $corp_y+($line*3));
	$pdf->MultiCell(186, 0, $foreign['fax'], 0, 'L', 0, 1, $corp_x, $corp_y+($line*4));
	$pdf->MultiCell(186, 0, $foreign['position'], 0, 'L', 0, 1, $corp_x, $corp_y+($line*5));
	$pdf->MultiCell(186, 0, $foreign['fullname'], 0, 'L', 0, 1, $corp_x, $corp_y+($line*6));
	$pdf->MultiCell(186, 0, $foreign['fullkana'], 0, 'L', 0, 1, $corp_x, $corp_y+($line*7));
	$pdf->MultiCell(166, 0, $url, 0, 'L', 0, 1, $corp_xx, $corp_y+($line*8));

	$brand_x = 36;
	$brand_y = 206;
	$pdf->MultiCell(0, 0, $foreign['brandname'], 0, 'L', 0, 1, $brand_x, $brand_y);
	$pdf->MultiCell(0, 0, $foreign['brandkana'], 0, 'L', 0, 1, $brand_x, $brand_y+($line*1));

	$booth_x = 10;
	$booth_y = 232;

	$line = 7.2;
	$y = 0;
	$ci->load->model('booth_model');
	$ci->load->model('member/members');
	$ci->load->helper('word');
	for ($i=1;$i<=9;$i++) {
		if (isset($foreign['q_boothcount'.$i]) && $foreign['q_boothcount'.$i] >= 1) {
			$row = $ci->booth_model->get_boothspace($foreign['q_booth'.$i]);
			$user = $ci->members->get_user_by_pcode($foreign['exhid'], $foreign['q_boothid'.$i]);
if ($user !== NULL) {
			$username = $user->username;
			$password = $user->password;
			$passyomi = '('.pronunciation_word($password).')';
} else {
			// ここに来る時は、アカウントが存在していないとき
			$username = 'ERROR';
			$password = 'ERROR';
			$passyomi = '(エラー)';
}
			$pdf->SetFont($defaultfont, '', 11);
			$pdf->MultiCell(32, 0, $row['spaceabbr'].'スペース', 0, 'C', 0, 1, $booth_x+1.4, $booth_y+($line*$y));
			$pdf->MultiCell(9.5, 0, $row['boothcount'], 0, 'C', 0, 1, $booth_x+34.5, $booth_y+($line*$y));
			$pdf->MultiCell(13, 0, $row['boothabbr'], 0, 'C', 0, 1, $booth_x+45.1, $booth_y+($line*$y));
			$pdf->MultiCell(15.4, 0, $foreign['q_boothcount'.$i], 0, 'C', 0, 1, $booth_x+59, $booth_y+($line*$y));

			$pdf->MultiCell(27.5, 0, $username, 0, 'C', 0, 1, $booth_x+76, $booth_y+($line*$y));

			$pdf->MultiCell(82.8, 0, $password, 0, 'L', 0, 1, $booth_x+104.5, $booth_y+($line*$y));
			$pdf->SetFont($defaultfont, '', 7.2);
			$pdf->MultiCell(82.8, 0, $passyomi, 0, 'L', 0, 1, $booth_x+104.5, $booth_y+($line*$y)+4.1);

			$y++;
		} 
	}

	// Page 2
	$pdf->AddPage();
	$pdf->setSourceFile(APPPATH . '/views/pdf/acceptance_2.pdf');
	$tpl = $pdf->importPage(1);
	$pdf->useTemplate($tpl);

	$manage_x = 30;
	$manage_y = 18;
	$line     = 4.7;

	$fulladdr = $foreign['m_prefecture'].$foreign['m_address1'].' '.$foreign['m_address2'];
	$fulladdr = '〒'.$foreign['m_zip'].'  '.$fulladdr;

	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $foreign['m_corpname'], 0, 'L', 0, 1, $manage_x, $manage_y);
	$pdf->MultiCell(0, 0, $foreign['m_corpkana'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*1));
	if (strlen($fulladdr) > 100) {
		$pdf->SetFont($defaultfont, '', 8.6);
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $manage_x, $manage_y+($line*2)+0.5);
	} else {
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $manage_x, $manage_y+($line*2));
	}
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $foreign['m_phone'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*3));
	$pdf->MultiCell(0, 0, $foreign['m_fax'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*4));
	$pdf->MultiCell(0, 0, $foreign['m_division'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*5));
	$pdf->MultiCell(0, 0, $foreign['m_position'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*6));
	$pdf->MultiCell(0, 0, $foreign['m_fullname'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*7));
	$pdf->MultiCell(0, 0, $foreign['m_fullkana'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*8));
	$pdf->MultiCell(0, 0, $foreign['m_mobile'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*9));
	$pdf->MultiCell(0, 0, $foreign['m_email'], 0, 'L', 0, 1, $manage_x, $manage_y+($line*10));

	$bill_x = 30;
	$bill_y = 78;
	$line   = 4.7;

	$fulladdr = '〒'.$foreign['b_zip'].'  '.$foreign['b_prefecture'].$foreign['b_address1'].' '.$foreign['b_address2'];

	$pdf->MultiCell(0, 0, $foreign['b_corpname'], 0, 'L', 0, 1, $bill_x, $bill_y);
	$pdf->MultiCell(0, 0, $foreign['b_corpkana'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*1));
	if (strlen($fulladdr) > 100) {
		$pdf->SetFont($defaultfont, '', 8.6);
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $bill_x, $bill_y+($line*2)+0.5);
	} else {
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $bill_x, $bill_y+($line*2));
	}
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $foreign['b_phone'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*3));
	$pdf->MultiCell(0, 0, $foreign['b_fax'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*4));
	$pdf->MultiCell(0, 0, $foreign['b_division'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*5));
	$pdf->MultiCell(0, 0, $foreign['b_position'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*6));
	$pdf->MultiCell(0, 0, $foreign['b_fullname'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*7));
	$pdf->MultiCell(0, 0, $foreign['b_fullkana'], 0, 'L', 0, 1, $bill_x, $bill_y+($line*8));

	$contact_x = 30;
	$contact_y = 129;
	$line      = 4.7;

	$fulladdr = '〒'.$foreign['c_zip'].'  '.$foreign['c_prefecture'].$foreign['c_address1'].' '.$foreign['c_address2'];

	$pdf->MultiCell(0, 0, $foreign['c_corpname'], 0, 'L', 0, 1, $contact_x, $contact_y);
	$pdf->MultiCell(0, 0, $foreign['c_corpkana'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*1));
	if (strlen($fulladdr) > 100) {
		$pdf->SetFont($defaultfont, '', 8.6);
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $contact_x, $contact_y+($line*2)+0.5);
	} else {
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $contact_x, $contact_y+($line*2));
	}
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $foreign['c_phone'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*3));
	$pdf->MultiCell(0, 0, $foreign['c_fax'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*4));
	$pdf->MultiCell(0, 0, $foreign['c_division'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*5));
	$pdf->MultiCell(0, 0, $foreign['c_position'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*6));
	$pdf->MultiCell(0, 0, $foreign['c_fullname'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*7));
	$pdf->MultiCell(0, 0, $foreign['c_fullkana'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*8));
	$pdf->MultiCell(0, 0, $foreign['c_mobile'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*9));
	$pdf->MultiCell(0, 0, $foreign['c_email'], 0, 'L', 0, 1, $contact_x, $contact_y+($line*10));

	$dist_x = 30;
	$dist_y = 189.5;
	$line   = 4.7;

	$fulladdr = '〒'.$foreign['d_zip'].'  '.$foreign['d_prefecture'].$foreign['d_address1'].' '.$foreign['d_address2'];

	$pdf->MultiCell(0, 0, $foreign['d_corpname'], 0, 'L', 0, 1, $dist_x, $dist_y);
	$pdf->MultiCell(0, 0, $foreign['d_corpkana'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*1));
	if (strlen($fulladdr) > 100) {
		$pdf->SetFont($defaultfont, '', 8.6);
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $dist_x, $dist_y+($line*2)+0.5);
	} else {
		$pdf->MultiCell(188, 0, $fulladdr, 0, 'L', 0, 1, $dist_x, $dist_y+($line*2));
	}
	$pdf->SetFont($defaultfont, '', 11);
	$pdf->MultiCell(0, 0, $foreign['d_phone'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*3));
	$pdf->MultiCell(0, 0, $foreign['d_fax'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*4));
	$pdf->MultiCell(0, 0, $foreign['d_division'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*5));
	$pdf->MultiCell(0, 0, $foreign['d_position'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*6));
	$pdf->MultiCell(0, 0, $foreign['d_fullname'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*7));
	$pdf->MultiCell(0, 0, $foreign['d_fullkana'], 0, 'L', 0, 1, $dist_x, $dist_y+($line*8));

	$query_x = 45;
	$query_y = 240.5;
	$line    = 4.7;

	$categorystr = '';
	foreach($category as $key=>$val) {
		if(isset($foreign['q_category_'.$key]) && $foreign['q_category_'.$key] != '') {
			$categorystr .= $val . ' ';
		}
	}
	$sectionstr = '';
	foreach($section as $key=>$val) {
		if(isset($foreign['q_section_'.$key]) && $foreign['q_section_'.$key] != '') {
			$sectionstr .= $val . ' ';
		}
	}

	$pdf->MultiCell(155, 0, $categorystr, 0, 'L', 0, 1, $query_x, $query_y);
	$pdf->MultiCell(155, 0, $sectionstr . '(' . $foreign['q_entrycars'] . '台)', 0, 'L', 0, 1, $query_x, $query_y+($line*1));
	$pdf->MultiCell(155, 0, $foreign['q_salesitem'], 0, 'L', 0, 1, $query_x, $query_y+($line*2));

	// ページはこれにて終了
	$pdf->lastPage();

	// コミットしてダウンロード
	header('Content-Type: application/pdf');
	header('Cache-Control: max-age=0');
	if ($preview != '') {
		$pdf->Output();
	} else {
		$filename = 'accept-' . $foreign['exhid'] . '-' . $foreign['brandname'] . '.pdf';
		$pdf->Output($filename, 'D');
	}
