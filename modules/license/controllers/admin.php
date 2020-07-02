<?php
class Admin extends Admin_Controller
{
    protected $section = 'license_serials';
    public function __construct()
    {
        parent::__construct();
        //$this->load->model('license/License_m', 'License');
        ////$this->lang->load('switchr');
        $this->lang->load('license');
        $this->load->helper('security');
    }

    public function index()
    {
        $extra['title'] = lang('license:license');

        $extra['buttons'] = array(
            array(
            'label'     => lang('global:edit'),
            'url'       => 'admin/license/edit/-entry_id-'
            ),
            array(
            'label'     => lang('global:delete'),
            'url'       => 'admin/license/delete/-entry_id-',
            'confirm'   => true,
            )
        );

        $this->streams->cp->entries_table('license_serials', 'license', 3, 'admin/license/index', true, $extra);
    }
    public function create()
    {
        $extra = array(
            'return' => 'admin/license',
            'success_message' => lang('license:submit_success'),
            'failure_message' => lang('license:submit_failure'),
            'title' => lang('global:new'),
         );

        $this->streams->cp->entry_form('license_serials', 'license', 'new', null, true, $extra);
    }
    public function edit($id)
    {
        $extra = array(
            'return' => 'admin/switchr',
            'success_message' => lang('projects:submit_success'),
            'failure_message' => lang('projects:submit_failure'),
            'title' => lang('global:edit'),
        );

        $this->streams->cp->entry_form('license_serials', 'license', 'edit', $id, true, $extra);
    }

    public function delete($id)
    {
        $this->streams->entries->delete_entry($id, 'license_serials', 'license');
        redirect('admin/license', 'refresh');
    }
}
