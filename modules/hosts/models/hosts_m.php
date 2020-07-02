<?php
class Hosts_m extends MY_Model
{
    public $server_stream;
    public $host_id;
    public $ssh;

    private $hosts;
    private $host;
    private $server;


    public function __construct()
    {
        parent::__construct();

        //$this->_table = 'hosts';
        $this->load->driver('Streams');
        $this->load->library('Logging');
        $this->load->library('Connect');
        $this->load->model('license/license_m');
    }

    public function createHost($trigger_data)  // $trigger_data['entry_id'] == host id
    {
        //specify port ranges to use for forwarding
        $port_range_from = 35000;
        $port_range_to   = 45000;
        $ssh_port = $this->connect->availablePort(
            $trigger_data['insert_data']['host_server_id'],
            $port_range_from,
            $port_range_to
        );

        //check if the stream slug is the hosts
        if ($trigger_data['stream']->stream_slug == 'hosts') {
            //create the shell user on the server for this host.
            $stream = $trigger_data['insert_data'];
            $entry_id = $trigger_data['entry_id'];  // This is the hosts_hosts.id that was created in table by Streams
            //username and password generation that was not included into trigger data
            $length = 12;
            $host_ssh_user = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $length); //ya do the shuffle...
            $host_ssh_pass = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $length); //and ya shuffle again!
            //add if check for connection, remove from addshelluser
            if ($this->connect->addShellUser($host_ssh_user, $host_ssh_pass, $stream['host_server_id'], $entry_id)) {
                //create log
                $this->logging->create('Hosts', 'A new host has been added.', $entry_id);

                //update the host data
                $entry_data = array(
                    'host_ssh_user' => $host_ssh_user,
                    'host_ssh_pass' => $host_ssh_pass,
                    'host_ssh_port' => $ssh_port
                );

                if ($this->streams->entries->update_entry($entry_id, $entry_data, 'hosts', 'hosts')) {
                    return true;
                }
            }
            return false;
        }
    }

    public function get_host($id)
    {
        $host = $this->streams->entries->get_entry($id, 'hosts', 'hosts');
        return $host;
    }

    /**
     * Get all hosts belonging to a user by user ID.
     * @param   string  $id  ID of user.
     * @return  array        Indexed array of hosts.
     */
    public function get_hosts($id)
    {
        $this->table = 'hosts';
        $data = array(
            'namespace' => 'hosts',
            'stream'    => 'hosts',
            'where'     => SITE_REF . "_hosts_hosts.created_by='$id'"
        );
        $hosts = $this->streams->entries->get_entries($data);
        return $hosts['entries'];
    }

    /**
     * Get all hosts assigned to a user.
     * @param   int    $user_id  ID of user.
     * @return  array
     */
    public function getMyAssignedHosts($user_id)
    {
        $data = array(
            'namespace' => 'hosts',
            'stream'    => 'host_users',
            'where'     => SITE_REF . "_hosts_host_users.user_id ='$user_id'"
        );
        $assignments = $this->streams->entries->get_entries($data)['entries'];
        // TODO: This whole block needs to be optimized because of the multiple database calls.
        // Add 'server_ip' to assigned host so we can use it in the view.
        for ($i = 0; $i < count($assignments); $i++) {
            $server_ip = $this->db->get_where(
                SITE_REF.'_servers_servers',
                array('id' => $assignments[$i]['host_id']['host_server_id']),
                1
            )->result()[0]->server_ip;
            $assignments[$i]['host_id']['server_ip'] = $server_ip;
            // $assignments[$i]['host_id']['remote_token'] = $_COOKIE["remote_token_$server_id"];

            // Get permissions for assigned hosts to be used in the hosts_index view.
            $permissions = $this->db->get_where(
                SITE_REF.'_hosts_host_users',
                array('host_id' => $assignments[$i]['host_id']['id'], 'user_id' => $user_id),
                1
            )->result()[0];
            $assignments[$i]['host_id']['permissions'] = $permissions;
        }

        return $assignments;
    }

    /**
     * Get all hosts that have been assigned to users.
     * @param   int    $user_id  ID of admin/assigning user.
     * @return  array
     */
    public function getHostsAssigned($user_id)
    {
        $data = array(
            'namespace' => 'hosts',
            'stream'    => 'host_users',
            'where'     => SITE_REF . "_hosts_host_users.created_by ='$user_id'"
        );
        $assignments = $this->streams->entries->get_entries($data)['entries'];

        // Add 'server_ip' to assigned host so we can use it in the view.
        for ($i = 0; $i < count($assignments); $i++) {
            $server_ip = $this->db->get_where(
                SITE_REF.'_servers_servers',
                array('id' => $assignments[$i]['host_id']['host_server_id']),
                1
            )->result()[0]->server_ip;
            $assignments[$i]['host_id']['server_ip'] = $server_ip;
            // $assignments[$i]['host_id']['remote_token'] = $_COOKIE["remote_token_$server_id"];
        }

        return $assignments;
    }

    /**
     * Add permissions for a user to access a Guacamole connection.
     * The connection must already exist, this just adds permission for a user.
     * @param  integer  $user_id  ID of user; it's the same in Pyro and Guac.
     * @param  integer  $host_id  ID of host.
     */
    public function addGuacPermission($user_id, $host_id)
    {
        $host = $this->get_host($host_id);
        // Return false if there is no connection set up.
        if (!$host->host_guac_rdp_id && !$host->host_guac_vnc_id) {
            return false;
        }

        $guac_db = $this->load->database('guac_db', true);
        if ($host->host_guac_rdp_id) {
            $guac_db->insert('guacamole_connection_permission', array(
                'user_id'       => $user_id,
                'connection_id' => $host->host_guac_rdp_id,
                'permission'    => 'READ',
            ));
        }
        if ($host->host_guac_vnc_id) {
            $guac_db->insert('guacamole_connection_permission', array(
                'user_id'       => $user_id,
                'connection_id' => $host->host_guac_vnc_id,
                'permission'    => 'READ',
            ));
        }
        $guac_db->close();
    }

    /**
     * Remove permissions for a user to access a Guacamole connection.
     * @param  integer  $user_id  ID of user; it's the same in Pyro and Guac.
     * @param  integer  $host_id  ID of host.
     */
    public function removeGuacPermission($user_id, $host_id)
    {
        $host = $this->get_host($host_id);
        // Return false if there is no connection set up.
        if (!$host->host_guac_rdp_id && !$host->host_guac_vnc_id) {
            return false;
        }

        $guac_db = $this->load->database('guac_db', true);
        if ($host->host_guac_rdp_id) {
            $guac_db->delete('guacamole_connection_permission', array(
                'user_id'       => $user_id,
                'connection_id' => $host->host_guac_rdp_id,
            ));
        }
        if ($host->host_guac_vnc_id) {
            $guac_db->delete('guacamole_connection_permission', array(
                'user_id'       => $user_id,
                'connection_id' => $host->host_guac_rdp_id,
            ));
        }
        $guac_db->close();
    }


    public function get_host_license($host_id)
    {
        $query = $this->db->query("SELECT * FROM " . SITE_REF . "_hosts_hosts WHERE id = $host_id LIMIT 1;");
        $license = $query->row()->host_license;
        return $license;
    }

    public function checkUserExists($user)
    {
        $data = array(
            'namespace' => 'hosts',
            'stream'    => 'hosts',
            'where'     => "host_ssh_user='$user'"
        );
        $host = $this->streams->entries->get_entries($data);
        if (count($host['entries']) > 0) {
            return true;
        } else {
            return false;
        }

        throw 'There was a problem checking if the user exists!';
    }

    public function get_host_by_key($key)
    {
        $data = array(
            'stream'    => 'license_serials',
            'namespace' => 'license',
            'where'     => "license_serial ='$key'",
            'paginate'  => true,
            'pag_segment' => 3
        );
        $result = $this->streams->entries->get_entries($data);

        if (count($result['entries']) > 0) {
            $data = array(
                'stream'    => 'hosts',
                'namespace' => 'hosts',
                'where'     => "host_license ='" . $result['entries'][0]['id'] . "'",
                'paginate'  => true,
                'pag_segment' => 3
            );
            $result = $this->streams->entries->get_entries($data);

            return $result['entries'][0];
        } else {
            return false;
        }
    }

    /**
     * Remove host access from an assigned user.
     * @param  int  $host_id  ID of assigned host.
     * @param  int  $user_id  ID of assigned user.
     */
    public function deleteAssigned($host_id, $user_id)
    {
        if ($this->hosts_m->is_owner($host_id)) {
            $entry_id = $this->streams->entries->get_entries(array(
                'stream'    => 'host_users',
                'namespace' => 'hosts',
                'where'     => SITE_REF."_hosts_host_users.host_id={$host_id} AND ".SITE_REF."_hosts_host_users.user_id={$user_id}",
                'limit'     => 1
            ))['entries'][0]['id'];
            $this->streams->entries->delete_entry($entry_id, 'host_users', 'hosts');
            // Remove assigned user's Guacamole permissions.
            $this->removeGuacPermission($user_id, $host_id);
        } else {
            return false;
        }
    }

    public function deleteHost($host_id)
    {
        //TODO REMOVE BACKUPS
        //are they the owner of the host record or an admin?
        if ($host_data = $this->hosts_m->is_owner($host_id)) {
            $host = $host_data['host'];
            $server = $host_data['server'];

            // If no server, don't continue.
            $server_ip = $this->streams->entries->get_entry(
                $host->host_server_id,
                'servers',
                'servers',
                false
            )->server_ip;
            if (!$this->connect->serverIsUp($server_ip)) {
                echo "The server is down.";
                die(header("HTTP/1.0 500 Server Error"));
            }

            //disable port if active.
            $entry_data = array(
                'namespace' => 'ports',
                'stream' => 'user_port',
                'where' => 'host_id='.$host->id,
            );
            $port = $this->streams->entries->get_entries($entry_data);

            foreach ($port['entries'] as $port) {
                if ($port['is_active']['key'] == '1') {
                    //toggle port
                    $port_obj = new stdClass();

                    foreach ($port as $k => $v) {
                        $port_obj->{$k} = $v;
                    }
                    $this->connect->switchPort($port_obj, 'tcp', $port['ip_rule'], $port['host_id']['id']);
                }

                $this->streams->entries->delete_entry($port['id'], 'user_port', 'ports');
            }

            //push the cfg to the host, restarting the service. Hangs if the host isn't connected.
            //$this->connect->pushConfig($host->id);
            $this->connect->delConfig($host->id);

            $this->connect->removeUser($host->id);

            $this->streams->entries->delete_entry($host->id, 'hosts', 'hosts');

            // Delete license associated with host.
            $this->streams->entries->delete_entry($host->host_license, 'license_serials', 'license');

            // Delete logs associated with host.
            $logs = $this->streams->entries->get_entries(array(
                'namespace' => 'logging',
                'stream'    => 'logging',
                'where'     => 'logging_host_id='.$host->id,
            ));
            foreach ($logs['entries'] as $log) {
                $this->streams->entries->delete_entry($log['id'], 'logging', 'logging');
            }

            // Delete host connection from Guacamole database.
            $guac_db = $this->load->database('guac_db', true);
            if ($host->host_guac_vnc_id) {
                $guac_db->delete('guacamole_connection', array('id' => $host->host_guac_vnc_id));
            }
            if ($host->host_guac_rdp_id) {
                $guac_db->delete('guacamole_connection', array('id' => $host->host_guac_rdp_id));
            }
            $guac_db->close();
        } else {
            //TODO = redirect to an un-authorized access page
        }
    }

    //checks if owner or admin
    //TODO join these tables manually, so we can make
    //one query instead of one for each module by NOT using streams
    public function is_owner($host_id, $group = false)
    {
        $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');
        if ($group) {
            //if group optioned, check to see that the group equals existing current_user group
            // or deny them right away
            if ($group !== $this->current_user->group || $this->current_user->group !== 'admin') {
                return false;
            }
        }

        //make sure they also have ownership of the host or are admin
        if ($host->created_by === $this->current_user->id || $this->current_user->group === 'admin') {
            $server = $this->streams->entries->get_entry($host->host_server_id, 'servers', 'servers');
            if ($host && $server) {
                //TODO add ports backups and else to return.
                return array('host' => $host, 'server' => $server);
            } else {
                return false;
            }
        }
        return false;   
    }
    public function is_assigned($host_id){
        //get the host info
        $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');
        $server = $this->streams->entries->get_entry($host->host_server_id, 'servers', 'servers');

        $user_id = $this->current_user->id;
        //check assigned user to host
        $assigned_user = $this->streams->entries->get_entries(array(
            'stream'    => 'host_users',
            'namespace' => 'hosts',
            'where'     => SITE_REF."_hosts_host_users.host_id={$host_id} AND ".SITE_REF."_hosts_host_users.user_id={$user_id}",
            'limit'     => 1
        ))['entries'][0];
        if(count($assigned_user) > 0)
        {
            //return the host data that is assigned
            return array('host' => $host, 'server' => $server);
        }
        return false;
    }

    public function fixWaitingGrind($host_id, $date)
    {
        $this->aloha->fixWaitingGrind($host_id, $date);
    }

    /**
     * Convert Unix timestamp to datetime string.
     * Return date.
     * @param   integer  $timestamp  Unix timestamp.
     * @return  string               Formatted Datetime string.
     */
    public function day($timestamp)
    {
        // checking $protocol in HTTP or HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            // this is HTTPS
            $protocol  = "https://";
        } else {
            // this is HTTP
            $protocol  = "http://";
        }
        $url = $protocol.$_SERVER['SERVER_NAME'];
        if ($timestamp && $this->current_user->timezone) {
            return (new DateTime('@'.$timestamp))->setTimeZone(new DateTimeZone($this->current_user->timezone))->format('Y-m-d');
        } elseif (!$timestamp) {
            return "No timestamp.";
        } elseif (!$this->current_user->timezone) {
            return "<a href='$url/edit-profile'>Set Timezone</a>";
        }
    }

    /**
     * Convert Unix timestamp to datetime string.
     * Return time.
     * @param   integer  $timestamp  Unix timestamp.
     * @return  string               Formatted Datetime string.
     */
    public function hour($timestamp)
    {
        if ($timestamp && $this->current_user->timezone) {
            return (new DateTime('@'.$timestamp))->setTimeZone(new DateTimeZone($this->current_user->timezone))->format('H:i:s');
        } else {
            return "";
        }
    }

    /**
     * Get directory list from the host
     *
     * @param   integer  $host_id  
     * @param   string  $location  Directory to list.
     * @return  array               Indexed array listing of file and folder names.
     */
    public function getDirList($host_id, $location)
    {

    }
}
