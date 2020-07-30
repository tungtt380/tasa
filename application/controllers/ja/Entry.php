<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Entry extends PubOP_Controller
{
    protected $form_prefix	 = 'entry';			// フォーム名
    protected $table_name	 = 'exhibitors';	// テーブル名
    protected $table_prefix  = 'S';				// テーブルの払出キー名(システムで一意)
    protected $table_expire  = TRUE;
    protected $foreign_keyid = 'exhid';			// テーブルの主キー名
    protected $foreign_token = 'token';			// ２重更新・削除防止のための項目
    protected $foreign_value = array(			// 入力チェック用に使用するカラムとパターン
        'corpname'		=> 'trim|required|xss_clean',
        'corpkana'		=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'zip'			=> 'trim|required|xss_clean|valid_zip',
        'prefecture'	=> 'trim|xss_clean',
        'address1'		=> 'trim|required|xss_clean',
        'address2'		=> 'trim|xss_clean',
        'phone'			=> 'trim|required|xss_clean|valid_phonejp',
        'fax'			=> 'trim|xss_clean|valid_phonejp',
        'url'			=> 'trim|xss_clean|valid_hostname',
        'position'		=> 'trim|required|xss_clean',
        'fullname'		=> 'trim|required|xss_clean',
        'fullkana'		=> 'trim|required|xss_clean|prep_kana|valid_kana',
        //
        'brandname'		=> 'trim|required|xss_clean',
        'brandkana'		=> 'trim|required|xss_clean|prep_kana|valid_kana',
        //
        'm_corpname'	=> 'trim|required|xss_clean',
        'm_corpkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'm_countrycode' => 'trim|required|xss_clean',
        'm_zip'			=> 'trim|required|xss_clean|valid_zip',
        'm_prefecture'	=> 'trim|xss_clean',
        'm_address1'	=> 'trim|required|xss_clean',
        'm_address2'	=> 'trim|xss_clean',
        'm_division'	=> 'trim|xss_clean',
        'm_position'	=> 'trim|xss_clean',
        'm_fullname'	=> 'trim|required|xss_clean|required|xss_clean',
        'm_fullkana'	=> 'trim|required|xss_clean|required|xss_clean|prep_kana|valid_kana',
        'm_phone'		=> 'trim|required|xss_clean|valid_phonejp',
        'm_fax'			=> 'trim|xss_clean|valid_phonejp',
        'm_mobile'		=> 'trim|required|xss_clean|valid_phonejp',
        'm_email'		=> 'trim|required|xss_clean|valid_email',
        //
        'b_corpname'	=> 'trim|required|xss_clean',
        'b_corpkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'b_countrycode' => 'trim|required|xss_clean',
        'b_zip'			=> 'trim|required|xss_clean|valid_zip',
        'b_prefecture'	=> 'trim|xss_clean',
        'b_address1'	=> 'trim|required|xss_clean',
        'b_address2'	=> 'trim|xss_clean',
        'b_phone'		=> 'trim|required|xss_clean|valid_phonejp',
        'b_fax'			=> 'trim|xss_clean|valid_phonejp',
        'b_division'	=> 'trim|xss_clean',
        'b_position'	=> 'trim|xss_clean',
        'b_fullname'	=> 'trim|required|xss_clean',
        'b_fullkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        //
        'c_corpname'	=> 'trim|required|xss_clean',
        'c_corpkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'c_countrycode' => 'trim|required|xss_clean',
        'c_zip'			=> 'trim|required|xss_clean|valid_zip',
        'c_prefecture'	=> 'trim|xss_clean',
        'c_address1'	=> 'trim|required|xss_clean',
        'c_address2'	=> 'trim|xss_clean',
        'c_division'	=> 'trim|xss_clean',
        'c_position'	=> 'trim|xss_clean',
        'c_fullname'	=> 'trim|required|xss_clean',
        'c_fullkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'c_phone'		=> 'trim|required|xss_clean|valid_phonejp',
        'c_fax'			=> 'trim|xss_clean|valid_phonejp',
        'c_mobile'		=> 'trim|required|xss_clean|valid_phonejp',
        'c_email'		=> 'trim|required|xss_clean|valid_email',
        //
        'd_corpname'	=> 'trim|required|xss_clean',
        'd_corpkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'd_countrycode' => 'trim|required|xss_clean',
        'd_zip'			=> 'trim|required|xss_clean|valid_zip',
        'd_prefecture'	=> 'trim|xss_clean',
        'd_address1'	=> 'trim|required|xss_clean',
        'd_address2'	=> 'trim|xss_clean',
        'd_division'	=> 'trim|xss_clean',
        'd_position'	=> 'trim|xss_clean',
        'd_fullname'	=> 'trim|required|xss_clean',
        'd_fullkana'	=> 'trim|required|xss_clean|prep_kana|valid_kana',
        'd_phone'		=> 'trim|required|xss_clean|valid_phonejp',
        'd_fax'			=> 'trim|xss_clean|valid_phonejp',
        //
        'q_entrycars'	=> 'trim|xss_clean|is_natural',
        'q_boothcount1' => 'trim|xss_clean|is_natural',
        'q_boothcount2' => 'trim|xss_clean|is_natural',
        'q_boothcount3' => 'trim|xss_clean|is_natural',
        'q_boothcount4' => 'trim|xss_clean|is_natural',
        'q_booth1'		=> 'trim|xss_clean|is_select_natural',
        'q_booth2'		=> 'trim|xss_clean|is_select_natural',
        'q_booth3'		=> 'trim|xss_clean|is_select_natural',
        'q_booth4'		=> 'trim|xss_clean|is_select_natural',
        'remark'	    => 'trim|xss_clean',
        //
        'promotion'		=> 'trim|xss_clean|alpha_dash',
    );

    protected $auth_password = 'SMff339c';

    function __construct() {
        parent::__construct();
        $this->load->model('exhibitors_model');
    }

    public function index()
    {
        redirect('/entry/regist');
    }

    protected function setup_form(&$data)
    {
        $this->load->helper('form');
        $this->load->model('country_model');
        $data['countrycode'] = $this->country_model->get_dropdown();
        $this->load->model('prefecture_model');
        $data['prefecture'] = $this->prefecture_model->get_dropdown(TRUE);
        $this->load->model('category_model');
        $data['category'] = $this->category_model->get_dropdown();
        $this->load->model('section_model');
        $data['section'] = $this->section_model->get_dropdown();
        $this->load->model('booth_model');
        $data['booth'] = $this->booth_model->get_dropdown();
        $data['boothgroup'] = $this->booth_model->get_dropdown(TRUE);
        $data['boothgroup_a'] = $this->booth_model->get_dropdown(TRUE,FALSE,'A');
        $data['boothgroup_b'] = $this->booth_model->get_dropdown(TRUE,FALSE,array('B','E','F'));
        $data['boothgroup_c'] = $this->booth_model->get_dropdown(TRUE,FALSE,array('C','D'));
        $data['boothgroup_s'] = $this->booth_model->get_dropdown(TRUE,FALSE,array('S','S300','S350','S400','S450','S500','S600','S700','S720','S750','S800','S850','S900','S1000','S1050','S1200'));

        $lists = $waiting = $soldout = array();
        $this->db->select('spaceid, spacename, spaceabbr, maxspaces, inventory');
        $this->db->order_by('seqno');
        $query = $this->db->get('v_spaces');
        if ($query->num_rows() > 0) {
            foreach($query->result_array() as $row) {
                if (strlen($row['spaceabbr']) == 1) {
                    $lists[$row['spaceid']] = $row;
                    if ($row['inventory'] == 1) {
                        $waiting[$row['spaceid']] = $row['spaceabbr'] . 'スペース';
                    }
                    if ($row['inventory'] == 9) {
                        $soldout[$row['spaceid']] = $row['spaceabbr'] . 'スペース';
                    }
                }
            }
        }
        $data['spaces'] = $lists;
        $data['waiting'] = implode('、', $waiting);
        $data['soldout'] = implode('、', $soldout);
    }

    protected function check_logic(&$data)
    {
        $this->load->model('booth_model');
        $foreign = $data['foreign'];
        $result = TRUE;
        $spacecounts = array('A'=>0,'B'=>0,'C'=>0,'D'=> 0,'E'=>0,'F'=>0,'S'=>0, 'S300'=> 0,'S350'=> 0,'S400'=> 0,'S450'=> 0,'S500'=> 0,'S600'=> 0,'S700'=> 0,'S720'=> 0,'S750'=> 0,'S800'=> 0,'S850'=> 0,'S900'=> 0,'S1000'=> 0,'S1050'=> 0,'S1200'=> 0);
        $spacelimits = array('A'=>5,'B'=>2,'C'=>5,'D'=>30,'E'=>2,'F'=>2,'S'=>1, 'S300'=> 1,'S350'=> 1,'S400'=> 1,'S450'=> 1,'S500'=> 1,'S600'=> 1,'S700'=> 1,'S720'=> 1,'S750'=> 1,'S800'=> 1,'S850'=> 1,'S900'=> 1,'S1000'=> 1,'S1050'=> 1,'S1200'=> 1);
        $othercount = 0;
        $contcount = 0;
        $waitcount = 0;

        for ($i=1;$i<=9;$i++) {
//			if (isset($foreign['q_boothcount'.$i]) && $foreign['q_boothcount'.$i] == 1) {
            if (isset($foreign['q_booth'.$i]) && $foreign['q_booth'.$i] != '') {
                $row = $this->booth_model->get_boothspace($data['foreign']['q_booth'.$i]);
                if (($row['spaceabbr'] != 'A' && $spacecounts['A'] > 0) ||
                    ($row['spaceabbr'] == 'A' && $othercount > 0)) {
                    $data['message']['__all'] = '<br /><span class="red">→Aスペースは他のスペースと同時に申込はできません。</span>';
                    $result = FALSE;
                    break;
                }
                $spacecounts[$row['spaceabbr']] += $row['boothcount'];
                if ($spacecounts[$row['spaceabbr']] > $spacelimits[$row['spaceabbr']]) {
                    $data['message']['__all'] = '<br /><span class="red">小間の申込上限数を超えています。</span>';
                    $result = FALSE;
                    break;
                }
                $othercount += ($row['spaceabbr'] == 'A')? 0:$row['boothcount'];
                if ($row['inventory'] == 0) {
                    $contcount += 1;
                } else {
                    $waitcount += 1;
                }
                if ($contcount > 0 && $waitcount > 0) {
                    $data['message']['__all'] = '<br /><span class="red">通常受付のスペースとキャンセル待ちのスペースの同時申込みはできません。</span>';
                    $result = FALSE;
                    break;
                }
            }
        }
        if ($result === FALSE) {
            log_message('notice', $data['message']['__all']);
        }
        return $result;
    }

    protected function check_limit_action()
    {
        $this->config->load('service', TRUE, TRUE);
        $service = $this->config->item('service', 'service');
        $pending = $this->config->item('service', 'pending');
        log_message('notice', "Service is [" . $service . "], Pending is [" . $pending . "]");
        return ($service == 0 ? TRUE:FALSE);
    }

    function regist()
    {
        if ($this->check_limit_action()) {
            $this->parser->parse('entry_limit');
        } else {
            if ($this->check_auth()) {
                parent::regist();
            } else {
                $this->parser->parse('entry_limit');
            }
        }
    }

    function regist_in()
    {
        if ($this->check_limit_action()) {
            $this->parser->parse('entry_limit');
        } else {
            if ($this->check_auth()) {
                parent::regist_in();
            } else {
                $this->parser->parse('entry_limit');
            }
        }
    }

    function regist_confirm()
    {
        if ($this->check_limit_action()) {
            $this->parser->parse('entry_limit');
        } else {
            if ($this->check_auth()) {
                parent::regist_confirm();
            } else {
                $this->parser->parse('entry_limit');
            }
        }
    }

    function regist_confirm_in()
    {
        if ($this->check_limit_action()) {
            $this->parser->parse('entry_limit');
        } else {
            if ($this->check_auth()) {
                parent::regist_confirm_in();
            } else {
                $this->parser->parse('entry_limit');
            }
        }
    }
    protected function check_auth ()
    {
        /* 「特別予約中」はベーシック認証を有効にする （2017-10 対応）
        　　通常時は false に
        　　../entry.php にも同じ記述有
         */
        $basic_auth_enable = false;

        if ($basic_auth_enable) {
            $check_flg = false;
            if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == 'tasa' && $_SERVER['PHP_AUTH_PW'] == $this->auth_password) {
                $check_flg = true;
            }
            if (!$check_flg) {
                header('WWW-Authenticate: Basic realm="Private Page"');
                set_status_header("401");
            }
            return $check_flg;
        } else {
            return true;
        }
    }

    protected function create_record(&$foreign)
    {
        if (FALSE) {
            $this->config->load('service', FALSE, TRUE);
            $pending = intval($this->config->item('pending'));
        } else {
            $this->load->model('booth_model');
            $pending = 0;
            for ($i=1; $i<=5; $i++) {
//				if (isset($foreign['q_boothcount'.$i]) && $foreign['q_boothcount'.$i] > 0) {
                if (isset($foreign['q_booth'.$i]) && $foreign['q_booth'.$i] != '') {
                    $row = $this->booth_model->get_boothspace($foreign['q_booth'.$i]);
                    if (isset($row['inventory']) && $row['inventory'] != 0) {
                        log_message('notice', 'w/waiting list.('.$row['spaceabbr'].')');
                        $pending = 1;
                    }
                }
            }
        }
        $statusno = ($pending == 1) ? '202':'200';

        return $this->exhibitors_model->create($foreign, $statusno, 'W');
    }

    protected function get_record(&$data, $uid)
    {
        $data['foreign'] = $this->exhibitors_model->read($uid);
        $this->load->model('booth_model');
        $data['booth'] = $this->booth_model->get_dropdown(FALSE, TRUE);
        $data['boothgroup'] = $this->booth_model->get_dropdown(TRUE, TRUE);
    }

    protected function after_regist(&$data)
    {
        // 更新日はデータベース日付なので、もう一度取り直す.
        $uid = $data['foreign'][$this->foreign_keyid];
        $this->get_record($data, $uid);

        $this->load->library('email');
        $mailto = array($data['foreign']['c_email']);
        if ($_SERVER['HTTP_HOST'] == 'cus.tokyoautosalon.jp') {
            $mailfrom = 'info@tokyoautosalon.jp';
            $mailto02 = 'info@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON';
        } else {
//            $mailfrom = 'ishii@mono-style.co.jp';
//            $mailto02 = 'ishii@mono-style.co.jp';
            $mailfrom = 'info@tokyoautosalon.jp';
            $mailto02 = 'info@tokyoautosalon.jp';
            $namefrom = 'TOKYO AUTO SALON(TEST MAIL)';
        }

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
        if (isset($data['foreign']['statusno']) && $data['foreign']['statusno'] == 202) {
            $text = $this->parser->parse('mail/entry_regist_url_waiting.txt', $data, TRUE);
        } else {
            $text = $this->parser->parse('mail/entry_regist_url.txt', $data, TRUE);
        }
        if (strpos($text, "\n") !== FALSE) {
            list($subject, $message) = explode("\n", $text, 2);
        } else {
            $subject = 'TOKYO AUTO SALON 2020【出展申込み確認メール】（控）';
            $message = $text;
        }

        $this->email->from($mailfrom, mb_convert_encoding($namefrom,'ISO-2022-JP','UTF-8'));
        $this->email->to($mailto);
        $this->email->bcc($mailto02);
        $this->email->reply_to($mailfrom);
        $this->email->subject(mb_convert_encoding($subject,'ISO-2022-JP','UTF-8'));
        $this->email->message(mb_convert_encoding($message,'ISO-2022-JP','UTF-8'));
        $this->email->send();
    }
}

// vim:ts=4
/* End of file entry.php */
/* Location: ./application/controllers/entry.php */
