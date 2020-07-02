<?php
class Ports_m extends MY_Model
{
    public function __construct()
    {
        parent::__construct();

        /**
         * If the hosts module's table was named "hostss"
         * then MY_Model would find it automatically. Since
         * I named it "hosts" then we just set the name here.
         */
        $this->_table = 'ports';
        $this->load->driver('Streams');
        $this->load->library('Connect');
    }
    public function get_port($port_id)
    {
        $result = $this->streams->entries->get_entry($port_id, 'user_port', 'ports');
        return $result;
    }

    public function get_ports($host_id)
    {
        $data = array(
                        'stream'    => 'user_port',
                        'namespace' => 'ports',
                        'where'    => "host_id =" . $host_id
                      );

        $result = $this->streams->entries->get_entries($data);

        return $result['entries'];
    }

    /**
     * Create a port. This is to get around using streams and events.php.
     * We manually insert the port here.
     * @param   int     $host_id
     * @param   int     $server_id
     * @param   int     $local_port  Port to open on host machine.
     * @param   string  $name        Description of entry.
     * @return  array                ID of newly inserted row and remote_port: 'port_id', 'remote_port'.
     */
    public function create($host_id, $server_id, $local_port, $name)
    {
        $port_range_from = 33000;
        $port_range_to   = 43000;

        $server_ip = $this->streams->entries->get_entry(
            $server_id,
            'servers',
            'servers',
            false
        )->server_ip;
        if (!$this->connect->serverIsUp($server_ip)) {
            echo "The server is down.";
            die(header("HTTP/1.0 500 Server Error"));
        }

        $port = $this->connect->availablePort(
            $server_id,
            $port_range_from,
            $port_range_to
        );

        $data = array(
            'host_id'      => $host_id,
            'server_id'    => $server_id,
            'service_name' => $name,
            'local_port'   => $local_port,
            'remote_port'  => $port,
            'is_active'    => '0',
            'created_by'   => $this->current_user->id,
            'created'      => date('Y-m-d H:i:s')
        );

        if ($this->db->insert('default_ports_user_port', $data)) {
            $port_id = $this->db->insert_id();
            $this->connect->pushConfig($host_id);
            return array('port_id' => $port_id, 'remote_port' => $port);
        } else {
            echo "Port creation failed.";
            die(header("HTTP/1.0 500 Server Error"));
        }
    }
}
