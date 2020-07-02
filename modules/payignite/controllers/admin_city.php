<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * POSignite Cart Module
 *
 * @author 		
 * @website		
 * @package 	PyroCMS
 * @subpackage 	
 */

class Admin_city extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'city';

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
		$extra['title'] = 'lang:payignite:city';
        
		$extra['buttons'] = array(
		    array(
			'label' => lang('global:edit'),
			'url' => 'admin/payignite/city/edit/-entry_id-'
		    ),
		    array(
			'label' => lang('global:delete'),
			'url' => 'admin/payignite/city/delete/-entry_id-',
			'confirm' => true
		    )
		);
	
		$this->streams->cp->entries_table('city', 'payignite', 10, 'admin/payignite/city/index', true, $extra);
	}
	public function create()
	{
		$extra = array(
		    'return' => 'admin/payignite/city',
		    'success_message' => lang('dropship:submit_success'),
		    'failure_message' => lang('dropship:submit_failure'),
		    'title' => 'lang:payignite:create',
		 );

		$this->streams->cp->entry_form('city', 'payignite', 'new', null, true, $extra);
	}

	public function edit($id)
	{
		$extra = array(
		    'return' => 'admin/payignite',
		    'success_message' => lang('payignite:submit_success'),
		    'failure_message' => lang('payignite:submit_failure'),
		    'title' => 'lang:payignite:edit',
		);

		$this->streams->cp->entry_form('city', 'payignite', 'edit', $id, true, $extra);
	}
	
	public function delete($id)
	{
		$this->streams->entries->delete_entry($id, 'city', 'payignite');
		redirect('admin/payignite/city', 'refresh');
	}

}