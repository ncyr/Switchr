<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Backup_lib
{
    public $pushConfig;
    public $addShellUser;
    public $switchPort;
    public $getBaseData;

    protected $_ci;
    protected $_connection;
    protected $ssh;
    protected $sftp;

    public function __construct($host, $server)
    {
        $this->_ci = & get_instance();
        $this->_ci->load->model('servers/servers_m');
        $this->_ci->load->model('hosts/hosts_m');
        $this->_ci->load->model('ports/ports_m');
        $this->_ci->load->library('Logging');

        $this->_ci->load->library('ConfigEncoder');
        //$this->_ci->load->library('encrypt');
    }

    private function _connection($server, $host = false)
    {
        if (!$host) {
            $host->id = 0;
        }
        //set_error_handler('errorHandler', E_USER_NOTICE);
        $path = '/addons/shared_addons/libraries/phpseclib';
        // set_include_path(get_include_path().$path);
        set_include_path($_SERVER['DOCUMENT_ROOT'].$path);

        include_once('Net/SSH2.php');
        include_once('Net/SFTP.php');
        include_once('Crypt/RSA.php');
        include_once('Crypt/AES.php');
        include_once('Crypt/Hash.php');
        include_once('Math/BigInteger.php');

        define('NET_SSH2_LOGGING', 2);

        $this->ssh = new Net_SSH2($server->server_ip);
        $this->sftp = new Net_SFTP($server->server_ip);

        $rsa = new Crypt_RSA();

        $site = SITE_REF;

        $privatekey = file_get_contents("../ssl.switchr/$site/$server->id");
        $rsa->setPassword($server->server_key_password);

        if ($rsa->loadKey(trim($privatekey)) === false) {
            $this->_ci->logging->create('Hosts', 'Security Key Failed to Load', $host->id, 1);
            return false;
        } else {
            $this->_ci->logging->create('Hosts', 'Security Key Loaded...', $host->id);
        }
        if ($this->ssh->login($server->server_username, $rsa) === false && $this->sftp->login($server->server_username, $rsa === false)) {
            $this->_ci->logging->create('Hosts', 'Secure Login Unsuccessful', 1, 1, $host->id, 1);
            $this->_ci->logging->create('Hosts', 'There were errors connecting the host to our server: #'. $server->id . '-' . $server->server_name . ', please contact our support department', 2, 1, $this->host_id, 1);
            return false;
        } else {
            $this->_ci->logging->create('Hosts', 'Secure Login Successful', 1, 1, $host->id);
            return true;
        }
        return false;
    }

    /**
     * Create the backup config on host, delete the backup index if it exists, and restart the service.
     * @param   string  $backup_id  Row ID of backup.
     * @param   string  $host_id    Row ID of host.
     * @param   string  $type       Type of backup: ftp, sftp, awss3, local.
     * @return  bool                True or false.
     */
    public function pushConfig($backup_id, $host_id, $type)
    {
        $host = $this->_ci->hosts_m->get_host($host_id);
        $server = $this->_ci->servers_m->get_server($host->host_server_id['id']);

        //if the connection works...
        if ($this->_connection($server, $host)) {
            $config = $this->_ci->configencoder->load()->fromString($this->getBaseData($backup_id, $type));
            $config = $config->encrypt()->asBase64();

            if ($type == 'ftp' || $type == 'sftp') {
                //usr, pass, port, cfg
                //$cmd = "./pushbackupconfig ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port . " " . $config;
                $cmd1 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo ".$config.">%~sISwitchrf.p'";
                $cmd2 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do del %~sIindex.xml'";
                $cmd3 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED'";
            } elseif ($type == 'awss3') {
                //$cmd = "./pushs3config ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port . " " . $config;
                $cmd1 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo ".$config.">%~sISwitchrs3.p'";
                $cmd2 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do del %~sIindexs3.xml'";
                $cmd3 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED'";
            } elseif ($type == 'local') {
                //$cmd = "./pushconfig ". $host->host_ssh_user . " " . $host->host_ssh_pass . " " . $host->host_ssh_port . " " . $config;
            }

            $precmd = 'ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port;
            $this->ssh->exec($precmd);
            $this->_ci->logging->create('Hosts', "precmd: ".$this->ssh->getStdError(), $host_id, 1);

            $this->ssh->exec($cmd1);
            $error1 = $this->ssh->getStdError();
            $this->ssh->exec($cmd2);
            $error2 = $this->ssh->getStdError();
            $this->ssh->exec($cmd3);
            $error3 = $this->ssh->getStdError();
            // Check for errors from the ssh library.
            if ($error1 && substr($error1, 0, 7) != 'Warning') {
                $this->_ci->logging->create('Hosts', "Unable to sync backup to host: cmd1 ".$error1, $host_id, 1);
                return false;
            }
            if ($error2) {
                $this->_ci->logging->create('Hosts', "Backup index not found. This may not be an error.", $host_id, 1);
            }
            if ($error3 && substr($error3, 0, 7) != 'Warning') {
                $this->_ci->logging->create('Hosts', "Unable to sync backup to host: cmd3 ".$error3, $host_id, 1);
                return false;
            }
            $this->_ci->logging->create('Hosts', 'Backup pushConfig() successful', $host_id, 0);
            return true;
        } else {
            $this->_ci->logging->create('Hosts', 'Backup pushConfig() connection error.', $host_id, 1);
            return false;
        }
    }

    /**
     * Delete the backup configuration and index of files/folders on client then restart Switchr.
     * @param   string/int  $backup_id  Database ID of backups_backup_dest row.
     * @return  boolean                 True if successful, false if failed.
     */
    public function remConfig($backup_id)
    {
        $backup = $this->_ci->backups_m->get_backup($backup_id);
        $host = $this->_ci->hosts_m->get_host($backup->backup_dest_host_id);
        $server = $this->_ci->servers_m->get_server($host->host_server_id['id']);
        //if the connection works...

        if ($this->_connection($server, $host)) {
            //temporary fix
            $cmd = 'sudo ssh-keygen -f "/root/.ssh/known_hosts" -R [localhost]:'.$host->host_ssh_port;
            $this->ssh->exec($cmd);
            $type = $backup->backup_dest_type;
            if ($type == 'ftp' || $type == 'sftp') {
                //usr, pass, port, cfg
                $cmd1 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do del %~sISwitchrf.p'";
                $cmd2 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do del %~sIindex.xml'";
                $cmd3 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED'";
            } elseif ($type == 'awss3') {
                $cmd1 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do del %~sISwitchrs3.p'";
                $cmd2 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do del %~sIindexs3.xml'";
                $cmd3 = "sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'for %I in (\"%SWITCHR_DIR%\") do echo STOP>%~sICHANGED'";
            } elseif ($type == 'local') {
                // Commands for local. Local is currently unused.
            }
            $this->ssh->exec($cmd1);
            $error1 = $this->ssh->getStdError();
            $this->ssh->exec($cmd2);
            $error2 = $this->ssh->getStdError();
            $this->ssh->exec($cmd3);
            $error3 = $this->ssh->getStdError();
            if ($error1 && substr($error1, 0, 7) != 'Warning') {
                $this->_ci->logging->create('Hosts', "remConfig(): Unable to sync backup to host: cmd1 ".$error1, $host_id, 1);
                return false;
            }
            if ($error2) {
                $this->_ci->logging->create('Hosts', "remConfig(): Unable to sync backup to host: cmd2 ".$error2, $host_id, 1);
            }
            if ($error3 && substr($error3, 0, 7) != 'Warning') {
                $this->_ci->logging->create('Hosts', "remConfig(): Unable to sync backup to host: cmd3 ".$error3, $host_id, 1);
                return false;
            }
            $this->_ci->logging->create('Hosts', 'Backup remConfig() successful', $host->id, 0);
            return true;
        } else {
            $this->_ci->logging->create('Hosts', 'Backup remConfig() connection error.', $host_id, 1);
            return false;
        }
    }

    public function getBaseData($id, $type)
    {
        $backup = $this->_ci->backups_m->get_backup($id);

        $host = $this->_ci->hosts_m->get_host($backup->backup_dest_host_id);
        $server = $this->_ci->servers_m->get_server($host->host_server_id);
        $centralUser = $host->host_ssh_user;


        if (!$this->ssh) {
            $this->_connection($server, $host);
        }

        if (ENVIRONMENT == 'staging') {
            $sshKey = $this->ssh->exec("sudo cat /home/$centralUser/.ssh/id_rsa");
        }
        if (ENVIRONMENT == 'development') {
            $sshKey = $this->ssh->exec("sudo cat /home/$centralUser/.ssh/id_rsa");
        } else {
            $sshKey = $this->ssh->exec("sudo cat /home/$centralUser/.ssh/id_rsa");
        }

        if ($type == 'ftp' || $type == 'sftp') {
            $xmlArray = array(
                'protocol'      => $backup->backup_dest_type,
                'uploadat'      => $backup->backup_dest_uploadat,
                'hostname'      => $backup->backup_dest_hostname,
                'username'      => $backup->backup_dest_username,
                'password'      => $backup->backup_dest_password,
                'port'          => $backup->backup_dest_port,
                'passv'         => $backup->backup_dest_passive['key'], //TODO Try this one so that config isn't: '0PassivePassive'
                // 'passv'         => $backup->backup_dest_passive,
                // 'l.file0'       => $backup->backup_dest_source,  //TODO Comment out for multiple selection.
                // 'r.file0'       => $backup->backup_dest_dest,  //TODO Comment out for multiple selection.
                // 'user_cert'     => $this->_ci->configencoder->decrypt($backup->backup_dest_ssh_key),
                // 'user_cert_pwd' => $this->_ci->configencoder->decrypt($backup->backup_dest_ssh_password),
                'user_cert'     => $backup->backup_dest_ssh_key,
                'user_cert_pwd' => $backup->backup_dest_ssh_password,
            );

            // This will add the array of files/folders to backup.
            // Multiple source files/folders; one destination folder.
            $selected_items = json_decode($backup->backup_dest_source, false);
            $selected_items_dest_path = json_decode($backup->backup_dest_dest, false);
            for ($i = 0; $i < count($selected_items); $i++) {
                $xmlArray["l.file{$i}"] = $selected_items[$i];
                $xmlArray["r.file{$i}"] = $selected_items_dest_path[$i];
            }

            // $backups = $this->_ci->backups_m->getBackups($id);

            // $backupCount = count($backups);

            //build ports array to merge with main for xml
            // for ($i = 1; $i <= $backupCount; $i++) {
            //     $xmlArray['l.file'.($i+1)] = $backups[($i-1)]['backup_dest_source'];
            //     $xmlArray['r.file'.($i+1)] = $backups[($i-1)]['backup_dest_dest'];
            // }
        } elseif ($type == "awss3") {
            $xmlArray = array(
                'protocol'       => "awss3",
                'uploadat'       => $backup->backup_dest_uploadat,
                'bucketname'     => $backup->backup_s3_bucketname,
                'awsaccesskeyid' => $backup->backup_s3_awsaccesskeyid,
                'awssecretkey'   => $backup->backup_s3_awssecretkey,
                'serviceurl'     => $backup->backup_s3_serviceurl['value'],
                'regionendpoint' => $backup->backup_s3_regionendpoint,
                // 'l.file0'        => $backup->backup_dest_source,
            );

            // This will add the array of files/folders to backup.
            // Multiple source files/folders; one destination folder.
            $selected_items = json_decode($backup->backup_dest_source, false);
            for ($i = 0; $i < count($selected_items); $i++) {
                $xmlArray["l.file{$i}"] = $selected_items[$i];
            }

            // if (is_array($backup->backup_dest_source)) {
            //     if (count($backup->backup_dest_source) >= 1) {
            //         $xmlArray['l.file0'] = $backup[0]['backup_dest_source'];
            //         $backupCount = count($backup);
            //
            //         //build ports array to merge with main for xml
            //         for ($i = 1; $i <= $portCount; $i++) {
            //             $xmlArray['l.file'.($i+1)] = $backup[($i-1)]['backup_dest_source'];
            //         }
            //     }
            // }
        } elseif ($type == "local") {
            $xmlArray =array(
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
                'autoupload'         => $backup->backup_dest_status,
                'autouploadat'       => $backup->backup_dest_uploadat,
                'l.file0'            => $backup->backup_dest_source,
                'r.file0'            => '/home/' . $host->host_ssh_user . '/' .$backup->backup_dest_dest,
                'sshSRV.cred0.user'  => $host->host_ssh_user,
                'sshSRV.cred0.pwd'   => $host->host_ssh_pass,
                'sshSRV.cred0.home'  => $backup->backup_dest_home ?: 'C:\\'
            );
            if (is_array($backup->backup_dest_source)) {
                if (count($s3->backup_dest_source) >= 1) {
                    $xmlArray['l.file0'] = $s3[0]['backup_dest_source'];
                    $backupCount = count($s3);

                    //build ports array to merge with main for xml
                    for ($i = 1; $i <= $portCount; $i++) {
                        $xmlArray['l.file'.($i+1)] = $s3[($i-1)]['backup_dest_source'];
                    }
                }
            }
        }
        $this->_ci->load->helper('Xml');
        return Xml::fromArray($xmlArray, null, '<settings/>');
    }
}
