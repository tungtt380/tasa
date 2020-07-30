<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exhibitor extends RecOP_Controller {

    protected $form_prefix   = 'exhibitor';     // フォーム名
    protected $table_name    = 'exhibitors';    // テーブル名
    protected $table_prefix  = 'S';             // テーブルの払出キー名(システムで一意)
    protected $table_expire  = TRUE;            // テーブルが論理削除の場合 TRUE
    protected $foreign_keyid = 'exhid';         // テーブルの主キー名
    protected $foreign_token = 'token';         // ２重更新・削除防止のための項目

    function __construct()
	{
        parent::__construct();
        $this->load->model('exhibitors_model');
    }

	public function index()
	{
	}

    //【詳細画面】
    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
    // public function detail()
    public function detail($uid = '')
    // Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
    {
		if (date('Y-m-d H:i:s') <= '2018-11-05 10:00:00'){
			redirect('/ex/preparation');
        }
        // Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
        // $uid = $this->member->get_exhid();
        $uid = $this->member_lib->get_exhid();
        // Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
        $data = $this->setup_data();
        $this->setup_form($data);
        $this->get_record($data, $uid);
        if (!isset($data['foreign'][$this->foreign_keyid])) {
            redirect(uri_redirect_string() . '/', 'location', 302);
        }
        $this->parser->parse('ex/'.$this->form_prefix.'_'.__FUNCTION__, $data);
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
        $data['booth'] = $this->booth_model->get_dropdown(FALSE,TRUE);
        $data['boothgroup'] = $this->booth_model->get_dropdown(TRUE,TRUE);
        // Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
        // if ($this->member->get_exhboothid()) {
        //     $data['exhboothid'] = $this->member->get_exhboothid();
        // }
        if ($this->member_lib->get_exhboothid()) {
            $data['exhboothid'] = $this->member_lib->get_exhboothid();
        }
        // Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
    }

    function get_record(&$data, $uid) {
        $data['foreign'] = $this->exhibitors_model->read($uid);
    }
}
