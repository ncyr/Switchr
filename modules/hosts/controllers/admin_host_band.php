<?php
class Admin_Host_Band extends Admin_Controller
{
    protected $section = 'host_band';
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $extra['title'] = lang('hosts:host_band');

        $extra['buttons'] = array(
            array(
            'label'     => lang('global:edit'),
            'url'       => 'admin/hosts/host_band/edit/-entry_id-'
            ),
            array(
            'label'     => lang('global:delete'),
            'url'       => 'admin/hosts/host_band/delete/-entry_id-',
            'confirm'   => true,
            )
        );

        $this->streams->cp->entries_table('host_band', 'hosts', 3, 'admin/hosts/host_band/index', true, $extra);
    }
    public function create()
    {
        $extra = array(
        'return' => 'admin/hosts/host_users/index',
        'success_message' => lang('hosts:submit_success'),
        'failure_message' => lang('hosts:submit_failure'),
        'title' => lang('hosts:new'),
         );

        $this->streams->cp->entry_form('host_users', 'hosts', 'new', null, true, $extra);
    }

    public function edit($id)
    {
        $extra = array(
            'return' => 'admin/hosts/host_users/index',
            'success_message' => lang('projects:submit_success'),
            'failure_message' => lang('projects:submit_failure'),
            'title' => lang('hosts:edit_host'),
        );

        $this->streams->cp->entry_form('host_users', 'hosts', 'edit', $id, true, $extra);
    }

    public function delete_host($id)
    {
        if ($this->uri->segment(4) == $id) {
            if ($this->Store->delete_host($id)) {
                $this->data['message'] = 'Store Deleted';
            } else {
                $this->data['message'] = 'There was a problem removing the host';
            }
        } else {
            $this->data['message'] = 'The host being removed did not match the one requested';
        }
        $this->template->build('/admin/hosts', $this->data);
    }
    public function remove_host_user()
    {
        $this->data['message'] = '';
        if ($this->input->post()) {
            $host_data = array(
                'user_id' => $this->input->post('user_id'),
                'host_id' => $this->input->post('host_id'),
            );
            // First check to see if they are the owner or an admin, if so set the host
            if ($this->current_user->group = 'admin') {
                if ($this->Store->remove_host_user($host_data)) {
                    $this->data['message'] = 'The user '.$host_data['user_id'].' was removed from the host '.$host_data['host_id'];
                } else {
                    $this->data['message'] = 'The user '.$host_data['user_id'].' was unable to be removed from the host '.$host_data['host_id'];
                    //echo '<div class="alert error" style="width: auto;">The user '.$host_data['user_id'].' was unable to be removed from the host '.$host_data['host_id'].'</div>';
                }
            }
            $this->template->build('/admin/remove_host_user', $this->data);
        } else {
            $this->template->build('/admin/remove_host_user', $this->data);
        }
    }
}
