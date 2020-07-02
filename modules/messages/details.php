<?php defined('BASEPATH') or exit('No direct script access allowed');
class Module_Messages extends Module {

	public $version = '1.0';
	public function info()
	{
		$info = array(
			'name' => array(
				'en' => 'Messages'
			),
			'description' => array(
				'en' => 'This is a PyroCMS messages module'
			),
			'frontend' => TRUE,
			'backend' => TRUE,
			'menu' => 'content',
			'roles'    => array(
					),
			'sections' => array(
				'messages' =>
					array(
						'name' 	=> 'messages:create', // These are translated from our language file
						'uri' 	=> 'admin/messages',
							'shortcuts' => array(
								'create' =>
									array(
									'name' 	=> 'messages:create',
									'uri' 	=> 'admin/messages/create',
									'class' => 'add'
									),
									array(
										'name' 	=> 'messages:assign_user',
										'uri' 	=> 'admin/messages/host_users/create',
										'class' => 'add'
									),
								)
						),
					)
				
		);

		return $info;
	}


	public function install()
	{

		$this->load->driver('Streams');
		$this->load->language('messages/messages');
		$this->streams->utilities->remove_namespace('messages');
		
		//Create our stream tables
		if ( ! $messages_stream_id = $this->streams->streams->add_stream('lang:messages:messages', 'messages', 'messages', 'messages_', null)) return false;
		return TRUE;
	}

	public function uninstall()
	{
		$this->load->driver('Streams');
		$this->streams->utilities->remove_namespace('stores');
		$this->db->delete('settings', array('module'=>'stores'));
		
		$this->streams->utilities->remove_namespace('messages');
		
		$this->db->delete('settings', array('module'=>'messages'));
		// TODO: Put a check in to see if something failed, otherwise it worked
		return TRUE;
	}

	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}

	public function help()
	{
		// Return a string containing help info
		return 'No documentation has been written for this module yet.';
	}
}
/* End of file details.php */
