<?php
class Servers_m extends MY_Model {

	public function __construct()
	{		
		parent::__construct();
		
		/**
		 * If the hosts module's table was named "hostss"
		 * then MY_Model would find it automatically. Since
		 * I named it "hosts" then we just set the name here.
		 */
		$this->_table = 'hosts';
		$this->load->driver('Streams');
	}
	
	//create a new item
	public function create($input)
	{
		$to_insert = array(
			'name' => $input['name'],
			'slug' => $this->_check_slug($input['slug'])
		);
		return $this->db->insert('hosts', $to_insert);
	}
	//make sure the slug is valid
	public function _check_slug($slug)
	{
		$slug = strtolower($slug);
		$slug = preg_replace('/\s+/', '-', $slug);
		return $slug;
	}
	public function get_host($id)
	{
		$host = $this->streams->entries->get_entry($id, 'hosts', 'hosts');
		return $host;
	}
	public function get_server($server_id)
	{
		$server = $this->streams->entries->get_entry($server_id, 'servers', 'servers');
		return $server;
	}
}