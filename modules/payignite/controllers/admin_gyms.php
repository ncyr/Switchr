<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * POSignite Cart Module
 *
 * @author 		
 * @website		
 * @package 	PyroCMS
 * @subpackage 	
 */

class Admin_gyms extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'gyms';

    protected $data;

    public function __construct()
    {
        parent::__construct();
        $this->lang->load('payignite');
        $this->load->driver('Streams');
    }
	/**
	 * List all items
	 */
	public function index()
	{
		$extra['title'] = 'lang:payignite:gyms';
        
		$extra['buttons'] = array(
		    array(
			'label' => lang('global:edit'),
			'url' => 'admin/payignite/gyms/edit/-entry_id-'
		    ),
		    array(
			'label' => lang('global:delete'),
			'url' => 'admin/payignite/gyms/delete/-entry_id-',
			'confirm' => true
		    )
		);
	
		$this->streams->cp->entries_table('gyms', 'payignite', 10, 'admin/payignite/gyms/index', true, $extra);
	}
	public function create()
	{
		$extra = array(
		    'return' => 'admin/payignite/gyms',
		    'success_message' => lang('dropship:submit_success'),
		    'failure_message' => lang('dropship:submit_failure'),
		    'title' => 'lang:payignite:create_gym',
		 );

		$this->streams->cp->entry_form('gyms', 'payignite', 'new', null, true, $extra);
	}

	public function edit($id)
	{
		$extra = array(
		    'return' => 'admin/payignite',
		    'success_message' => lang('payignite:submit_success'),
		    'failure_message' => lang('payignite:submit_failure'),
		    'title' => 'lang:payignite:edit_gym',
		);

		$this->streams->cp->entry_form('gyms', 'payignite', 'edit', $id, true, $extra);
	}
	
	public function delete($id)
	{
		$this->streams->entries->delete_entry($id, 'gyms', 'payignite');
		redirect('admin/payignite/gyms', 'refresh');
	}

}