<?php defined('BASEPATH') or exit('No direct script access allowed');

class Backups extends Public_Controller
{
    protected $section = 'backups';

    public function __construct()
    {
        parent::__construct();

        $this->load->language('backups');
        $this->load->library('Logging');
        $this->load->model('backups_m');
        $this->load->model('hosts/hosts_m');
        $this->load->model('servers/servers_m');
        //$this->load->model('payignite/payignite_m', 'Payignite');
        //$this->load->library('backups');
        $this->load->driver('Streams');
        $this->load->library('Connect');
        $this->load->library('ftp');
        $this->load->library('Ftp_Sftp');
        $this->load->library('s3');
    }


    public function index($id = false)
    {
        // If there is no backup id then view all backups belonging to user.
        if (!$id) {
            $this->template
                    ->title($this->module_details['name'])
                    ->set('user_id', $this->current_user->id)
                    ->append_js('module::backups.js')
                    ->build('backups_all');

            // View backup(s) belonging to one host.
        } else {
            $this->template
                    ->title($this->module_details['name'])
                    ->set('host_id', $id)
                    ->append_js('module::backups.js')
                    ->build('backups_index');
        }
    }

    public function create($type, $host_id)
    {
        if ($this->input->post()) {
            $host_id = $this->input->post('backup_dest_host_id');
            if (!$this->backups_m->is_owner($host_id)) {
                exit('You do not have permission. IP has been recorded for evaluation.');
            }

            $backup_dest_source_raw = json_decode($this->input->post('backup_dest_source'));
            $backup_dest_source = array();
            $backup_dest_dest   = array();
            foreach ($backup_dest_source_raw as $path => $recursive) {
                $source_path = base64_decode($path);
                $end_dest_path = str_replace(array(':','\\',' '), array('','/','_'), $source_path);
                if ($recursive->recursive === true) {
                    $source_path .= '\.';
                } elseif ($recursive->recursive === false) {
                    // If not recursive then all we can do is include all files, no folders.
                    $source_path .= '\*.*';
                }
                $backup_dest_source[] = $source_path;
                $backup_dest_dest[]   = $this->input->post('backup_dest_dest').'/'.$end_dest_path;
            }

            switch ($type) {
                // This will handle both FTP and SFTP,
                // but for edit() FTP and SFTP are split up.
                case 'ftp':
                    $entry_data = array(
                        'backup_dest_host_id'      => $this->input->post('backup_dest_host_id'),
                        'backup_dest_type'         => $this->input->post('backup_dest_type'),
                        'backup_dest_name'         => $this->input->post('backup_dest_name'),
                        'backup_dest_uploadat'     => $this->input->post('backup_dest_uploadat'),
                        'backup_dest_username'     => $this->input->post('backup_dest_username'),
                        'backup_dest_password'     => $this->input->post('backup_dest_password'),
                        'backup_dest_hostname'     => $this->input->post('backup_dest_hostname'),
                        'backup_dest_port'         => $this->input->post('backup_dest_port'),
                        'backup_dest_passive'      => $this->input->post('backup_dest_passive'),
                        'backup_dest_source'       => json_encode($backup_dest_source),
                        // 'backup_dest_source'       => $this->input->post('backup_dest_source'),
                        // 'backup_dest_dest'         => $this->input->post('backup_dest_dest'),
                        'backup_dest_dest'         => json_encode($backup_dest_dest),
                        'backup_dest_ssh_key'      => $this->input->post('backup_dest_ssh_key'),
                        'backup_dest_ssh_password' => $this->input->post('backup_dest_ssh_password'),
                        'backup_dest_status'       => $this->input->post('backup_dest_status'),
                    );
                    $this->streams->entries->insert_entry($entry_data, 'backup_dest', 'backups');
                    break;
                case 'awss3':
                    $entry_data = array(
                        'backup_dest_host_id'      => $this->input->post('backup_dest_host_id'),
                        'backup_dest_type'         => $this->input->post('backup_dest_type'),
                        'backup_dest_name'         => $this->input->post('backup_dest_name'),
                        'backup_dest_uploadat'     => $this->input->post('backup_dest_uploadat'),
                        'backup_dest_source'       => json_encode($backup_dest_source),
                        // 'backup_dest_source'       => $this->input->post('backup_dest_source'),
                        'backup_s3_bucketname'     => $this->input->post('backup_s3_bucketname'),
                        'backup_s3_awsaccesskeyid' => $this->input->post('backup_s3_awsaccesskeyid'),
                        'backup_s3_awssecretkey'   => $this->input->post('backup_s3_awssecretkey'),
                        'backup_s3_regionendpoint' => $this->input->post('backup_s3_regionendpoint'),
                    );

                    // Set the AWS endpoint (URL) according to the region specified.
                    // This must be set correctly for AWS commands to work.
                    switch ($entry_data['backup_s3_regionendpoint']) {
                        case 'us-east-2':
                            $entry_data['backup_s3_serviceurl'] = 's3-us-east-2.amazonaws.com';
                            break;
                        case 'us-west-1':
                            $entry_data['backup_s3_serviceurl'] = 's3-us-west-1.amazonaws.com';
                            break;
                        case 'us-west-2':
                            $entry_data['backup_s3_serviceurl'] = 's3-us-west-2.amazonaws.com';
                            break;
                        default:
                            $entry_data['backup_s3_regionendpoint'] = 'us-east-1';
                            $entry_data['backup_s3_serviceurl'] = 's3.amazonaws.com';
                            break;
                    }
                    // Create the bucket and enable versioning before the database insert
                    // because the post-insert hook will push the data to the host and we want it
                    // all setup by then.
                    $data['key']    = $entry_data['backup_s3_awsaccesskeyid'];
                    $data['secret'] = $entry_data['backup_s3_awssecretkey'];
                    $data['region'] = $entry_data['backup_s3_regionendpoint'];
                    $bucket_name    = $entry_data['backup_s3_bucketname'];
                    $s3 = new $this->s3($data);
                    $s3->createBucket($bucket_name);
                    // Insert the entry to the database and push config to host.
                    $this->streams->entries->insert_entry($entry_data, 'backup_dest', 'backups');
                    break;
                default:
                    die(header('HTTP/1.0 500 Server error'));
                    break;
            }
            redirect('/backups/index/'.$host_id, 'refresh');
        }

        //$backups_available = $this->Payignite->backupsAvailable();

        switch ($type) {
            // case 'local':
            //     if (2==2) {
            //         $view = 'backups_create_local';
            //         $title = 'Create Remote Backup';
            //     } else {
            //         exit("You must <a href='/payignite'>purchase more</a> of this type of backup.");
            //     }
            //     break;
            case 'ftp':
                if (2==2) {
                    $view = 'backups_create_ftp';
                    $title = 'Create FTP/SFTP Backup';
                } else {
                    exit("You must <a href='/payignite'>purchase more</a> of this type of backup.");
                }
                break;
            case 'awss3':
                if (2==2) {
                    $view = 'backups_create_s3';
                    $title = 'Create Amazon S3 Backup';
                } else {
                    //redirect('/payignite/edit');
                    exit("You must <a href='/payignite'>purchase more</a> of this type of backup.");
                }
                break;
            default:
                exit('You must select a backup type.');
        }
        $this->template
                ->title($title)
                ->set('host_id', $host_id)
                ->set('type', $type)
                ->append_js('module::jquery.croneditor.js')
                // ->append_js('module::jquery-ui.js')
                // ->append_css('module::jquery-ui.css')
                ->append_js('module::backups.js')
                ->build($view);
    }

    public function edit($type, $id)
    {
        if ($this->input->post()) {
            $host_id = $this->input->post('backup_dest_host_id');
            if (!$this->backups_m->is_owner($host_id)) {
                exit('You do not have permission. IP has been recorded for evaluation.');
            }

            $backup_dest_source_raw = json_decode($this->input->post('backup_dest_source'));
            $backup_dest_source = array();
            $backup_dest_dest   = array();
            foreach ($backup_dest_source_raw as $path => $recursive) {
                $source_path = base64_decode($path);
                $end_dest_path = str_replace(array(':','\\',' '), array('','/','_'), $source_path);
                if ($recursive->recursive === true) {
                    $source_path .= '\.\*';
                } elseif ($recursive->recursive === false) {
                    // If not recursive then all we can do is include all files, no folders.
                    $source_path .= '\*';
                }
                $backup_dest_source[] = $source_path;
                $backup_dest_dest[]   = $this->input->post('backup_dest_dest').'/'.$end_dest_path;
            }

            switch ($type) {
                case 'ftp':
                    $entry_data = array(
                        'backup_dest_host_id'      => $this->input->post('backup_dest_host_id'),
                        'backup_dest_type'         => $this->input->post('backup_dest_type'),
                        'backup_dest_name'         => $this->input->post('backup_dest_name'),
                        'backup_dest_uploadat'     => $this->input->post('backup_dest_uploadat'),
                        'backup_dest_username'     => $this->input->post('backup_dest_username'),
                        'backup_dest_password'     => $this->input->post('backup_dest_password'),
                        'backup_dest_hostname'     => $this->input->post('backup_dest_hostname'),
                        'backup_dest_port'         => $this->input->post('backup_dest_port'),
                        'backup_dest_passive'      => $this->input->post('backup_dest_passive'),
                        // 'backup_dest_source'       => json_encode($backup_dest_source),
                        'backup_dest_source'       => json_encode($backup_dest_source),
                        'backup_dest_dest'         => json_encode($backup_dest_dest),
                        'backup_dest_ssh_key'      => $this->input->post('backup_dest_ssh_key'),
                        'backup_dest_ssh_password' => $this->input->post('backup_dest_ssh_password'),
                        'backup_dest_status'       => $this->input->post('backup_dest_status'),
                    );
                    $this->streams->entries->update_entry($id, $entry_data, 'backup_dest', 'backups');
                    break;
                case 'sftp':
                    $entry_data = array(
                        'backup_dest_host_id'      => $this->input->post('backup_dest_host_id'),
                        'backup_dest_type'         => $this->input->post('backup_dest_type'),
                        'backup_dest_name'         => $this->input->post('backup_dest_name'),
                        'backup_dest_uploadat'     => $this->input->post('backup_dest_uploadat'),
                        'backup_dest_username'     => $this->input->post('backup_dest_username'),
                        'backup_dest_password'     => $this->input->post('backup_dest_password'),
                        'backup_dest_hostname'     => $this->input->post('backup_dest_hostname'),
                        'backup_dest_port'         => $this->input->post('backup_dest_port'),
                        'backup_dest_passive'      => $this->input->post('backup_dest_passive'),
                        // 'backup_dest_source'       => json_encode($backup_dest_source),
                        'backup_dest_source'       => json_encode($backup_dest_source),
                        'backup_dest_dest'         => json_encode($backup_dest_dest),
                        'backup_dest_ssh_key'      => $this->input->post('backup_dest_ssh_key'),
                        'backup_dest_ssh_password' => $this->input->post('backup_dest_ssh_password'),
                        'backup_dest_status'       => $this->input->post('backup_dest_status'),
                    );
                    $this->streams->entries->update_entry($id, $entry_data, 'backup_dest', 'backups');
                    break;
                case 'awss3':
                    $entry_data = array(
                        'backup_dest_host_id'      => $this->input->post('backup_dest_host_id'),
                        'backup_dest_type'         => $this->input->post('backup_dest_type'),
                        'backup_dest_name'         => $this->input->post('backup_dest_name'),
                        'backup_dest_uploadat'     => $this->input->post('backup_dest_uploadat'),
                        'backup_dest_source'       => json_encode($backup_dest_source),
                        // 'backup_dest_source'       => $this->input->post('backup_dest_source'),
                        //'backup_s3_bucketname'     => $this->input->post('backup_s3_bucketname'),
                        //'backup_s3_awsaccesskeyid' => $this->input->post('backup_s3_awsaccesskeyid'),
                        //'backup_s3_awssecretkey'   => $this->input->post('backup_s3_awssecretkey'),
                        //'backup_s3_regionendpoint' => $this->input->post('backup_s3_regionendpoint'),
                    );
                    // Set the AWS endpoint (URL) according to the region specified.
                    // This must be set correctly for AWS commands to work.
                    // switch ($entry_data['backup_s3_regionendpoint']) {
                    //     case 'us-east-2':
                    //         $entry_data['backup_s3_serviceurl'] = 's3.us-east-2.amazonaws.com';
                    //         break;
                    //     case 'us-west-1':
                    //         $entry_data['backup_s3_serviceurl'] = 's3-us-west-1.amazonaws.com';
                    //         break;
                    //     case 'us-west-2':
                    //         $entry_data['backup_s3_serviceurl'] = 's3-us-west-2.amazonaws.com';
                    //         break;
                    //     default:
                    //         $entry_data['backup_s3_regionendpoint'] = 'us-east-1';
                    //         $entry_data['backup_s3_serviceurl'] = 's3.amazonaws.com';
                    //         break;
                    // }
                    $this->streams->entries->update_entry($id, $entry_data, 'backup_dest', 'backups');
                    break;
                default:
                    die(header('HTTP/1.0 500 Server error'));
                    break;
            }
            redirect('/backups/index/'.$host_id, 'refresh');
        }

        //$backups_available = $this->Payignite->backupsAvailable();
        $backup = $this->streams->entries->get_entry($id, 'backup_dest', 'backups');

        switch ($type) {
            case 'ftp':
                if (2==2) {
                    $view = 'backups_edit_ftp';
                    $title = 'Edit FTP Backup';
                } else {
                    exit("You must <a href='/payignite'>purchase more</a> of this type of backup.");
                }
                break;
            case 'sftp':
                if (2==2) {
                    $view = 'backups_edit_sftp';
                    $title = 'Edit SFTP Backup';
                } else {
                    exit("You must <a href='/payignite'>purchase more</a> of this type of backup.");
                }
                break;
            case 'awss3':
                if (2==2) {
                    $view = 'backups_edit_s3';
                    $title = 'Edit Amazon S3 Backup';
                } else {
                    //redirect('/payignite/edit');
                    exit("You must <a href='/payignite'>purchase more</a> of this type of backup.");
                }
                break;
            default:
                exit('You must select a backup type.');
        }
        $this->template
                ->title($title)
                ->set('host_id', $backup->backup_dest_host_id)
                ->set('type', $type)
                ->set('backup_id', $id)
                ->set('backup_dest_name', $backup->backup_dest_name)
                ->set('backup_dest_uploadat', $backup->backup_dest_uploadat)
                ->set('backup_dest_username', $backup->backup_dest_username)
                ->set('backup_dest_password', $backup->backup_dest_password)
                ->set('backup_dest_hostname', $backup->backup_dest_hostname)
                ->set('backup_dest_port', $backup->backup_dest_port)
                ->set('backup_dest_passive', $backup->backup_dest_passive['key'])
                ->set('backup_dest_source', $backup->backup_dest_source)
                // Since backup_dest_dest is an auto-generated array, we will not include it in the edit form.
                // The user will have to select it again, for now.
                // ->set('backup_dest_dest', $backup->backup_dest_dest)
                ->set('backup_dest_ssh_key', $backup->backup_dest_ssh_key)
                ->set('backup_dest_ssh_password', $backup->backup_dest_ssh_password)
                ->set('backup_dest_status', $backup->backup_dest_status['key'])
                ->set('backup_s3_bucketname', $backup->backup_s3_bucketname)
                ->set('backup_s3_awsaccesskeyid', $backup->backup_s3_awsaccesskeyid)
                ->set('backup_s3_awssecretkey', $backup->backup_s3_awssecretkey)
                ->set('backup_s3_serviceurl', $backup->backup_s3_serviceurl['key'])
                ->set('backup_s3_regionendpoint', $backup->backup_s3_regionendpoint)
                ->append_js('module::jquery.croneditor.js')
                ->append_js('module::backups.js')
                // ->append_js('module::jquery-ui.js')
                // ->append_css('module::jquery-ui.css')
                ->build($view);
    }

    /**
     * Delete the backup config from the host (remConfig()).
     * Delete the bucket from S3 and the backup from the database.
     * @param  string  $id  Row ID of backup.
     */
    public function delete($id, $redirect = true)
    {
        $host_id = null;
        if ($this->input->post()) {
            $host_id = $this->input->post('backup_dest_host_id');
            if (!$this->backups_m->is_owner($host_id)) {
                exit('You do not have permission. IP has been recorded for evaluation.');
            }
        }

        if ($this->backups_m->remConfig($id)) {
            // Get data from the database.
            $backup = $this->streams->entries->get_entry($id, 'backup_dest', 'backups', false);
            $host_id = $backup->backup_dest_host_id;
            // Delete the bucket from S3.
            if ($backup->backup_dest_type == 'awss3') {
                $data['key']    = $backup->backup_s3_awsaccesskeyid;
                $data['secret'] = $backup->backup_s3_awssecretkey;
                $data['region'] = $backup->backup_s3_regionendpoint;
                $bucket_name = $backup->backup_s3_bucketname;
                // We need to wait until the host service has restarted;
                // this will ensure that no new files are pushed to the S3 bucket and
                // bucket removal will not fail due to that.
                while (!$this->connect->okayToPush($host_id)) {
                    sleep(3);
                }
                sleep(3);
                $s3 = new $this->s3($data);
                $s3->deleteBucket($bucket_name);
            }
            // Delete from database
            $this->streams->entries->delete_entry($id, 'backup_dest', 'backups');
        }

        if ($redirect) {
            redirect('/backups/index/'.$host_id, 'refresh');
        }
    }

    /**
     * Establish connection to SFTP server.
     * To be used with AJAX.
     */
    public function sftpConnect()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $hostname = $this->input->post('hostname');
        $port     = $this->input->post('port');
        $key      = $this->input->post('key');
        $key_password = $this->input->post('key_password');

        $this->sftp = new $this->ftp_sftp($hostname, $port, $username, $password, $key, $key_password);
        // print_r($this->sftp);
        // print_r($this->sftp->listDirs('.'));
        // die;
        if ($this->sftp === false) {
            echo 'false';
        } else {
            echo json_encode(array('dirs' => $this->sftp->listDirs('.'), 'cwd' => $this->sftp->pwd()));
        }
        // ftp_close($this->ftp);
    }

    /**
     * Make new SFTP directory.
     * To be used with AJAX..
     */
    public function sftpMkDir()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $hostname = $this->input->post('hostname');
        $port     = $this->input->post('port');
        $key      = $this->input->post('key');
        $key_password = $this->input->post('key_password');

        $current_path = $this->input->post('current_path');
        $new_dir = $this->input->post('new_dir');

        $this->sftp = new $this->ftp_sftp($hostname, $port, $username, $password, $key, $key_password);
        if ($this->sftp === false) {
            echo 'false';
        } else {
            echo json_encode(array('dirs' => $this->sftp->mkDir($current_path, $new_dir)));
        }
        // ftp_close($this->ftp);
    }

    /**
     * Change SFTP directory.
     * To be used with AJAX.
     */
    public function sftpChDir()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $hostname = $this->input->post('hostname');
        $port     = $this->input->post('port');
        $key      = $this->input->post('key');
        $key_password = $this->input->post('key_password');

        $this->sftp = new $this->ftp_sftp($hostname, $port, $username, $password, $key, $key_password);
        if ($this->sftp === false) {
            echo 'false';
        } else {
            echo json_encode(array('dirs' => $this->sftp->chDir($this->input->post('dir')), 'cwd' => $this->sftp->pwd()));
        }
        // ftp_close($this->ftp);
    }

    /**
     * Establish connection to FTP server.
     * To be used with AJAX.
     */
    public function ftpConnect()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $hostname = $this->input->post('hostname');
        $port = $this->input->post('port');
        $passive = $this->input->post('passive');

        $this->ftp = new $this->ftp($hostname, $port, $username, $password, $passive);
        if ($this->ftp === false) {
            echo 'false';
        } else {
            echo json_encode(array('dirs' => $this->ftp->listDirs(), 'cwd' => $this->ftp->pwd()));
        }
        ftp_close($this->ftp);
    }

    /**
     * Make new FTP directory.
     * To be used with AJAX..
     */
    public function ftpMkDir()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $hostname = $this->input->post('hostname');
        $port = $this->input->post('port');
        $passive = $this->input->post('passive');

        $current_path = $this->input->post('current_path');
        $new_dir = $this->input->post('new_dir');

        $this->ftp = new $this->ftp($hostname, $port, $username, $password, $passive);
        if ($this->ftp === false) {
            echo 'false';
        } else {
            echo json_encode(array('dirs' => $this->ftp->mkDir($current_path, $new_dir)));
        }
        ftp_close($this->ftp);
    }

    /**
     * Change FTP directory.
     * To be used with AJAX.
     */
    public function ftpChDir()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $hostname = $this->input->post('hostname');
        $port = $this->input->post('port');
        $passive = $this->input->post('passive');

        $this->ftp = new $this->ftp($hostname, $port, $username, $password, $passive);
        if ($this->ftp === false) {
            echo 'false';
        } else {
            echo json_encode(array('dirs' => $this->ftp->chDir($this->input->post('dir')), 'cwd' => $this->ftp->pwd()));
        }
        ftp_close($this->ftp);
    }

    /**
     * Establish connection to Switchr client.
     * To be used with AJAX.
     */
    public function sourceConnect()
    {
        $host_id = $this->input->post('host_id');
        $host    = $this->hosts_m->get_host($host_id);
        $server  = $this->servers_m->get_server($host->host_server_id['id']);

        $source = new $this->connect();
        if ($source->establish($server, $host)) {
            $precmd = 'ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port;
            $source->ssh->exec($precmd);
            // $preError = $source->ssh->getStdError();
            // if ($preError) {
            //     $this->logging->create('Hosts', "precmd error: ".$preError, $host_id, 1);
            // }

            // $cwd = rtrim($source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'cd'"));

            $drives = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'wmic logicaldisk get name'");

            // $dirs = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir C:\ /b /a'");

            // $files = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir C:\ /b /a'");

            $drives = str_replace('Name', '', $drives);
            $drives = explode(' ', $drives);
            $drives = preg_replace('/\s+/', '', $drives);
            $drives = array_filter($drives);

            // Discard the warning about host-key change.
            // If the drive letter does not end with ':'
            // then we don't need to see it because all drives end in ':'
            foreach ($drives as $key => $value) {
                if (substr($value, -1) != ':' || $value == 'Warning:') {
                    unset($drives[$key]);
                }
            }
            // var_dump($dir);
            // print_r($drives);

            $error = $source->ssh->getStdError();
            // Check for errors from the ssh library.
            if ($error) {
                $this->logging->create('Hosts', "Unable to list directory in source: ".$error, $host_id, 1);
                // return false;
            }
            echo json_encode(array('dirs' => $drives, 'cwd' => null));
        } else {
            echo 'false';
        }
    }

    /**
     * Change source directory.
     * To be used with AJAX.
     */
    public function sourceChDir()
    {
        $host_id     = $this->input->post('host_id');
        $current_dir = $this->input->post('dir');
        $host        = $this->hosts_m->get_host($host_id);
        $server      = $this->servers_m->get_server($host->host_server_id['id']);
        // var_dump($current_dir);

        $source = new $this->connect();
        $source->establish($server, $host);
        if ($source) {
            // $precmd = 'ssh-keygen -f "/home/switchr/.ssh/known_hosts" -R [localhost]:' . $host->host_ssh_port;
            // $source->ssh->exec($precmd);
            // $preError = $source->ssh->getStdError();
            // if ($preError) {
            //     $this->logging->create('Hosts', "precmd error: ".$preError, $host_id, 1);
            // }

            // $cwd = rtrim($source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'cd'"));

            // $drives = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'cd $current_dir'");

            $dirs = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir $current_dir /b /ad /on'");

            $files = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir $current_dir /b /a-d-s /on'");

            // $files = $source->ssh->exec("sshpass -p $host->host_ssh_pass ssh -o ExitOnForwardFailure=yes -o StrictHostKeyChecking=no $host->host_ssh_user@localhost -p $host->host_ssh_port 'dir C:\ /b /a'");
            //

            // var_dump($dir);

            // $dir = str_replace('$Recycle.Bin', '', $dir);
            $dirs  = preg_split('/\R/', $dirs);
            array_pop($dirs);
            $files = preg_split('/\R/', $files);
            array_pop($files);
            // $dir = preg_replace('/\s+/', '', $dir);
            // $dir = array_filter($dir);


            // var_dump($dir);
            // print_r($drives);

            $error = $source->ssh->getStdError();
            // Check for errors from the ssh library.
            if ($error) {
                $this->logging->create('Hosts', "Unable to list directory in source: ".$error, $host_id, 1);
                // return false;
            }
            echo json_encode(array('dirs' => $dirs, 'files' => $files, 'cwd' => $current_dir));
        } else {
            echo 'false';
        }
    }

    /**
     * Check to see if FTP or S3 backup already exists for host, and pass error to AJAX if it does.
     * Only one FTP or one S3 backup can exist at the same time (Switchr client limitation).
     * @return  error  Die with HTTP error so AJAX performs error function.
     */
    public function check()
    {
        $type    = $_POST['type'];
        $host_id = $_POST['host_id'];

        // Check for an existing backup for this host. Currently only supports 1 FTP and 1 S3 at the same time.
        $existing = $this->streams->entries->get_entries(
            array(
                'namespace' => 'backups',
                'stream'    => 'backup_dest',
                'where'     => "backup_dest_host_id='$host_id'",
                //'where'     => "backup_dest_type='$type'"  // TODO Only one type of backup works right now. Pick one: FTP or S3.
            )
        );
        if ($existing['total'] > 0) {
            // Pass HTTP error to AJAX to activate popup.
            die(header('HTTP/1.0 500 Server error'));
        }
    }

    /**
     * Create bucket.
     * See backups/libraries/S3/createBucket()
     */
    public function createBucket()
    {
        $data['key'] = $this->input->post('key');
        $data['secret'] = $this->input->post('secret');
        $data['region'] = $this->input->post('region');
        $bucket_name = $this->input->post('bucket_name');
        $s3 = new $this->s3($data);
        if (!$s3->createBucket($bucket_name)) {
            echo "false";
        } else {
            echo "true";
        }
    }

    /**
     * Check that AWS credentials are correct.
     * See backups/libraries/S3/credsCorrect()
     */
    public function checkCreds()
    {
        $data['key'] = $this->input->post('key');
        $data['secret'] = $this->input->post('secret');
        $data['region'] = $this->input->post('region');
        $s3 = new $this->s3($data);
        echo $s3->credsCorrect();
    }

    /**
     * Get backup ID of S3 belonging to host.
     * Result is an echo to be used with AJAX.
     * @param   string  $host_id  ID of host.
     * @return  string            Row ID of backup; false if doesn't exist.
     */
    public function getS3($host_id = null)
    {
        if ($this->input->post()) {
            $host_id = $this->input->post('host_id');
        }
        $existing = $this->streams->entries->get_entries(
            array(
                'namespace' => 'backups',
                'stream'    => 'backup_dest',
                'where'     => "backup_dest_host_id='$host_id' AND backup_dest_type='awss3'",
                //'where'     => "backup_dest_type='$type'"  // TODO Only one type of backup works right now. Pick one: FTP or S3.
            )
        );
        if ($existing['total'] > 0) {
            echo $existing['entries'][0]['id'];
        } else {
            echo 'false';
        }
    }
}

/* End of file hosts.php */
