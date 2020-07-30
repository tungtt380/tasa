<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Broadcast extends RecOP_Controller {

    protected $form_prefix   = 'ad/broadcast';			// フォーム名
    protected $table_name    = 'v_members_exhibitor';	// テーブル名

    function __construct()
	{
        parent::__construct();
    }

	public function index()
	{
		$data = $this->setup_data();
		$this->setup_form($data);
		$data['lists'] = array();

		$sent = 0;
		$this->db->from($this->table_name);
		$this->db->where('activate = 0');
		$query = $this->db->get();
		$result = $query->result_array();
		foreach($result as $record) {
			if ($record['activate'] != 0) {
				if ($sent < 499) {
					$sent++;
					$record['mark'] = 'S';
					$data['lists'][] = $record;
				} else {
					$record['mark'] = 'o';
					$data['lists'][] = $record;
				}
			} else {
				$record['mark'] = 'x';
				$data['lists'][] = $record;
			}
		}
		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	public function broadmail()
	{
		$sent = 0;
		$this->db->from($this->table_name);
		$this->db->where('activate = 0');
		$query = $this->db->get();
		$result = $query->result_array();

//die(var_export($result));
		set_time_limit(300);
		foreach($result as $record) {
			if ($record['activate'] == 0) {
				if ($sent < 499) {
					$this->mail($record);
					$this->db->where('memberid', $record['memberid']);
					$this->db->update('members', array('activate'=>1));
					log_message('notice', 'ACTIVATE '.$record['username'] . "/" . $record['corpname'] . "/" . $record['c_email']);
					$sent++;
				} else {
					log_message('notice', 'PENDING ACTIVATE '.$record['username'] . "/" . $record['corpname'] . "/" . $record['c_email']);
				}
			} else {
				log_message('notice', 'NO ACTIVATE '.$record['username'] . "/" . $record['corpname'] . "/" . $record['c_email']);
			}
		}
		redirect(uri_class_string().'/');
	}

	protected function mail($record, $test=TRUE)
	{
        $this->load->library('email');

        $data = array();
		$data['foreign'] = $record;
        $this->load->library('email');
        if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
            $mailfrom = 'info@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON';
        } else {
            $mailfrom = 'miko@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON';
        }
        $mailto = $data['foreign']['c_email'];

        $text = $this->parser->parse('mail/sorry.txt', $data, TRUE);
        if (strpos($text, "\n") !== FALSE) {
            list($subject, $message) = explode("\n", $text, 2);
        } else {
            $subject = "［重要］公式WEBサイトからの諸手続き用ログインIDの発行のお知らせ";
            $message = $text;
        }

        $subject = trim($subject, "\r\n");
        $message = trim($message, "\r\n") . "\r\n";
        $this->email->from('info@tokyoautosalon.jp',mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
		$this->email->to($mailto);
		$this->email->bcc(array('info@tokyoautosalon.jp','miko@tokyoautosalon.jp'));
        $this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
        $this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
        $this->email->send();
	}
}
