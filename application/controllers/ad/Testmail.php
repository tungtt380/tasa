<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TestMail extends RecOP_Controller {

	public $foreign_keyid = 'exhid';

    function __construct() {
        parent::__construct();
        $this->load->model('exhibitors_model');
    }

	function mail()
	{
		$this->load->library('email');
		$subject  = "【登録完了のお知らせ】ご登録ありがとうございました。";
		$message  = "ご登録ありがとうございます。";
		$fromname = "東京オートサロン事務局";

		$data = array();
		$data['foreign'] = $this->exhibitors_model->read('S0000006');
        $this->load->library('email');
        $mailfrom = "TOKYO AUTOSALON";
        $mailto = array($data['foreign']['m_email'], $data['foreign']['c_email']);

		// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
		// $this->load->library('encrypt');
		// $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
		// $code = $this->encrypt->encode($data['foreign'][$this->foreign_keyid]);
		$this->load->library('encryption');
		$this->encryption->initialize(
			array(
					'cipher' => 'blowfish',
					'mode' => 'cbc'
			)
		);
		$code = $this->encryption->encrypt($data['foreign'][$this->foreign_keyid]);
		// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
		$data['cipher'] = str_replace(array('+','/','='), array('_','-',''), $code);
		$text = $this->parser->parse('mail/entry_regist_url.txt', $data, TRUE);
		if (strpos($text, "\n") !== FALSE) {
			list($subject, $message) = explode("\n", $text, 2);
		} else {
			$subject = "TOKYO AUTO SALON 2016【出展申込み確認メール】（控）";
			$message = $text;
		}

		$subject = trim($subject, "\r\n");
		$message = trim($message, "\r\n") . "\r\n";

		$this->email->from('info@tokyoautosalon.jp',mb_convert_encoding($fromname,'ISO-2022-JP','UTF-8'));
		$this->email->to('miko@cafelounge.net');
//      $this->email->bcc('info@tokyoautosalon.jp');
//      $this->email->reply_to('info@tokyoautosalon.jp', $mailfrom);
		$this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
		$this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
		$this->email->send();
	}

    function detailx()
    {
		// Upgrade CI3 - Replace encrypt library with encryption library - Start by TTM
        // $this->load->library('encrypt');
        // $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
		// $code =  $this->encrypt->encode('S0000006');
		$this->load->library('encryption');
		$this->encryption->initialize(
			array(
					'cipher' => 'blowfish',
					'mode' => 'cbc'
			)
		);
		$code =  $this->encryption->encrypt('S0000006');
		// Upgrade CI3 - Replace encrypt library with encryption library - End by TTM
        $code = str_replace(array('+','/'), array('_','-'), $code);
        echo '<br>' . $code;
        exit;
    }
}
