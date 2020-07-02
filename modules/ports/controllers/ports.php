<?php defined('BASEPATH') or exit('No direct script access allowed');

class Ports extends Public_Controller
{
    private $_connection;
    private $ssh;
    protected $section = 'ports';

    public function __construct()
    {
        parent::__construct();

        $this->load->language('ports');
        $this->load->model('hosts/hosts_m');
        $this->load->model('ports_m');


        $this->load->driver('Streams');
    }

    public function index($host_id = false)
    {
        //if($host_id){
        //	$extra = array(
        //				   'stream' => 'user port',
        //				   'ports'	=> 'ports',
        //				   'where'	=> "host_id='$host_id'"
        //				   );
        //	$result = $this->stream->entries->get_entries($extra);
        //	$this->template->set('ports', $result['entries'] );
        //}
        $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');
        $this->template
                ->set('host_id', $host_id)
                ->set('server_id', $host->host_server_id)
                ->append_js('module::ports.js')
                ->title('Your Ports')
                ->build('port_index');
    }

    public function create($host_id)
    {
        $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');

        $this->template
                ->title('Add Port')
                ->set('host_id', $host_id)
                ->set('server_id', $host->host_server_id)
                ->append_js('module::ports.js')
                ->build('port_create');
    }

    public function edit($port_id)
    {
        $this->template->title(lang('global:edit'));

        $this->template
                ->set('port_id', $port_id)
                ->append_js('module::ports.js')
                ->title('Edit Port')
                ->build('port_edit');
    }

    public function delete($id)
    {
        $this->load->library('Connect');
        $port = $this->streams->entries->get_entry($id, 'user_port', 'ports', false);

        // If the node is down then don't do anything.
        $server_ip = $this->streams->entries->get_entry(
            $port->server_id,
            'servers',
            'servers',
            false
        )->server_ip;
        if (!$this->connect->serverIsUp($server_ip)) {
            echo "The server is down.";
            die(header("HTTP/1.0 500 Server Error"));
        }

        //disable the port
        if ($port->is_active) {
            $this->connect->switchPort($port, $port->protocol, $port->ip_rule, $port->host_id);
        }

        // Delete Guac RDP entry if it exists.
        if ($port->service_name == 'remote_rdp') {
            $guac_db = $this->load->database('guac_db', true);
            // Get the connection id by looking up the remote port used.
            $guac_conn_id = $this->db->get_where('default_hosts_hosts', array('id' => $port->host_id), 1)->result()[0]->host_guac_rdp_id;
            // The table is set to cascade foreign keys on delete,
            // so all entries in all tables with the connection id will be deleted.
            $guac_db->delete('guacamole_connection', array('connection_id' => $guac_conn_id));
            $guac_db->close();
            // Update the host row.
            $this->db->update('default_hosts_hosts', array('host_guac_rdp_id' => null), array('id' => $port->host_id));
        }

        // Delete Guac VNC entry if it exists.
        if ($port->service_name == 'remote_vnc') {
            $guac_db = $this->load->database('guac_db', true);
            // Get the connection id by looking up the remote port used.
            $guac_conn_id = $this->db->get_where('default_hosts_hosts', array('id' => $port->host_id), 1)->result()[0]->host_guac_vnc_id;
            // The table is set to cascade foreign keys on delete,
            // so all tables with the connection id will be deleted.
            $guac_db->delete('guacamole_connection', array('connection_id' => $guac_conn_id));
            $guac_db->close();
            // Update the host row.
            $this->db->update('default_hosts_hosts', array('host_guac_vnc_id' => null), array('id' => $port->host_id));
        }

        //push the cfg to the host, restarting the service. Hangs if the host isn't connected.
        $this->connect->pushConfig($port->host_id);

        $this->streams->entries->delete_entry($id, 'user_port', 'ports');

        redirect('ports/index/'.$port->host_id, 'refresh');
    }

    public function switchPort($id)
    {
        $this->load->library('Connect');

        $ip_rule     = $this->input->get('ip_rule');
        $remote_port = $this->input->get('remote_port');
        $protocol    = $this->input->get('protocol');

        $port = $this->streams->entries->get_entry($id, 'user_port', 'ports', false);

        // If the node is down then don't do anything.
        $server_ip = $this->streams->entries->get_entry(
            $port->server_id,
            'servers',
            'servers',
            false
        )->server_ip;

        if (!$this->connect->serverIsUp($server_ip)) {
            echo "The server is down.";
            die(header("HTTP/1.0 500 Server Error"));
        }

        if ($this->current_user->id == $port->created_by || $this->current_user->group == 'admin') {
            $server = $this->streams->entries->get_entry($port->server_id, 'servers', 'servers');
            $this->connect->switchPort($port, $protocol, $ip_rule, $port->host_id, $server);
            redirect('ports', 'refresh');
        }
    }

    public function checkPortExists($port)
    {
        if ($port) {
            $response = shell_exec('sudo iptables -L | grep ' . $port);
            if ($response != '') {
                return true;
            } else {
                return false;
            }
        }
    }

    public function checkId($id)
    {
        $entries = $this->streams->entries->get_entry($id, 'user_port', 'ports', false);

        if ($entries) {
            echo json_encode($entries);
            return true;
        } else {
            return false;
        }
    }
}

/* End of file ports.php */
