<?php
class Admin extends Admin_Controller
{
    protected $section = 'servers';
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Servers_m');
        $this->load->library('Servers');
        $this->load->library('Connect');
        $this->load->model('hosts/hosts_m');
        $this->lang->load('servers');
        $this->load->helper('security');
    }

    public function index()
    {
        $extra['title'] = lang('servers:servers');

        $extra['buttons'] = array(
        array(
        'label'     => lang('global:edit'),
        'url'       => 'admin/servers/edit/-entry_id-'
        ),
        array(
        'label'     => lang('global:delete'),
        'url'       => 'admin/servers/delete/-entry_id-',
        'confirm'   => true,
        )
    );
        $this->streams->cp->entries_table('servers', 'servers', 3, 'admin/servers/index', true, $extra);
    }
    public function create()
    {
        $extra = array(
            'return' => 'admin/servers',
            'success_message' => lang('servers:submit_success'),
            'failure_message' => lang('servers:submit_failure'),
            'title' => lang('servers:new'),
         );

        $this->streams->cp->entry_form('servers', 'servers', 'new', null, true, $extra);
    }

    public function edit($id)
    {
        $extra = array(
            'return' => 'admin/servers',
            'success_message' => lang('projects:submit_success'),
            'failure_message' => lang('projects:submit_failure'),
            'title' => lang('servers:edit_host'),
        );

        $this->streams->cp->entry_form('servers', 'servers', 'edit', $id, true, $extra);
    }

    public function delete($id)
    {
        // Get server object.
        $server = $this->servers_m->get_server($id);
        // Remove all hosts.
        $entry_data = array(
            'namespace' => 'hosts',
            'stream' => 'hosts',
            'where' => 'host_server_id='.$server->id,
        );
        $hosts = $this->streams->entries->get_entries($entry_data);
        foreach ($hosts['entries'] as $host) {  // $host is an array, not an object!
            $this->hosts_m->deleteHost($host['id']);
        }
        // Remove iptables rules if necessary.
        $this->connect->iptablesRemove($server);

        // Remove from database.
        $this->streams->entries->delete_entry($id, 'servers', 'servers');
        redirect('admin/servers', 'refresh');
    }

    public function addServer($id=false)
    {
        //create and store a ssh key for the server at central host
        //$this->servers->addServer($id);
    }

    public function upload_key()
    {
        if ($this->input->post()) {
            $this->servers->uploadKey($this->input->post('key_data'), $this->input->post('server_id'));
            redirect('admin/servers');
        } else {
            $entry_data = array(
                                'stream' => 'servers',
                                'namespace' => 'servers'
                                );
            $data = array(
                            'key_data' => $this->input->post('key_data'),
                            'server_list' => $this->streams->entries->get_entries($entry_data),
                          );
            $this->template
            ->build('admin/server_upload_key', $data);
        }
    }
}
