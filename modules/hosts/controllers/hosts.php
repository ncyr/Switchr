<?php defined('BASEPATH') or exit('No direct script access allowed');

class Hosts extends Public_Controller
{
    protected $section = 'hosts';

    public function __construct()
    {
        parent::__construct();

        $this->load->language('hosts');
        $this->load->library('Logging');
        $this->load->driver('Streams');
        $this->load->library('Connect');
        $this->load->model('servers/servers_m');
        $this->load->model('hosts_m');
        $this->load->model('ports/ports_m');
        $this->load->model('payignite/payignite_m', 'Payignite');
    }

    public function index()
    {
        $id = $this->current_user->id;
        // Get user's hosts and assigned hosts.
        $hosts = $this->hosts_m->get_hosts($id);
        $assigned_hosts = $this->hosts_m->getMyAssignedHosts($id);
        // Get the Guac auth token and add it to each host's array.
        for ($i = 0; $i < count($hosts); $i++) {
            $server_id = $hosts[$i]['host_server_id']['id'];
            $hosts[$i]['remote_token'] = $_COOKIE["remote_token_$server_id"];
        }
        for ($i = 0; $i < count($assigned_hosts); $i++) {
            $server_id = $assigned_hosts[$i]['host_id']['host_server_id'];
            $assigned_hosts[$i]['host_id']['remote_token'] = $_COOKIE["remote_token_$server_id"];
        }

        $servers = $this->servers_m->getAllServers()['entries'];

        $this->template
            ->title($this->module_details['name'])
            ->set('time_status_limit', date('U', strtotime('-40 seconds')))
            ->set('servers', $servers)
            ->set('hosts', $hosts)
            ->set('assigned_hosts', $assigned_hosts)
            ->append_js('module::hosts.js')
            ->build('hosts_index');
    }

    /**
     * Host creation uses AJAX, so we must echo results
     * back and take appropriate action with JS.
     * @return  string
     */
    public function ajaxCreate()
    {
        // If the user has not used all allocated hosts...
        if ($this->Payignite->hostsAvailable() > 0) {
            echo 'true';
            // If no Plan, must create one.
        } elseif ($this->Payignite->Plan->getPlan() == false) {
            echo '/payignite/create';
            // If the user has used all allocated hosts, must buy more.
            // TODO: Need to pass edit an ID.
        } else {
            echo '/payignite/edit/'.$this->Payignite->Plan->getPlan()['id'];
        }
    }

    // public function create()
    // {
    //     // I forget why I wanted a $_POST check here...
    //     // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     //     echo '<h3>POST!!!</h3>';
    //     //     die;
    //     // }
    //
    //     // Get an array of all servers to load into dropdown.
    //     $servers = $this->servers_m->getAllServers()['entries'];
    //     // If the user has not used all allocated hosts...
    //     if ($this->Payignite->hostsAvailable() > 0) {
    //         $this->template
    //             ->title('Add Host')
    //             ->set('servers', $servers)
    //             ->append_js('module::hosts.js')
    //             ->build('hosts_create');
    //         // If no Plan, must create one.
    //     } elseif ($this->Payignite->Plan->getPlan() == false) {
    //         redirect('/payignite/create');
    //         // If the user has used all allocated hosts, must buy more.
    //     } else {
    //         redirect('/hosts/create');
    //     }
    // }

    /**
     * Assign other user to current user's host.
     * This will allow the other user to manipulate that host.
     */
    public function assign_user($host_id = false)
    {
        // On form submission.
        if ($_POST) {
            // Get the id and email of a user by looking up the email address submitted.
            $this->db->select('id, email');
            $user = $this->db->get_where('default_users', array('email' => $_POST['user_email']), 1)->result()[0];

            // If no email address exists then refresh the page.
            // TODO: AJAX error modal.
            if (!$user) {
                redirect('hosts/assign_user', 'refresh');
            }

            $host = $this->hosts_m->get_host($_POST['host_id']);

            // Deny if user is not owner.
            if ($this->hosts_m->is_owner($_POST['host_id'])) {
                // If email address is found, then add user to table and redirect to different page.
                $entry_data = array(
                    'created'       => date("Y-m-d H:i:s"),
                    'created_by'    => $this->current_user->id,
                    'host_id'       => $_POST['host_id'],
                    'user_id'       => $user->id,
                    'perm_ports'    => $_POST['perm_ports'] ? 1 : 0,
                    'perm_push'     => $_POST['perm_push'] ? 1 : 0,
                    'perm_info'     => $_POST['perm_info'] ? 1 : 0,
                    'perm_network'  => $_POST['perm_network'] ? 1 : 0,
                    'perm_remove'   => $_POST['perm_remove'] ? 1 : 0,
                    'perm_reset'    => $_POST['perm_reset'] ? 1 : 0,
                    'perm_backup'   => $_POST['perm_backup'] ? 1 : 0,
                    'perm_reports'  => $_POST['perm_reports'] ? 1 : 0,
                    'perm_fixgrind' => $_POST['perm_fixgrind'] ? 1 : 0,
                    'perm_connect'  => $_POST['perm_connect'] ? 1 : 0,
                    'perm_restart'  => $_POST['perm_restart'] ? 1 : 0,

                );
                $this->db->insert('hosts_host_users', $entry_data);

                // Allow the assigned user Guacamole access if it has been set up.
                if ($_POST['perm_connect'] && ($host->host_guac_rdp_id || $host->host_guac_vnc_id)) {
                    $this->hosts_m->addGuacPermission($user->id, $_POST['host_id']);
                }
            }

            redirect('hosts/assigned_users/'.$_POST['host_id'], 'refresh');
        }

        // Initial view.
        $hosts = $this->hosts_m->get_hosts($this->current_user->id);
        $this->template
            ->title('Assign User')
            ->set('hosts', $hosts)
            ->set('host_id', $host_id)
            ->build('hosts_assign_user');
    }

    /**
     * Edit the permissions for an assigned user.
     */
    public function assign_user_edit($host_id, $assigned_user_id)
    {
        // On form submission.
        if ($_POST) {
            // If no email address exists then refresh the page.
            // TODO: AJAX error modal.
            if (!$assigned_user_id || !$host_id) {
                redirect('hosts/assign_user', 'refresh');
            }

            // Deny if user is not owner.
            if ($this->hosts_m->is_owner($host_id)) {
                // If email address is found, then add user to table and redirect to different page.
                $entry_data = array(
                    'updated'       => date("Y-m-d H:i:s"),
                    'host_id'       => $host_id,
                    'user_id'       => $assigned_user_id,
                    'perm_ports'    => $_POST['perm_ports'] ? 1 : 0,
                    'perm_push'     => $_POST['perm_push'] ? 1 : 0,
                    'perm_info'     => $_POST['perm_info'] ? 1 : 0,
                    'perm_network'  => $_POST['perm_network'] ? 1 : 0,
                    'perm_remove'   => $_POST['perm_remove'] ? 1 : 0,
                    'perm_reset'    => $_POST['perm_reset'] ? 1 : 0,
                    'perm_backup'   => $_POST['perm_backup'] ? 1 : 0,
                    'perm_reports'  => $_POST['perm_reports'] ? 1 : 0,
                    'perm_fixgrind' => $_POST['perm_fixgrind'] ? 1 : 0,
                    'perm_connect'  => $_POST['perm_connect'] ? 1 : 0,
                    'perm_restart'  => $_POST['perm_restart'] ? 1 : 0,

                );
                $this->db->where('host_id', $host_id);
                $this->db->where('user_id', $assigned_user_id);
                $this->db->update('hosts_host_users', $entry_data);

                // Add or remove Guacamole access.
                $host = $this->hosts_m->get_host($host_id);
                if ($_POST['perm_connect'] && ($host->host_guac_rdp_id || $host->host_guac_vnc_id)) {
                    $this->hosts_m->addGuacPermission($assigned_user_id, $host_id);
                } elseif (!$_POST['perm_connect'] && ($host->host_guac_rdp_id || $host->host_guac_vnc_id)) {
                    $this->hosts_m->removeGuacPermission($assigned_user_id, $host_id);
                }
            }

            redirect('hosts/assigned_users/'.$host_id, 'refresh');
        }

        // Deny if user is not owner.
        if ($this->hosts_m->is_owner($host_id)) {
            // Initial view.
            $host = $this->hosts_m->get_host($host_id);
            $current_permissions = $this->db->get_where(SITE_REF.'_hosts_host_users', array('host_id' => $host_id, 'user_id' => $assigned_user_id), 1)->result()[0];
            $assigned_user_email = $this->db->get_where(SITE_REF.'_users', array('id' => $assigned_user_id), 1)->result()[0]->email;
            $this->template
                ->title('Edit User Permissions')
                ->set('host', $host)
                ->set('host_id', $host_id)
                ->set('assigned_user_id', $assigned_user_id)
                ->set('assigned_user_email', $assigned_user_email)
                ->set('current_permissions', $current_permissions)
                ->build('hosts_assign_user_edit');
        } else {
            redirect('hosts', 'refresh');
        }
    }

    /**
     * View all users that have been assigned a host from the current user.
     * @param  int  $host_id  ID of host.
     */
    public function assigned_users($host_id = false)
    {
        $this->template
            ->set('host_id', $host_id)
            ->title('Assigned Users')
            ->build('hosts_assigned');
    }

    /**
     * Remove host access from an assigned user.
     * @param  int  $host_id  ID of host assigned.
     * @param  int  $user_id  ID of user assigned.
     */
    public function delete_assigned($host_id, $user_id)
    {
        $this->hosts_m->deleteAssigned($host_id, $user_id);
        redirect('/hosts/assigned_users/'.$host_id, 'refresh');
    }

    // public function assign_host_group($host_id, $group_id)
    // {
    //     $this->template
    //         ->set('host_id', $host_id)
    //         ->title('')
    //         ->build('hosts_assigned');
    // }

    public function create_ajax()
    {
        $this->template->build('hosts_create_ajax');
    }

    public function delete($id)
    {
        $this->load->model('hosts_m');
        $this->hosts_m->deleteHost($id);
        redirect('/hosts', 'refresh');
    }

    public function pushConfig($id)
    {
        $this->connect->pushConfig($id);
        redirect('/hosts', 'refresh');
    }

    public function restartHost($host_id)
    {
        $this->connect->restartHost($host_id);
        redirect('/hosts', 'refresh');
    }

    public function bandwidth($id)
    {
        $this->template
            ->title('Bandwidth Usage')
            ->set('host_id', $id)
            ->append_js('module::hosts.js')->append_js('theme::clipboard.min.js')
            ->build('host_band');
    }

    public function resetConnection($id)
    {
        //make sure they own it first
        $this->connect->resetConnection($id);
        redirect('/hosts', 'refresh');
    }

    public function resetService($id)
    {
        //make sure they own it first
        $this->connect->resetHostService($id);
        redirect('/hosts/dashboard/'.$id, 'refresh');
    }

    public function update($key)
    {
        $username = $this->input->post('username');
        $this->load->library('encrypt');
        if ($key == "holdontoyourpotatoesdrjones") {
            $params = array(
                'namespace' => 'hosts',
                'stream'    => 'hosts'
            );
            $hosts = $this->streams->entries->get_entries($params);
            foreach ($hosts['entries'] as $row) {
                if ($row['host_ssh_user'] == $username) {
                    $host_id = $row['id'];
                }
            }

            $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');

            if ($host_info = $this->input->post('computerinfo')) {
                $entry_data = array(
                        'host_info' => "$host_info"
                    );

                $this->streams->entries->update_entry($host_id, $entry_data, 'hosts', 'hosts');
                return true;
            } elseif ($heartbeat = $this->input->post('heartbeat')) {
                $entry_data = array(
                        'host_status_timestamp' => date('U')
                    );

                $this->streams->entries->update_entry($host_id, $entry_data, 'hosts', 'hosts');
                return true;
            }
        }
        return false;
    }

    public function dashboard($host_id)
    {
        $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');
        $this->template
            ->title('Dashboard')
            ->set('host', $host)
            ->append_js('module::hosts.js')
            ->build('host_dash');
    }

    /**
     * Get all connection logs for a host.
     * @param   integer  $host_id  ID of host to get all logs for.
     * @param   integer  $conn_id  VNC or RDP connection_id.
     */
    public function logs($host_id, $conn_id)
    {
        // Deny if user is not owner.
        if ($this->hosts_m->is_owner($host_id)) {
            // Get host for general purpose.
            $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');
            // Get logs.
            $guac_db = $this->load->database('guac_db', true);
            $logs = $guac_db->get_where('guacamole_connection_history', array('connection_id' => $conn_id))->result();

            $this->template
                ->title('Connection Logs')
                ->set('host', $host)
                ->set('logs', $logs)
                ->build('host_logs');
        } else {
            redirect('hosts', 'refresh');
        }
    }

    /**
     * Returns the value of library function Connect->okayToPush().
     * Checks for the 'CHANGED' file, which lets us know that the host will be restarting soon.
     * If it exists then we don't want to try to push for fear of the host being disconnected at that exact moment.
     * If user checks the option, install VNC on the remote host.
     * @param   string  Row ID of host.
     * @return  string  We are only using this function with AJAX,
     *                  so we need an actual string to check against: 'true' or 'false'
     */
    public function okayToPush()
    {
        // echo $this->connect->okayToPush($_POST['host_id']);
        if ($this->connect->okayToPush($_POST['host_id'])) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function vncExists()
    {
        $host_id = $_POST['host_id'];
        // Check for already-installed TightVNC.
        $cmd = 'sc query tvnserver';
        $vnc_current = $this->connect->hostcmd($cmd, $host_id);
        if (strpos($vnc_current, 'tvnserver') !== false) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function vncRemoveExists()
    {
        $host_id = $_POST['host_id'];
        $host    = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts', true);  // Needs Pyro to decrypt blobs.
        // Set URL for download link.
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https" : "http";
        $url = $url . "://$_SERVER[HTTP_HOST]";

        $bit     = null;
        if (json_decode(html_entity_decode($host->host_info))->OSArchitecture == '32-bit') {
            $bit = '32';
        } else {
            $bit = '64';
        }

        $cmd = '"bitsadmin /transfer vnc /priority high '. $url.'/files/tvnc-'.$bit.'.msi C:\tvnc-'.$bit.'.msi"';
        $this->connect->hostcmd($cmd, $host_id);

        // Uninstall TightVNC.
        $cmd = 'msiexec /x C:\tvnc-'.$bit.'.msi /quiet /norestart';
        $this->connect->hostcmd($cmd, $host_id);
        // Remove config in registry.
        $cmd = 'reg delete HKEY_LOCAL_MACHINE\Software\TightVNC\Server /va /f';
        $this->connect->hostcmd($cmd, $host_id);

        // Delete the installer.
        $cmd = 'del C:\tvnc-'.$bit.'.msi';
        $this->connect->hostcmd($cmd, $host_id);
    }

    /**
     * AJAX function to create Guacamole VNC connection.
     */
    public function createVnc()
    {
        // Open Guacamole DB connection and set variables.
        $guac_db = $this->load->database('guac_db', true);
        $host_id      = $_POST['host_id'];
        $host_name    = $_POST['host_name'];
        $vnc_port     = $_POST['vnc_port'];
        $vnc_password = $_POST['vnc_password'];
        $push_vnc     = $_POST['push_vnc'];
        $host         = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts', true);  // Needs Pyro to decrypt blobs.

        // If user checks the option, install VNC on the remote host.
        // $push_vnc is a string of 'true' or 'false'.
        if ($push_vnc == 'true') {
            // Set the URL of the website to download files.
            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https" : "http";
            $url = $url . "://$_SERVER[HTTP_HOST]";

            // If 32-bit the path will be 'Program Files'
            $bit = null;
            if (json_decode(html_entity_decode($host->host_info))->OSArchitecture == '32-bit') {
                $bit = '32';
            } else {
                $bit = '64';
            }

            $cmd = '"bitsadmin /transfer vnc /priority high '. $url.'/files/tvnc-'.$bit.'.msi C:\tvnc-'.$bit.'.msi"';
            $this->connect->hostcmd($cmd, $host_id);
            // Run the installer.
            $cmd = '"msiexec /i C:\tvnc-'.$bit.'.msi /quiet /norestart ADDLOCAL=Server SET_ALLOWLOOPBACK=1 VALUE_OF_ALLOWLOOPBACK=1 SET_RFBPORT=1 VALUE_OF_RFBPORT='.$vnc_port.' SET_PASSWORD=1 VALUE_OF_PASSWORD='.$vnc_password.'"';
            $this->connect->hostcmd($cmd, $host_id);
            // Delete the installer.
            $cmd = 'del C:\tvnc-'.$bit.'.msi';
            $this->connect->hostcmd($cmd, $host_id);
        }

        // Insert data into guacamole_connection.
        $guac_db->insert('guacamole_connection', array(
            'protocol'        => 'vnc',
            'connection_name' => $host_name,
            'max_connections' => 3,
            'max_connections_per_user' => 1,
        ));

        // Get the row ID of guacamole_connection insert.
        $conn_id = $guac_db->insert_id();

        // Create port on server for connection
        $port = $this->ports_m->create(
            $host_id,
            $host->host_server_id,
            $vnc_port,
            'remote_vnc'
        );

        // Insert hostname.
        $guac_db->insert('guacamole_connection_parameter', array(
            'connection_id'   => $conn_id,
            'parameter_name'  => 'hostname',
            'parameter_value' => 'localhost',
        ));
        // Insert VNC password.
        $guac_db->insert('guacamole_connection_parameter', array(
            'connection_id'   => $conn_id,
            'parameter_name'  => 'password',
            'parameter_value' => $vnc_password,
        ));
        // Insert VNC port.
        $guac_db->insert('guacamole_connection_parameter', array(
            'connection_id'   => $conn_id,
            'parameter_name'  => 'port',
            'parameter_value' => $port['remote_port'],
        ));

        // Allow this user to use this connection.
        $guac_db->insert('guacamole_connection_permission', array(
            'user_id'         => $this->current_user->id,
            'connection_id'   => $conn_id,
            'permission'      => 'READ',
        ));

        // Update host entry with guac_vnc connection ID.
        $this->streams->entries->update_entry($host_id, array('host_guac_vnc_id' => $conn_id), 'hosts', 'hosts');

        // If this host has already been assigned to other users then we
        // want to add any assigned users to the Guacamole permissions table.
        $assigned_hosts = $this->hosts_m->getHostsAssigned($this->current_user->id);
        foreach ($assigned_hosts as $index => $assigned_host) {
            if ($assigned_host['perm_connect']) {
                $guac_db->insert('guacamole_connection_permission', array(
                'user_id'         => $assigned_host['user_id']['user_id'],
                'connection_id'   => $conn_id,
                'permission'      => 'READ',
                ));
            }
        }

        $guac_db->close();
    }

    /**
     * AJAX function to create Guacamole RDP connection.
     */
    public function createRdp()
    {
        // Open Guacamole DB connection and set variables.
        $guac_db = $this->load->database('guac_db', true);
        $host_id      = $_POST['host_id'];
        $host_name    = $_POST['host_name'];
        $rdp_port     = $_POST['rdp_port'];
        $rdp_username = $_POST['rdp_username'];
        $rdp_password = $_POST['rdp_password'];
        $host         = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts', false);

        // Insert data into guacamole_connection.
        $guac_db->insert('guacamole_connection', array(
        'protocol'        => 'rdp',
        'connection_name' => $host_name,
        'max_connections' => 1,
        'max_connections_per_user' => 1,
        ));

        // Get the row ID of guacamole_connection insert.
        $conn_id = $guac_db->insert_id();

        // Create port on server for connection
        $port = $this->ports_m->create(
        $host_id,
        $host->host_server_id,
        $rdp_port,
        'remote_rdp'
        );

        // Insert hostname.
        $guac_db->insert('guacamole_connection_parameter', array(
        'connection_id'   => $conn_id,
        'parameter_name'  => 'hostname',
        'parameter_value' => 'localhost',
        ));
        // Insert RDP username.
        $guac_db->insert('guacamole_connection_parameter', array(
        'connection_id'   => $conn_id,
        'parameter_name'  => 'username',
        'parameter_value' => $rdp_username,
        ));
        // Insert RDP password.
        $guac_db->insert('guacamole_connection_parameter', array(
        'connection_id'   => $conn_id,
        'parameter_name'  => 'password',
        'parameter_value' => $rdp_password,
        ));
        // Insert RDP port.
        $guac_db->insert('guacamole_connection_parameter', array(
        'connection_id'   => $conn_id,
        'parameter_name'  => 'port',
        'parameter_value' => $port['remote_port'],
        ));

        // Allow this user to use this connection.
        $guac_db->insert('guacamole_connection_permission', array(
        'user_id'         => $this->current_user->id,
        'connection_id'   => $conn_id,
        'permission'      => 'READ',
        ));

        // Update host entry with guac_rdp connection ID.
        $this->streams->entries->update_entry($host_id, array('host_guac_rdp_id' => $conn_id), 'hosts', 'hosts');

        // If this host has already been assigned to other users then we
        // want to add any assigned users to the Guacamole permissions table.
        $assigned_hosts = $this->hosts_m->getHostsAssigned($this->current_user->id);
        foreach ($assigned_hosts as $index => $host) {
            if ($host['perm_connect']) {
                $guac_db->insert('guacamole_connection_permission', array(
                'user_id'         => $host['user_id']['user_id'],
                'connection_id'   => $conn_id,
                'permission'      => 'READ',
                ));
            }
        }

        $guac_db->close();
    }

    public function statusUpdateHosts($user_id)
    {
        $hosts = $this->hosts_m->get_hosts($user_id);
        echo json_encode($hosts);
    }

    public function statusUpdateAssignedHosts($user_id)
    {
        $assigned_hosts = $this->hosts_m->getMyAssignedHosts($user_id);
        echo json_encode($assigned_hosts);
    }

    public function network($host_id = false)
    {
        //if no host id was included, send them back to the hosts page.
        if (!$host_id) {
            redirect('hosts', 'refresh');
        }

        //list windows network workgroup computers
        $result = $this->connect->hostcmd("net view", $host_id);
        //make sure there was no hijack attempt message, so we dont show the user.
        if (strpos($result, '@@@')) {
            $data = array(
            'slug' => 'connection-problem',
            'to' => 'admin@switchr.io',
            'name' => 'Switchr Admin',
            'host_id' => $host_id
            );
            //mail the notification
            Events::trigger('email', $data, 'array');

            $this->template
            ->title("Host Network")
            ->set('error', true)
            ->build('hosts_network_index');
        } elseif (strpos($result, 'Cannot')) {
            $this->template
            ->title("Host Network")
            ->set('error', true)
            ->build('hosts_network_index');
        }
        $result = str_replace('The command completed successfully.', '', $result);
        //sepearate them by hosts which begin with two slashes
        $host_array = explode('\\', $result);
        //do not need this row - Server Name/Remark columns
        array_shift($host_array);
        //do not need this row - the dash across that makes an ASCII line
        array_shift($host_array);
        foreach ($host_array as $row) {
            //split by slashes
            $pre = preg_split('#\\\\[A-Z0-9]+#i', $row);
            foreach ($pre as $format) {
                //split name and description to array
                $stuff = preg_split('/[\s]+/', $format);
            }
            //put it back into an array
            array_pop($stuff);
            $parts[]  = $stuff;
        }
        //get arp table
        $arp = $this->connect->hostcmd("arp -a", $host_id);
        //split into array by line end
        $arp = explode("\n", $arp);
        //remove extra empty arrays
        $parts = array_filter($parts);

        //users list-style
        $users = $this->connect->hostcmd("net users", $host_id);
        $users = explode("\n", $users);
        //fourth row contains the users
        array_shift($users);
        array_shift($users);
        array_shift($users);
        array_shift($users);
        array_pop($users);
        array_pop($users);
        array_pop($users);
        //$users = array_filter( preg_split('/[\s]+/', $users[4].$users[5].$users[6].$users[7].$users[8]) );
        $this->template
            ->title("Host Network")
            ->set('hosts', $parts)
            ->set('arp', $arp)
            ->set('users', $users)
            ->append_js('module::network.js')
            ->build('hosts_network_index');
    }
    public function change_password($host_id, $username)
    {
        $user_id = $this->current_user->id;
        // Get user's hosts and assigned hosts.
        $hosts = $this->hosts_m->get_hosts($user_id);
        $assigned_hosts = $this->hosts_m->getMyAssignedHosts($id);
        //search arrays for existing host ids
        $found_host = array_search($host_id, array_column($hosts, 'id'));
        $found_assigned = array_search($host_id, array_column($assigned_hosts, 'id'));
        if ($found_host !== false || $found_assigned !== false) {
            $password = $_POST['password'];
            //change password line
            $cmd = $this->connect->hostcmd("net user ".$username." ".$password, $host_id);
            if (strpos($cmd, 'successfully')) {
                echo 'Password Change Complete';
            } else {
                echo 'There was a problem changing the password.';
            }
        } else {
            echo 'You do not have permission';
            //contact the admin someone is trying to break in
            $data = array(
            'slug' => 'no-access',
            'to' => 'admin@switchr.io',
            'name' => 'Switchr Admin',
            'host_id' => $host_id,
            'msg' => 'Password Attempt'
            );
            //mail the notification
            Events::trigger('email', $data, 'array');
        }
    }
}

/* End of file hosts.php */
