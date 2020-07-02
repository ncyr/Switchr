<?php

class Admin extends Admin_Controller
{
    protected $section = 'logging';

    public function __construct()
    {
        parent::__construct();
        $this->lang->load('logging');
        //$this->load->driver('Streams');
        $this->load->helper('security');
        //$this->load->library('Logging');
    }

    public function index()
    {
        $extra['title'] = 'lang:logging:logs';

        $extra['buttons'] = array(
            array(
                'label'   => lang('global:delete'),
                'url'     => 'admin/logging/delete/-entry_id-',
                'confirm' => true,
            )
        );

        $this->streams->cp->entries_table('logging', 'logging', 10, 'admin/logs/index', true, $extra);
    }
}
