<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Local_port_used extends Public_Controller
{

	protected $section = 'local_port_used';

	public function __construct()
	{
		parent::__construct();
		
		$this->load->language('ports');

		$this->load->driver('Streams');
	}

	public function index()
	{
			$this->template
				->title('Your Ports')
				->build('local_port_used_index');
	}

	public function create()
	{
		$this->template
				->title('Add Port')
				->build('local_port_used_create');
	}
	
	public function edit($id)
	{
		$this->template->title(lang('global:edit'));

		$this->streams->cp->entry_form('port_used', 'ports', 'edit', $id, true, array(
			'return'			=> 'admin/api/keys',
			// 'success_message'	=> lang('faq:submit_success'),
			// 'failure_message'	=> lang('faq:submit_failure'),
			'title'				=> lang('global:add')
		));
	}
	
	public function switchPort($status, $ip, $port, $protocol)
	{
		if($status)
		{
			//Switch this port on
			$tables = shell_exec('sudo iptables -A INPUT -p '. $protocol .' -s '. $ip .'/24 --dport '. $port .' -j DROP');
		}
		elseif($status = 0){
			//Switch this port off
			$tables = shell_exec('sudo iptables -D INPUT -p '. $protocol .' -s '. $ip .'/24 --dport '. $port .' -j DROP');
		}
		redirect('switchr', 'refresh');
	}
	public function checkPortExists($port)
	{
		if($port)
		{
			$response = shell_exec('sudo iptables -L | grep ' . $port);
			if($response != '')
			{
				return true;
			}
			else{
				return false;
			}
		}
	}
}

/* End of file switchr.php */