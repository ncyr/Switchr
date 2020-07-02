<?php
class Admin_Local_port_used extends Admin_Controller {
	protected $section = 'local_port_used';
	function __construct()
	{
		parent::__construct();
		//$this->load->model('Port_model', 'Port');
		//$this->load->model('license/License_m', 'License');
		$this->lang->load('ports');
		$this->load->helper('security');
	}

	function index()
	{
		$extra['title'] = 'port:local_port_used';
	 
		$extra['buttons'] = array(
		    array(
			'label'     => lang('global:edit'),
			'url'       => 'admin/ports/local_port_used/edit/-entry_id-'
		    ),
		    array(
			'label'     => lang('global:delete'),
			'url'       => 'admin/ports/local_port_used/delete/-entry_id-',
			'confirm'   => true,
		    )
		);
		 
		$this->streams->cp->entries_table('local_port_used', 'ports', 3, 'admin/ports/local_port_used/index', true, $extra);
	}
	function create(){
		
	    $extra = array(
		'return' => 'admin/ports/local_port_used/index',
		'success_message' => lang('global:submit_success'),
		'failure_message' => lang('global:submit_failure'),
		'title' => 'lang:ports:new',
	     );
    
	    $this->streams->cp->entry_form('local_port_used', 'ports', 'new', null, true, $extra);
	}
	
	public function edit($id)
	{
		$extra = array(
		    'return' => 'admin/ports/local_port_used/index',
		    'success_message' => lang('projects:submit_success'),
		    'failure_message' => lang('projects:submit_failure'),
		    'title' => 'lang:ports:edit_port',
		);

		$this->streams->cp->entry_form('local_port_used', 'ports', 'edit', $id, true, $extra);
	}

	function delete($id)
	{
		if($this->uri->segment(4) == $id)
		{
			if($this->Port->delete_port($id)){
                $this->data['message'] = 'Port Deleted';
            }
            else{$this->data['message'] = 'There was a problem removing the Port';}
		}
		else
		{
			$this->data['message'] = 'The port being removed did not match the one requested';
		}
        $this->template->build('/admin/ports', $this->data);
	}
}
