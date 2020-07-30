<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service extends OP_Controller {

	protected $form_prefix = 'service';

	function index()
	{
		$this->slash_complete();
		$data = $this->setup_data();
//		$this->setup_form($data);
//		$this->setup_calc($data);
        $lists = array();
        $this->db->select('spaceid, spacename, spaceabbr, maxspaces, inventory');
        $this->db->order_by('seqno');
        $query = $this->db->get('v_spaces');
        if ($query->num_rows() > 0) {
            foreach($query->result_array() as $row) {
                $lists[$row['spaceid']] = $row;
                $lists[$row['spaceid']]['count'] = 0;
            }
        }
        $this->db->select('booths.spaceid, exhibitors.statusno');
        $this->db->select_sum('`boothcount` * `count`', 'counter', FALSE);
        $this->db->select_sum("IF(STRCMP(`route`, 'W'), `boothcount` * `count`, 0)", 'faxcount', FALSE);
        $this->db->select_sum("IF(STRCMP(`route`, 'W'), 0, `boothcount` * `count`)", 'webcount', FALSE);
        $this->db->from('booths');
        $this->db->join('exhibitor_booth', 'exhibitor_booth.boothid = booths.boothid');
        $this->db->join('exhibitors', 'exhibitors.exhid = exhibitor_booth.exhid AND exhibitors.expired = 0');
        $this->db->group_by(array('booths.spaceid', 'exhibitors.statusno'));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach($query->result_array() as $row) {
                $lists[$row['spaceid']]['count'] += $row['webcount'] + $row['faxcount'];
            }
        }
		$data['spaces'] = $lists;

		$this->parser->parse($this->form_prefix.'_'.__FUNCTION__, $data);
	}

	function setup_data()
	{
		$data = parent::setup_data();
        $this->config->load('service', FALSE, TRUE);
        $data['service'] = intval($this->config->item('service'));
        $data['pending'] = intval($this->config->item('pending'));
		return $data;
	}

	function start()
	{
		$this->write_file(0,1);
		$this->log_history('開始');
		redirect(uri_class_string());
	}

	function stop()
	{
		$this->write_file(0,0);
		$this->log_history('停止');
		redirect(uri_class_string());
	}

	function inventory()
	{
		$this->check_action();
		$foreign = $this->input->post();

		$this->db->set('inventory', $foreign['inventory']);
		$this->db->where('spaceid', $foreign['spaceid']);
		$this->db->update('v_spaces');

		switch($foreign['inventory']) {
		case 0:
			$this->log_history('受付開始', 'スペース('.$foreign['spaceid'].')');
			break;
		case 1:
			$this->log_history('キャンセル待ち', 'スペース('.$foreign['spaceid'].')');
			break;
		case 9:
			$this->log_history('受付停止', 'スペース('.$foreign['spaceid'].')');
			break;
		}
		
		redirect(uri_class_string());
	}
/*
	function pause()
	{
		$data = $this->setup_data();
		$this->write_file(1);
		if ($data['pending'] == 0) {
			$this->log_history('キャンセル待ち');
		} else {
			$this->log_history('通常運用');
		}
		redirect(uri_class_string());
	}
*/
	protected function write_file($mode, $value=1)
	{
		$data = $this->setup_data();
		if ($mode == 0) {
			$data['service'] = $value;
		}
		if ($mode == 1) {
			$data['pending'] = ($data['pending'] == 0 ? 1:0);
		}

		$text = $this->parser->parse('conf/service.php', $data, TRUE);
		$filename = APPPATH . 'config/service.php';
		file_put_contents($filename, $text, LOCK_EX);
	}

    /**
     * 操作履歴の追加
     *
     * @param $action
     * @param $uid
     */
    protected function log_history($action, $label = 'サービス')
    {
        $this->load->model('histories_model');
		$state = '';
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - Start by TTM
		// $this->histories_model->log($this->member->get_userid(), $action, $label.$state);
		$this->histories_model->log($this->member_lib->get_userid(), $action, $label.$state);
		// Upgrade CI3 - Avoid duplicate class name between Member Controller and Member Lib - End by TTM
    }
}

/* End of file service.php */
/* Location: ./application/controllers/(:any)/service.php */
