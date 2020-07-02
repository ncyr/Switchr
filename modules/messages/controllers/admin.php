<?php
class Admin extends Admin_Controller {
	protected $section = 'messages';
	function __construct()
	{
		parent::__construct();
		$this->lang->load('messages');

	}

	function index()
	{
        $extra['title'] = 'messages:messages';
     
        $extra['buttons'] = array(
            array(
            'label'     => lang('global:edit'),
            'url'       => 'admin/messages/edit/-entry_id-'
            ),
            array(
            'label'     => lang('global:delete'),
            'url'       => 'admin/messages/delete/-entry_id-',
            'confirm'   => true,
            )
        );
        $this->streams->cp->entries_table('messages', 'messages', 3, 'admin/messages/index', true, $extra);
    }
}
