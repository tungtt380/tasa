<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E16reservation extends OP_Controller {

    protected $form_prefix   = 'e16reservation';

	// 出展者から見た場合はdetailを表示
	public function index()
	{
		$data = $this->setup_data();
        $this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}
}
