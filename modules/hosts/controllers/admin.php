<?php
class Admin extends Admin_Controller
{
    protected $section = 'hosts';
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hosts_m');

        //$this->load->model('license/License_m', 'License');
        $this->lang->load('hosts');
        $this->load->helper('security');
    }

    public function index()
    {
        $extra['title'] = lang('hosts:hosts');

        $extra['buttons'] = array(
        array(
        'label'     => lang('global:edit'),
        'url'       => 'admin/hosts/edit/-entry_id-'
        ),
        array(
        'label'     => lang('global:delete'),
        'url'       => 'admin/hosts/delete/-entry_id-',
        'confirm'   => true,
        )
    );

        $this->streams->cp->entries_table('hosts', 'hosts', 10, 'admin/hosts/index', true, $extra);
    }
    public function create()
    {
        $extra = array(
            'return' => 'admin/hosts',
            'success_message' => lang('hosts:submit_success'),
            'failure_message' => lang('hosts:submit_failure'),
            'title' => lang('lang:hosts:new'),
         );

        $this->streams->cp->entry_form('hosts', 'hosts', 'new', null, true, $extra);
    }

    public function edit($id)
    {
        $extra = array(
            'return' => 'admin/hosts',
            'success_message' => lang('projects:submit_success'),
            'failure_message' => lang('projects:submit_failure'),
            'title' => lang('lang:hosts:edit_host'),
        );

        $this->streams->cp->entry_form('hosts', 'hosts', 'edit', $id, true, $extra);
    }

    public function delete($id)
    {
        $this->streams->entries->delete_entry($id, 'hosts', 'hosts');
        redirect('admin/hosts', 'refresh');
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
                if ($this->Host->remove_host_user($host_data)) {
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
    public function host_change($host_id)
    {
        $this->Host->host_change($host_id);
    }
}
