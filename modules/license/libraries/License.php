<?php
Class License{
	
	function __construct($url = '')
	{
		$this->_ci = & get_instance();
		$this->_ci->load->model('hosts/hosts_m');
		$this->_ci->load->model('servers/servers_m');
	}

}