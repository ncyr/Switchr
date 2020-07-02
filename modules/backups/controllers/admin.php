<?php
class Admin extends Admin_Controller
{
    protected $section = 'backups';

    public function __construct()
    {
        parent::__construct();

        $this->load->model('servers/servers_m');
        $this->load->model('hosts/hosts_m');

        $this->load->model('backups_m');
        //$this->load->library('Backups');
        $this->load->library('Connect');
    }

    public function index($year=false, $month=false)
    {
        $this->template->build('admin/backups', $this->data);
    }

    public function remove_file($file=false, $location)
    {
        if ($this->currentStore) {
            $this->backups->removeFile($file, $location);
        }
    }

    public function show_folder($folder)
    {
        $this->data['files'] = $this->backups->showFolder($this->currentStore, $file);
        $this->template->build('/admin/show_day', $this->data);
    }

    //function show_backups($year, $month)
    //{
    //	$currentStore = $this->session->userdata('current_store');
    //	$this->data['month'] = sprintf('%02d', $month);
    //	$this->data['year'] = $year;
    //
    //	if($currentStore)
    //	{
    //		$this->backups->showBackups($currentStore);
    //		$this->data['eventDates'] = $this->backups->getEvents($month, $year);
    //	}
    //
    //	// Loads from addons/modules/blog/views/admin/view_name.php
    //	$this->load->view('admin/show_backups', $this->data);
    //}
    public function show_backups($sourceId, $location = false)
    {
        if ($this->currentStore) {
            $this->data['sourceId'] = $sourceId;
            $this->data['location'] = $location;
            if ($location) {
                $this->data['files'] = $this->backups->getBackups($sourceId, $location);
            } else {
                $this->data['files'] = $this->backups->getBackups($sourceId);
            }
        }
        $this->template->build('admin/show_backups', $this->data);
    }
    public function settings()
    {
        $this->template->build('/admin/settings', $this->data);
    }
    public function config_source($sourceId)
    {
        $post = $this->input->post();
        if ($this->input->post()) {
            $post['password'] = $this->encrypt->encode($post['password']);
            $post['user_cert'] = $this->encrypt->encode($post['user_cert']);
            $post['user_cert_pwd'] = $this->encrypt->encode($post['user_cert_pwd']);
            array_pop($post);
            array_pop($post);
            $this->db->where('id', $sourceId);
            $this->db->update('backup_sources', $post);
            redirect('/admin/backups');
        }
        $this->data['sourceId'] = $sourceId;
        $this->data['source'] = $this->backups->getSource($sourceId);
        $this->template->build('/admin/config_source', $this->data);
    }
    public function create_source()
    {
        $post = $this->input->post();

        if ($this->input->post()) {
            $post['password'] = $this->encrypt->encode($post['password']);
            $post['user_cert'] = $this->encrypt->encode($post['user_cert']);
            $post['user_cert_pwd'] = $this->encrypt->encode($post['user_cert_pwd']);

            array_pop($post);
            array_pop($post);
            $post['owner_id'] = $this->currentStore;
            $this->db->insert('backup_sources', $post);
            redirect('/admin/backups');
        }
        $this->template->build('/admin/create_source', $this->data);
    }
}
