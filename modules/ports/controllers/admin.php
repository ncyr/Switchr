<?php
class Admin extends Admin_Controller {
	protected $section = 'port_setup';
	function __construct()
	{
		parent::__construct();
		//$this->load->model('Sw_model', 'Sw');

		//$this->load->model('license/License_m', 'License');
		$this->lang->load('ports');
		$this->load->helper('security');

	}

	function index()
	{

	$extra['title'] = 'sw:ports';
 
	$extra['buttons'] = array(
	    array(
		'label'     => lang('global:edit'),
		'url'       => 'admin/ports/edit/-entry_id-'
	    ),
	    array(
		'label'     => lang('global:delete'),
		'url'       => 'admin/ports/delete/-entry_id-',
		'confirm'   => true,
	    )
	);
	 
	$this->streams->cp->entries_table('user_port', 'ports', 3, 'admin/ports/index', true, $extra);
	}
	function create(){
		
		$extra = array(
		    'return' => 'admin/ports',
		    'success_message' => lang('ports:submit_success'),
		    'failure_message' => lang('ports:submit_failure'),
		    'title' => 'lang:ports:new',
		 );
	
		$this->streams->cp->entry_form('user_port', 'ports', 'new', null, true, $extra);
	}
	public function edit($id)
	{
		$extra = array(
		    'return' => 'admin/ports',
		    'success_message' => lang('projects:submit_success'),
		    'failure_message' => lang('projects:submit_failure'),
		    'title' => 'lang:ports:edit_sw',
		);

		$this->streams->cp->entry_form('user_port', 'ports', 'edit', $id, true, $extra);
	}
	
	public function delete($id)
	{
		$this->streams->entries->delete_entry($id, 'user_port', 'ports');
		redirect('admin/ports', 'refresh');
	}    
}
