<?php defined('BASEPATH') or exit('No direct script access allowed');

class Servers extends Public_Controller
{
    protected $section = 'servers';

    public function __construct()
    {
        parent::__construct();
        $this->load->language('servers');
        $this->load->driver('Streams');
        $this->load->library('Connect');
        $this->load->model('servers_m');
    }

    public function index()
    {
        $this->template
                ->title($this->module_details['name'])
                ->build('servers_index');
    }

    public function serverIsUp()
    {
        $server_id = $_POST['id'];
        $ip = $this->servers_m->get_server($server_id)->server_ip;
        if (!$this->connect->serverIsUp($ip)) {
            echo "The server is down.";
            die(header("HTTP/1.0 500 Server Error"));
        }
        //return $this->connect->serverIsUp($ip);
    }
}

/* End of file hosts.php */
