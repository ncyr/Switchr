<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Connect
{
    public $pushConfig;
    public $addShellUser;
    public $switchPort;
    public $getBaseData;

    protected $_ci;
    protected $_connection;
    public $ssh;

    public function __construct()
    {
        $this->_ci = &get_instance();
        $this->_ci->load->model('servers/servers_m');
        $this->_ci->load->model('hosts/hosts_m');
        $this->_ci->load->model('ports/ports_m');
        $this->_ci->load->library('Logging');
        $this->_ci->load->driver('Streams');

        $this->_ci->load->library('ConfigEncoder');
        $this->_ci->load->library('encrypt');
        $this->_ci->load->helper('Xml');
    }

    public function getStats($host_id)
    {
        $host = $this->_ci->host_m->getHost($host_id);
        $future = strtotime($host->updated, "+1 minute");
        return $this->checkTimer($host->timestamp, $future);
    }

    public function checkTimer($date, $future)
    {
        //check to see if the timestamp date is past the future.
        if (time() > $future) {
            return true;
        }
        return false;
    }

    public function checkStatus($host_id)
    {
        //which server are they on
        $host = $this->_ci->hosts_m->get_host($host_id);
        $server = $this->_ci->servers_m->get_server($host->host_server_id);
        if ($this->_connection($server, $host)) {
            if ($this->ssh->exec("sudo netstat -anp | grep $host->host_ssh_port | grep ESTABLISHED") != "") {
                return true;
            }
        }
        echo false;
    }

    /**
     * SSH Connection via phpseclib
     * @param   object   $server
     * @param   object   $host
     * @return  boolean
     */
    private function _connection($server, $host = false)
    {
        // If server is down return false.
        // if (!$this->serverIsUp($server->server_ip)) {
        //     return false;
        // }

        if (!$host) {
            $host = new stdClass();  // There must be an object to assign 'id' property to.
            // NOTE This should probably be 'null' instead of 0, but not sure if anything relies on the integer.
            //      If it is changed to 'null', then you probably don't even need this block at all.
            $host->id = 0;
        }

        //set_error_handler('errorHandler', E_USER_NOTICE);
        $path = '/addons/shared_addons/libraries/phpseclib';
        set_include_path($_SERVER['DOCUMENT_ROOT'].$path);

        include_once('Net/SSH2.php');
        include_once('Net/SFTP.php');
        include_once('Crypt/RSA.php');
        include_once('Crypt/AES.php');
        include_once('Crypt/Hash.php');
        include_once('Math/BigInteger.php');

        define('NET_SSH2_LOGGING', 2);

        $this->ssh = new Net_SSH2($server->server_ip);

        $rsa = new Crypt_RSA();

        $site = SITE_REF;
        $privatekey = '';
        //reset the path
        set_include_path(getcwd());
        if ($_SERVER['PYRO_ENV'] == 'staging') {
            $privatekey = file_get_contents("../ssl.switchr_beta/$site/$server->id");
        }
        if ($_SERVER['PYRO_ENV'] == 'production') {
            $privatekey = file_get_contents("../ssl.switchr/$site/$server->id");
        } else {
            $privatekey = file_get_contents("../ssl.switchr/$site/$server->id");
        }


        $rsa->setPassword($server->server_key_password);

        if ($rsa->loadKey(trim($privatekey)) === false) {
            $this->_ci->logging->create('Hosts', 'Security Key Failed to Load', $host->id, 1);
            return false;
        } else {
            $this->_ci->logging->create('Hosts', 'Security Key Loaded...', $host->id);
        }
        if ($this->ssh->login($server->server_username, $rsa) === false) {
            $this->_ci->logging->create('Hosts', 'Secure Login Unsuccessful', $host->id, 1);
            $this->_ci->logging->create(
                'Hosts',
                'There were errors connecting the host to our server: #'. $server->id . '-' . $server->server_name . ', please contact our support department',
                $host->id,
                1
            );
            return false;
        } else {
            $this->_ci->logging->create('Hosts', 'Secure Login Successful', $host->id);
            return true;
        }
        return false;
    }

    /**
     * Creates the shell user on the server
     * OS: Ubuntu/Debian Specs
     * @param   string $user       Username; ex: "4orvyw0ing17"
     * @param   string $pass       12 characters, similar to the username.
     * @param   string $server_id
     * @param   string $host_id
     * @return  boolean
     */
    public function addShellUser($user, $pass, $server_id, $host_id = null)
    {
        // Check if the username already exists on that server.
        if (!$this->_ci->hosts_m->checkUserExists($user)) {
            $host = $this->_ci->hosts_m->get_host($host_id);
            $server = $this->_ci->servers_m->get_server($server_id);

            // if the connection works...
            if ($this->_connection($server, $host)) {
                //command that creates the user and ssh key
                // $cmd = "sudo ./createuser $user $pass";
                $cmd = "sudo userdel $user; \
                    sudo mkdir /home/$user; \
                    sudo rm -rf /home/$user/.ssh; \
                    sudo useradd -d /home/$user $user; \
                    sudo mkdir /home/$user/.ssh; \
                    sudo chmod 700 /home/$user/.ssh; \
                    sudo chown $user:$user /home/$user/.ssh; \
                    sudo ssh-keygen -t rsa -b 2048 -N $pass -f /home/$user/.ssh/id_rsa; \
                    sudo cat /home/$user/.ssh/id_rsa.pub | sudo tee /home/$user/.ssh/authorized_keys > /dev/null; \
                    sudo chmod 600 /home/$user/.ssh/authorized_keys; \
                    sudo chown -Rf $user:$user /home/$user;";

                // execution - logging and return
                if ($this->ssh->exec($cmd)) {
                    $this->_ci->logging->create('Hosts', 'Created a user on the server: '. $this->_ci->streams->entries->get_entry($server_id, 'servers', 'servers')->server_name, $host_id);
                    return true;
                } else {
                    $this->_ci->logging->create('Hosts', 'Could not create user '. $this->_ci->streams->entries->get_entry($server_id, 'servers', 'servers')->server_name, $host_id);
                    return false;
                }
            } else {
                $this->_ci->logging->create('Hosts', 'Unable to create a user on the server: '. $this->_ci->streams->entries->get_entry($server_id, 'servers', 'servers')->server_name .', please contact our support department', $host_id, 1);
                return false;
            }
            return false;
        }
    }

    public function removeUser($id)
    {
        $host = $this->_ci->hosts_m->get_host($id);

        //if there a ssh connection exists
        if ($this->ssh) {
            //does the user exist
            if ($host->host_ssh_user && $host->host_ssh_user != "" && $host->host_ssh_user != " ") {
                $this->ssh->exec('sudo killall -u ' . $host->host_ssh_user);
                //command that creates the user and ssh key
                //$cmd = "sudo ./removeuser $host->host_ssh_user";
                $cmd = 'ret=false
                    getent passwd '.$host->host_ssh_user .'>/dev/null 2>&1 && ret=true
                    if $ret; then
                        sudo userdel -f '.$host->host_ssh_user.'
                        sudo rm -Rf /home/'.$host->host_ssh_user.'
                    else
                        echo "User does not exist"
                    fi';
                $error = $this->ssh->exec($cmd);
                //execution - logging and return
                if ($error) {
                    $this->_ci->logging->create('Hosts', "The command did not execute to remove the user - $host->host_ssh_user - ERROR: $error", $id, 1);
                    return false;
                } else {
                    $this->_ci->logging->create('Hosts', "Removed user - $host->host_ssh_user", $id);
                    //$this->_removeLicense($host->host_license);
                    return true;
                }
            } else {
                //check to see if the user has a directory
                $cmd = "ls /home | grep " . $host->host_ssh_user;
                $error = $this->ssh->exec($cmd);

                //if the directory exists
                if ($error = $host->host_ssh_user) {
                    $cmd = "sudo rm -Rf /home/".$host->host_ssh_user;
                    $this->ssh->exec($cmd);
                }
                //remove any license
                //$this->_removeLicense($host->host_license);

                $this->_ci->logging->create('Hosts', "User does not exist. We removed the folder and any related license from the system. - $host->host_ssh_user", $id, 1);
                return true;
            }
        } else {
            $this->_ci->logging->create('Hosts', "Cannot remove user, Cannot connect. - $host->host_ssh_user", $id, 1);
        }
        return false;
    }

    /**
     * Pushes the hosts current encrypted configuration.
     * @param   string  $host_id  Row ID of host.
     * @return  bool              True or false.
     */
    public function pushConfig($host_id)
    {
        $host = $this->_ci->hosts_m->get_host($host_id);
        $server = $this->_ci->servers_m->get_server($host->host_server_id['id']);
        //$this->_ci->logging->create('Hosts', "Testing pushConfig() log. Host =  $host, Server = $server");
        //if the connection works...
        if ($this->_connection($server, $host)) {
            //$this->_ci->logging->create('Hosts', "pushConfig() _connection works.");
            $config = $this->_ci->configencoder->load()->fromString($this->getBaseData($host, true));
            $config = $config->encrypt()->asBase64();

            //because we're not actually getting existing ssh cert creds from hosts switchr config.
            //this is a temporary fix for making sure hostcmd works all around after performing a pushconfig
            //since we are pushing no values for the hosts ssh creds at the moment,
            //switchr app will automatically fill them with rand.
            $precmd = 'sudo ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port . ' && sudo chown switchr:switchr /home/switchr/.ssh/known_hosts';
            $this->ssh->exec($precmd);

            //usr, pass, port, cfg
            //$cmd = "./pushconfig ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port . " " . $config;
            $cmd1 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo ".$config.">%~sISwitchru.p'";
            $cmd2 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED'";

            //$connected = $this->ssh->exec($cmd); //TODO if = "@" is when the REMOTE HOST warning

            // Check for the 'CHANGED' file, which lets us know that the host will be restarting soon.
            // If it exists then we don't want to try to push for fear of the host being disconnected at that exact moment.
            $dir = $this->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir \"%SWITCHR_DIR%\"'");
            if (strpos($dir, 'CHANGED') !== false) {
                echo 'There are unapplied changes. Please wait up to one minute before sending a new configuration.';
                return false;
            }

            $this->ssh->exec($cmd1);
            $error1 = $this->ssh->getStdError();
            $this->ssh->exec($cmd2);
            $error2 = $this->ssh->getStdError();
            // Check for errors from the ssh library.
            if ($error1) {
                $this->_ci->logging->create('Hosts', "Unable to sync config to host: cmd1 ".$error1, $host_id, 1);
                return false;
            }
            if ($error2) {
                $this->_ci->logging->create('Hosts', "Unable to sync config to host: cmd2 ".$error2, $host_id, 1);
                return false;
            }
            $this->_ci->logging->create('Hosts', 'Connect pushConfig() successful', $host_id, 0);
            return true;
        } else {
            $this->_ci->logging->create('Hosts', 'Connect pushConfig() connection error.', $host_id, 1);
            return false;
        }
    }

    /**
     * Deletes the host's configuration and stop the switchr service.
     * @param   string  $host_id  Row ID of host.
     * @return  bool              True or false.
     */
    public function delConfig($host_id)
    {
        $host = $this->_ci->hosts_m->get_host($host_id);
        $server = $this->_ci->servers_m->get_server($host->host_server_id['id']);
        //if the connection works...
        if ($this->_connection($server, $host)) {
            $config = $this->_ci->configencoder->load()->fromString($this->getBaseData($host, false));
            $config = $config->encrypt()->asBase64();

            //because we're not actually getting existing ssh cert creds from hosts switchr config.
            //this is a temporary fix for making sure hostcmd works all around after performing a pushconfig
            //since we are pushing no values for the hosts ssh creds at the moment,
            //switchr app will automatically fill them with rand.
            $precmd = 'sudo ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port . ' && sudo chown switchr:switchr /home/switchr/.ssh/known_hosts';
            $this->ssh->exec($precmd);

            //usr, pass, port, cmd
            //$cmd = "./pushconfig ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port . " " . $config;
            //$cmd2 = "sudo ./hostcmd ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port . " " . "net stop Switchr";
            $cmd1 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo ".$config.">%~sISwitchru.p'";
            $cmd2 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED'";
            //$connected = $this->ssh->exec($cmd); //TODO if = "@" is when the REMOTE HOST warning

            $this->ssh->exec($cmd1);
            $error1 = $this->ssh->getStdError();
            $this->ssh->exec($cmd2);
            $error2 = $this->ssh->getStdError();
            if ($error1) {
                $this->_ci->logging->create('Hosts', "Connect delConfig(): Unable to sync config to host: cmd1 ".$error1, $host_id, 1);
                return false;
            }
            if ($error2) {
                $this->_ci->logging->create('Hosts', "Connect delConfig(): Unable to sync backup to host: cmd2 ".$error2, $host_id, 1);
                return false;
            }
            $this->_ci->logging->create('Hosts', 'Connect delConfig() successful', $host->id, 0);
            return true;
        } else {
            $this->_ci->logging->create('Hosts', 'Connect delConfig() connection error.', $host_id, 1);
            return false;
        }
    }

    public function getRemoteConfig($host)
    {
        //should already be connected to the host at this point..
        //so then read the config
        $cmd = "sudo ./getconfig ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port;
        $result = $this->ssh->exec($cmd); //TODO if this didn't work then notify us so we can fix it. Log error
        $key = explode(' ', $result);
        if ($key[0] !== "ssh:") {
            return $key[2];
        }
        return false;
    }

    /****************************************
    * return the value of an encoded config
    *
    ****************************************/
    public function parseConfig($search, $string)
    {
        //viewing entire var dumps
        //ini_set("xdebug.var_display_max_children", -1);
        //ini_set("xdebug.var_display_max_data", -1);
        //ini_set("xdebug.var_display_max_depth", -1);
        if ($string) {
            $config = $this->_ci->configencoder->load()->fromString($string);
            $config = $config->decrypt($config);
            $config = substr($config, 0, -5); //remove extra ||||| for some reason

            $config = str_replace('<?xml version="1.0" encoding="utf-16"?>', '', $config);
            $xml = simplexml_load_string(trim($config));
            if ($xml) {
                return $xml->$search;
            }
        }
        return false;
    }

    /****************************************
    * toggle access through a port
    *
    ****************************************/
    public function switchPort($port, $protocol, $ip_rule, $host_id, $server)
    {
        if (!$this->ssh) {
            $host = $this->_ci->hosts_m->get_host($host_id);
            $server = $this->_ci->servers_m->get_server($port->server_id);
            $this->_connection($server, $host);
        }
        //set the rule source for the command
        $rule_source = '';
        if ($port->mac_rule) {
            $rule_source = " -m mac --mac-source $port->mac_rule";
        } else {
            $rule_source = ' -s '. $port->ip_rule;
        }

        //if they are active, we toggle them to inactive
        if ($port->is_active) {
            //check bandwidth usage and record.
            $query_data = array(
                'host_band_port' => $port->remote_port, 'updated'=>null
            );
            $host_band = $this->_ci->db->get_where('default_hosts_host_band', $query_data, 1)->row();

            $bandwidth_used_input = $this->getBandwidth($port->remote_port, 'INPUT');
            $bandwidth_used_forward = $this->getBandwidth($port->remote_port, 'FORWARD');
            $bandwidth_used_output = $this->getBandwidth($port->remote_port, 'OUTPUT');

            $insert_data = array(
                'host_band_port'    => $port->remote_port,
                'host_band_host_id' => $host_id,
                'host_band_input'   => ($host_band->host_band_input + $bandwidth_used_input),
                'host_band_forward' => ($host_band->host_band_forward + $bandwidth_used_forward),
                'host_band_output'  => ($host_band->host_band_output + $bandwidth_used_output),
            );
            $this->_ci->streams->entries->update_entry($host_band->id, $insert_data, 'host_band', 'hosts');

            $this->ssh->exec('sudo iptables -D INPUT -p '. $protocol .' --dport '. $port->remote_port . $rule_source .' -m state --state NEW,ESTABLISHED -j ACCEPT -m comment --comment "HostId:'.$host_id.'"');
            $this->ssh->exec('sudo iptables -D OUTPUT -p ' . $protocol . ' --sport ' . $port->remote_port . ' -m state --state ESTABLISHED,RELATED -j ACCEPT -m comment --comment "HostId:'.$host_id.'"');
            $this->ssh->exec('sudo bash -c "iptables-save > /etc/iptables/rules.v4"');

            $data = array(
                'is_active' => "0"
            );
            $this->_ci->streams->entries->update_entry($port->id, $data, 'user_port', 'ports');
        } //otherwise toggle them active
        else {
            //add new record for port at zero bandwidth.
            $insert_data = array(
                'host_band_port'    => $port->remote_port,
                'host_band_host_id' => $host_id,
                'host_band_input'   => 0,
                'host_band_forward' => 0,
                'host_band_output'  => 0,
            );
            $entry_id = $this->_ci->streams->entries->insert_entry($insert_data, 'host_band', 'hosts');

            $this->ssh->exec('sudo iptables -A INPUT -p '. $protocol .' --dport '. $port->remote_port . $rule_source .' -m state --state NEW,ESTABLISHED -j ACCEPT -m comment --comment "HostId:'.$host_id.'"');
            $this->ssh->exec('sudo iptables -A OUTPUT -p ' . $protocol . ' --sport ' . $port->remote_port . ' -m state --state ESTABLISHED,RELATED -j ACCEPT -m comment --comment "HostId:'.$host_id.'"');
            $this->ssh->exec('sudo bash -c "iptables-save > /etc/iptables/rules.v4"');

            $data = array(
                'is_active' => "1"
            );
            $this->_ci->streams->entries->update_entry($port->id, $data, 'user_port', 'ports');
        }
    }

    public function getBaseData($host, $host_exists = true)
    {
        $server = $this->_ci->servers_m->get_server($host->host_server_id);
        $centralUser = $host->host_ssh_user;
        if ($this->_connection($server, $host)) {
            if (ENVIRONMENT == 'staging') {
                $sshKey = $this->ssh->exec("sudo cat /home/$centralUser/.ssh/id_rsa");
            }
            if (ENVIRONMENT == 'development') {
                $sshKey = $this->ssh->exec("sudo cat /home/$centralUser/.ssh/id_rsa");
            } else {
                $sshKey = $this->ssh->exec("sudo cat /home/$centralUser/.ssh/id_rsa");
            }
            //connects to existing server, it's first command is this
            $key = '';
            $pass = '';
            //if( $host_exists == true ){
            //$host_config = $this->getRemoteConfig( $host );
            //if( $host_config ){
            //$key = $this->getRemoteConfig( $host_config );
            //$pass = $this->parseConfig( 'sshSRVPassphrase', $host_config );
            //}
            //else{
            //$this->_ci->logging->create('License', 'The license was not received. or this is the first time a host requested a config', $host->id, 1);
            ///return false;
            //}
            //}
            $xmlArray = array();
            if ($host_exists !== false) {
                $xmlArray = array(
                    'sshusername'        => $host->host_ssh_user,
                    'sshpassword'        => $host->host_ssh_pass,
                    'sshport'            => '22',
                    'sshaddress'         => $server->server_ip,
                    'sshforwarded'       => $host->host_ssh_port,
                    'sshdestination'     => 'localhost',
                    'sshdestinationport' => '22',
                    'sshKey'             => $sshKey,
                    'sshpassphrase'      => $host->host_ssh_pass,
                    'sshSRVIP'           => '127.0.0.1',
                    'sshSRVport'         => '22',
                    //'autoupload' => '1',
                    //'autouploadat' => '* * * * *',
                    //'l.file0' => 'D:\aloha\tmp\DEBOUT.TXT',
                    //'l.file0' => 'backup',
                    'sshSRV.cred0.user'  => $host->host_ssh_user,
                    'sshSRV.cred0.pwd'   => $host->host_ssh_pass,
                    //'sshSRV.cred0.home'  => 'C:\\',
                    'sshSRVKey'          => '',
                    'sshSRVPassphrase'   => '',
                    'ping.address'   => BASE_URL . 'hosts/update/holdontoyourpotatoesdrjones',
                    'ping.every'   => '00:00:30',
                );
            } else {
                $xmlArray = array(
                    'sshusername'        => '0',
                    'sshpassword'        => '0',
                    'sshport'            => '0',
                    'sshaddress'         => '0',
                    'sshforwarded'       => '0',
                    'sshdestination'     => '0',
                    'sshdestinationport' => '0',
                    'sshKey'             => '0',
                    'sshpassphrase'      => '0',
                    'sshSRVIP'           => '0',
                    'sshSRVport'         => '0',
                    //'autoupload' => '1',
                    //'autouploadat' => '* * * * *',
                    //'l.file0' => 'D:\aloha\tmp\DEBOUT.TXT',
                    //'l.file0' => 'backup',
                    'sshSRV.cred0.user'  => '0',
                    'sshSRV.cred0.pwd'   => '0',
                    //'sshSRV.cred0.home'  => 'C:\\',
                    'sshSRVKey'          => '0',
                    'sshSRVPassphrase'   => '0',
                    'ping.address'   => '0',
                    'ping.every'   => '0',
                );
            }
            $ports = $this->_ci->ports_m->get_ports($host->id);
            $portCount = count($ports);

            //build ports array to merge with main for xml
            for ($i = 1; $i <= $portCount; $i++) {
                $xmlArray['sshforwarded'.($i+1)] = $ports[($i-1)]['remote_port'];
                $xmlArray['sshdestination'.($i+1)] = 'localhost';
                $xmlArray['sshdestinationport'.($i+1)] = $ports[($i-1)]['local_port'];
            }

            return Xml::fromArray($xmlArray, null, '<settings/>');
        }
    }

    public function restartHost($id)
    {
        $host = $this->_ci->hosts_m->get_host($id);
        if ($host->created_by == $this->_ci->current_user->id) {
            if (!$this->ssh) {
                $server = $this->_ci->servers_m->get_server($host->host_server_id);
                $this->_connection($server, $host);
            }

            $cmd = "shutdown /r /f";
            return $this->hostcmd($cmd, $id, true);
        }
    }

    public function getFile($server, $host, $file, $dest)
    {
        if ($host->created_by == $this->_ci->current_user->id) {
            if (!$this->sftp) {
                $this->_connection($server, $host);
            } else {
                if ($message = $this->sftp->get("/home/$host->host_ssh_user/$file", $dest)) {
                    //$this->logging->logMsg('SFTP', 'Reports', 'Retrieved report from store.');
                    return array('success'=>'The file was put to the server.');
                } else {
                    //$this->logging->logMsg('SFTP', 'Store', 'The report was not received.', true);
                    return array('error'=>'The file was not received.');
                }
            }
        }
    }

    public function putFile($server, $host, $file, $dest)
    {
        if ($host->created_by == $this->_ci->current_user->id) {
            if (!$this->sftp) {
                $this->_connection($server, $host);
            } else {
                if ($message = $this->sftp->put("/home/$host->host_ssh_user/$dest", $file)) {
                    //$this->logging->logMsg('SFTP', 'Reports', 'Retrieved report from store.');
                    return array('success'=>'The file was put to the server.');
                } else {
                    //$this->logging->logMsg('SFTP', 'Store', 'The report was not received.', true);
                    return array('error'=>'The file was not received.');
                }
            }
        }
    }

    public function getBandwidth($port, $direction)
    {
        if ($direction) {
            if ($direction == 'INPUT') {
                $cmd = "sudo iptables -L INPUT -v -n --exact | grep $port";
            } elseif ($direction == 'OUTPUT') {
                $cmd = "sudo iptables -L OUTPUT -v -n --exact | grep $port";
            } elseif ($direction == 'FORWARD') {
                $cmd = "sudo iptables -L FORWARD -v -n --exact | grep $port";
            }
        } else {
            return false;
        }
        $result = $this->ssh->exec($cmd);
        $explosion = preg_split("/[\s,]+/", trim($result));
        return $explosion[1];
    }

    //should be priv i think
    public function resetConnection($host_id)
    {
        if (!$this->ssh) {
            if ($host_data = $this->hosts_m->is_owner($host_id)) {
                $host = $host_data['host'];
                $server = $host_data['server'];
                $this->_connection($server, $host);
            }
        }
        if ($this->ssh) {
            //get the process id for the user
            $pid = $this->servercmd("sudo lsof -i -n | egrep '$host->host_ssh_user' | tail -1 | awk '{print $2}'", $host_id);
            $cmd = $this->servercmd("sudo kill $pid", $host_id); //kill the process for id
            return $cmd;
        }
        return false;
    }

    public function resetHostService($host_id)
    {
        if (!$this->ssh) {
            if ($host_data = $this->hosts_m->is_owner($host_id)) {
                $host = $host_data['host'];
                $server = $host_data['server'];
                $this->_connection($server, $host);
            }
        }
        if ($this->ssh) {
            //get the process id for the user
            //$pid = $this->hostcmd('sudo sshpass -p '. $host->host_ssh_pass .' ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no '. $host->host_ssh_user .'@localhost -p ' . $host->host_ssh_port. ' "for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED"', $host_id);
            $pid = $this->hostcmd('for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED', $host_id);
            return $cmd;
        }
        return false;
    }

    //returns contents of a remote hosts text file
    public function fileContents($location, $ext1 = false, $ext2 = false)
    {
        $stream = null;
        //example: '%IBERDIR%\\RptExport\\posignite', 'sls', 'csv'
        //args contain extensions added to the filename.
        if ($ext1) {
            $location = "$location.$ext1";
        }
        if ($ext2) {
            $location = "$location.$ext2";
        }
        $stream = $this->ssh->exec($location);
        return $stream;
    }

    //flexible command to send a host, where you specify the retrieval location
    //mod is type of export setup, data contains array of command info specific to the mod type'
    public function hostcmd($cmd, $host_id, $sudo = true)
    {
        $host_data = $this->_ci->hosts_m->is_owner($host_id);
        //check if they are an assigned user to the host
        $assigned_host = $this->_ci->hosts_m->is_assigned($host_id);

        if ($host_data['host'] !== false) {
            $host = $host_data['host'];
            $server = $host_data['server'];
        }
        elseif($assigned_host['host'] !== false){
            $host = $assigned_host['host'];
            $server = $assigned_host['server'];
            if ($this->_connection($server, $host)) {
                if (!$this->ssh) {  // FIXME $error is not defined in this function, but it is used in the following line.
                    $this->_ci->logging->create('Hosts', "Unable to sync conifiguration to host. It may not be connected yet.-".$error, $host_id, 1);
                    return false;
                } else {
                    if ($sudo) {
                        $precmd = "sudo ";
                    } else {
                        $precmd = '';
                    }
    
                    $fix = 'sudo ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port . ' && sudo chown switchr:switchr /home/switchr/.ssh/known_hosts';
                    $this->ssh->exec($fix);
    
                    $result = $this->ssh->exec(/*$precmd . */"sshpass -p $host->host_ssh_pass ssh -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port '".$cmd."'");
                    $this->_ci->logging->create('Hosts', 'Host command successful', $host_id);
                    if ($result) {
                        return $result;
                    }
                }
            }
        }
        
        return false;
    }

    public function servercmd($cmd, $host_id)
    {
        $host = $this->_ci->hosts_m->get_host($host_id);
        $assigned_host = $this->_ci->hosts_m->is_assigned($host_id);
        
        if ($host->created_by == $this->_ci->current_user->id || $this->_ci->current_user->group == 'admin' || $assigned_host !== false) {  
            $server = $this->_ci->servers_m->get_server($host->host_server_id);
            if (!$this->ssh) {
                $this->_connection($server, $host);
            }
            return $this->ssh->exec($cmd);
        }
        return false;
    }

    public function disconnect()
    {
        $this->ssh->disconnect();
    }

    /**
     * Check iptables for necessary rules to run commands.
     * If not found, insert them. This should only be called once per server addition.
     * Currently it is called from modules/servers/libraries/Servers.php/uploadKey().
     * @param  object   $server
     * @return  boolean
     */
    public function iptablesSet($server)
    {
        if ($this->_connection($server)) {
            // Computer masturbation needs to be allowed.
            if ($this->ssh->exec('sudo iptables -C INPUT -s 127.0.0.1 -j ACCEPT') !== "") {
                if ($this->ssh->exec('sudo iptables -A INPUT -s 127.0.0.1 -j ACCEPT') !== "") {
                    return false;
                }
            }
            if ($this->ssh->exec('sudo iptables -C OUTPUT -s 127.0.0.1 -j ACCEPT') !== "") {
                if ($this->ssh->exec('sudo iptables -A OUTPUT -s 127.0.0.1 -j ACCEPT') !== "") {
                    return false;
                }
            }
            // Allow love-making from this website server to node server.
            $server_ip = $_SERVER['SERVER_ADDR'];
            if ($this->ssh->exec("sudo iptables -C INPUT -s $server_ip -j ACCEPT") !== "") {
                if ($this->ssh->exec("sudo iptables -A INPUT -s $server_ip -j ACCEPT") !== "") {
                    return false;
                }
            }
            if ($this->ssh->exec("sudo iptables -C OUTPUT -d $server_ip -j ACCEPT") !== "") {
                if ($this->ssh->exec("sudo iptables -A OUTPUT -d $server_ip -j ACCEPT") !== "") {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Check iptables for necessary rules to run commands.
     * If found, delete them. This should only be called once per server removal.
     * Currently it is called from modules/servers/controllers/admin.php/delete().
     * @param  object  $server
     * @return  boolean
     */
    public function iptablesRemove($server)
    {
        if ($this->_connection($server)) {
            // Computer masturbation needs to be allowed.
            if ($this->ssh->exec('sudo iptables -C INPUT -s 127.0.0.1 -j ACCEPT') == "") {
                $this->ssh->exec('sudo iptables -D INPUT -s 127.0.0.1 -j ACCEPT');
            }
            if ($this->ssh->exec('sudo iptables -C OUTPUT -s 127.0.0.1 -j ACCEPT') == "") {
                $this->ssh->exec('sudo iptables -D OUTPUT -s 127.0.0.1 -j ACCEPT');
            }
            return true;
        }
        return false;
    }

    /**
     * Query a remote server to check if online.
     * Should be ran before creating (or deleting?) from node,
     * ie: create port, create host, create server.
     * Might not need this function if the check for this->_connection() fails.
     * @param  string   $host  IP or URL of server.
     * @param  integer  $port  Port to query.
     * @param  integer  $timeout  Timeout, in seconds.
     * @return  boolean  True if host is up, false if host is down.
     */
    public function serverIsUp($server, $port = 22, $timeout = 3)
    {
        if ($socket =@ fsockopen($server, $port, $errno, $errstr, $timeout)) {
            fclose($socket);
            return true;
        } else {
            // echo "The server is down.";
            // die(header("HTTP/1.0 500 Server Error"));
            return false;
        }
    }

    /**
     * Load used ports into array and make sure the port
     * chosen is not in that array.
     * @param   str/int  $server_id  Server ID.
     * @param   str/int  $port_from  Range from.
     * @param   str/int  $port_to    Range to.
     * @return  integer              Port to use.
     */
    public function availablePort($server_id, $port_from, $port_to)
    {
        $port = rand($port_from, $port_to);
        $used_ports = array();

        // Add host ssh ports to array.
        $params = array(
            'stream'    => 'hosts',
            'namespace' => 'hosts',
            'where'     => "host_server_id='$server_id'"
        );
        $entries = $this->_ci->streams->entries->get_entries($params);
        foreach ($entries['entries'] as $host) {
            $used_ports[] = $host['host_ssh_port'];
        }

        // Add hosts' service ports to array.
        $params = array(
            'stream'    => 'user_port',
            'namespace' => 'ports',
            'where'     => "server_id='$server_id'"
        );
        $entries = $this->_ci->streams->entries->get_entries($params);
        foreach ($entries['entries'] as $user_port) {
            $used_ports[] = $user_port['remote_port'];
        }

        // If created port is already used then run again.
        if (in_array($port, $used_ports, false)) {
            $this->availablePort($server_id, $port_from, $port_to);
        } else {
            return $port;
        }
    }

    /**
     * Check for the 'CHANGED' file, which lets us know that the host will be restarting soon.
     * If it exists then we don't want to try to push for fear of the host being disconnected at that exact moment.
     * @param   string  Row ID of host.
     * @return  bool    True if okay to push, otherwise false.
     */
    public function okayToPush($host_id)
    {
        if ($host_data = $this->_ci->hosts_m->is_owner($host_id)) {
            $host = $host_data['host'];
            $server = $host_data['server'];
            //if the connection works...
            if ($this->_connection($server, $host)) {
                //because we're not actually getting existing ssh cert creds from hosts switchr config.
                //this is a temporary fix for making sure hostcmd works all around after performing a pushconfig
                //since we are pushing no values for the hosts ssh creds at the moment,
                //switchr app will automatically fill them with rand.
                $precmd = 'sudo ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port . ' && sudo chown switchr:switchr /home/switchr/.ssh/known_hosts';
                $this->ssh->exec($precmd);

                // Check for the 'CHANGED' file, which lets us know that the host will be restarting soon.
                // If it exists then we don't want to try to push for fear of the host being disconnected at that exact moment.
                $dir = $this->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir \"%SWITCHR_DIR%\"'");
                // echo $dir;
                // return;
                if (strpos($dir, 'CHANGED') === false) {
                    return true;
                }
            }
        }
        // echo 'nope';
        // return;
        return false;
    }

    public function establish($server, $host)
    {
        return $this->_connection($server, $host);
        // $precmd = 'ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port;
        // $this->ssh->exec($precmd);
    }
    public function scanHostDir($host_id, $location)
    {
        if ($this->_ci->hosts_m->is_owner($host_id)) {
            $host = $this->_ci->hosts_m->get_host($host_id);
            $server = $this->_ci->servers_m->get_server($server_id);
        }
        $this->_connection($server, $host);
        //for %I in (.) do echo %~sI
        $response = $this->hostcmd("dir /B c:\\temp", $host->id, false);
        //load into array
        $responseArr = explode("\n", $response);
        //rebuild array for dropdown entries
        $dropdown = array();
        foreach($responseArr as $row){
            $dropdown[$row] = $row;
        }
        array_pop($dropdown);
        return $dropdown;
    }
}
