<?php
//class Backups_m extends CI_Model {
class Backups_m extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('streams');
    }
    public function get_backup($id)
    {
        $backup = $this->streams->entries->get_entry($id, 'backup_dest', 'backups');
        return $backup;
    }
    public function is_owner($host_id)
    {
        $host = $this->streams->entries->get_entry($host_id, 'hosts', 'hosts');
        if ($this->current_user->id == $host->created_by) {
            return true;
        }
    }
    public function getEvents($month, $year)
    {
        $dir = array();
        $list = array();
        $dirPath = FCPATH.UPLOAD_PATH.'/backups/'.$storeId;
        if ($this->currentStore != '') {
            if (is_dir($dirPath)) {
                if ($dir = scandir($dirPath)) {
                    //PREPARE A LIST OF DIRS NAME TO GIVE BACK
                    for ($x=0; $x<=(count($dir)-1); $x++) {
                        if ($dir[$x] != '..' && $dir[$x] != '.'&& $dir[$x] != ' ') {
                            if (strpos($dir[$x], $year.$month) !== false) {
                                $rowDir = $dir[$x];
                                $day = str_replace($year.$month, "", $rowDir);
                                $list[intval($day)] = '/admin/backups/show_day/' . $rowDir;
                            }
                        }
                    }
                    return $list;
                } else {
                    $this->logMsg($storeId, 'error', 'backup', 'The directory could not be scanned', true);
                    return false;
                }
            } else {
                $this->logMsg($storeId, 'error', 'backup', 'The directory for the store was not found because the system did not create it.', false);
                return false;
            }
        } else {
            return $this->logMsg($storeId, 'error', 'backup', 'No Store Was Selected', true);
            return false;
        }
    }
    //showBackups
    //RETURNS: LIST OF ALL DATED SUBDIRECTORIES
    //LAST MOD: 6.16.12-NC
    public function getBackups($sourceId, $location=false)
    {
        $dir = array();
        $list = array();
        $storeId = $this->currentStore;
        $dirPath = FCPATH.UPLOAD_PATH . '/backups/' . $storeId . '/' . $sourceId;

        if ($location) {
            $dirPath = FCPATH.UPLOAD_PATH . '/backups/' . $storeId . '/' . $sourceId . '/' . $location;
        }

        if ($storeId != '') {
            if (is_dir($dirPath)) {
                if ($dir = scandir($dirPath)) {
                    //PREPARE A LIST OF DIRS NAME TO GIVE BACK
                    for ($x=0; $x<=(count($dir)-1); $x++) {
                        if ($dir[$x] != '..' && $dir[$x] != '.'&& $dir[$x] != ' ') {
                            $list[] = $dir[$x];
                        }
                    }
                    return $list;
                } else {
                    $this->logMsg($storeId, 'error', 'backup', 'The directory could not be scanned', true);
                    return false;
                }
            } else {
                //$this->logMsg($storeId, 'error', 'backup', 'The directory for the store was not found because the system did not create it.', false);
                return false;
            }
        } else {
            return false;
        }
    }
    /* END SHOW BACKUPS */

    //backupDay DEPRICATED
    //RETURNS: Backup dated subdir
    //LAST MOD: 8.12.12-NC
    public function backupDay($storeId, $date)
    {
        $validPath = false;
        $storePath = FCPATH.UPLOAD_PATH.'backups/'.$storeId;
        $filePath = FCPATH.UPLOAD_PATH.'backups/'.$storeId.'/'.$date;

        if (!is_dir($storePath)) {
            $this->createStoreDir($storePath);
        } else {
            //check if the backup folder exists already
            if (!is_dir($filePath)) {
                if ($this->createDir($filePath)) {
                    $validPath = true;
                }
            } else {
                $validPath = true;
            }
        }
        if ($validPath) {
            if (!$this->getDate($storeId, $date)) {
                $this->removeDir($filePath);
            }
        } else {
            $this->logMsg($storeId, 'error', 'backups', 'There was not a valid path found for ' . $filePath);
            return false;
        }
    }

    //restoreDay - by store, by date
    //RETURNS: BOOLEAN
    //LAST MOD: 8.12.12-NC
    public function restoreDay($userId, $date)
    {
        //make the connection
        $this->connectSFTP();

        if ($this->sftp->mkdir($date, 644)) {
            $d = dir('c:\web\users\\'.$userId.'\\'.$date);
            while (false !== ($row = readdir($d->handle))) {
                //upload the file to the store
                if ($this->sftp->uploadFile('c:\web\users\\'.$userId.'\\'.$date.'\\'. $row, "/$date/$row")) {
                    return true;
                } else {
                    return array('error'=>'There was a problem uploading the file.');
                }
            }
        } else {
            return array('error'=>'there was a problem creating the directory');
        }
    }

    public function removeDay($storeId, $date)
    {
        if (is_dir(FCPATH.UPLOAD_PATH . 'backups/' . $storeId . '/' . $date)) {
            $file = opendir(FCPATH.UPLOAD_PATH . 'backups/' . $storeId . '/' . $date);
            while (false !== ($row = readdir($file))) {
                if ($row != '.' || $row != '..') {
                    unlink(FCPATH.UPLOAD_PATH . 'backups/' . $storeId . '/' . $date . '/' .$row);
                }
            }
            if (!rmdir(FCPATH.UPLOAD_PATH . 'backups/'. $storeId . '/' . $date)) {
                return array('error'=>"Could not remove the directory: $date");
            }
        } else {
            return array('error'=>'Could not read the directory');
        }
    }
    public function showFolder($folder)
    {
        $dir = array();
        $list = array();

        if ($storeId != '') {
            if ($dir = scandir(FCPATH.UPLOAD_PATH.'/backups/'.$storeId.'/'.$folder)) {
                //PREPARE A LIST OF DIRS NAME TO GIVE BACK
                for ($x=0; $x<=(count($dir)-1); $x++) {
                    if ($dir[$x] != '..' && $dir[$x] != '.'&& $dir[$x] != ' ') {
                        $list[] = $dir[$x];
                    }
                }
                return $list;
            } else {
                $this->logMsg($storeId, 'error', 'backup', 'The directory could not be scanned', true);
                return false;
            }
        } else {
            return $this->logMsg($storeId, 'error', 'backup', 'No Store Was Selected', true);
            ;
        }
    }
    public function getCheckpoint()
    {
    }
    public function getSources($storeId)
    {
        return $this->db->get_where('backup_sources', array('owner_id' => $storeId))->result();
    }
    public function getSource($sourceId)
    {
        return $this->db->get_where('backup_sources', array('id' => $sourceId))->row();
    }
    // public function getbackupConfig($sourceId)
    // {
    //     $backup = $this->getSource($sourceId);
    //
    //     $config = array(
    //         'protocol'        => $backup->backup_type,
    //         'uploadat'        => '* * * * *',
    //         'hostname'        => $backup->hostname,
    //         'username'        => $backup->username,
    //         'password'        => $this->encrypt->decode($backup->password),
    //         'port'            => $backup->port,
    //         'passv'            => $backup->passive,
    //         'l.file0'        => $backup->backup_location,
    //         'r.file0'        => $backup->destination,
    //         'user_cert'        => $this->encrypt->decode($backup->user_cert),
    //         'user_cert_pwd'=> $this->encrypt->decode($backup->user_cert_pwd)
    //         );
    //     return Xml::fromArray($config, null, '<settings/>');
    // }
    public function isOwnerSource($sourceId)
    {
        $source = $this->getSource($sourceId);
        if (!$this->Store->is_owner($source->owner_id)) {
            return false;
        }
    }
    public function get_all_s3($host_id)
    {
        $data = array(
                        'stream'    => 'backup_s3',
                        'namespace' => 'backups',
                        'where'    => "backup_s3_host_id =" . $host_id
                      );

        $result = $this->streams->entries->get_entries($data);

        return $result['entries'];
    }
    public function pushConfig($id, $host_id, $type)
    {
        $this->load->library('backup_lib');

        $this->backup_lib->pushConfig($id, $host_id, $type);
    }
    public function remConfig($id)
    {
        $this->load->library('backup_lib');

        return $this->backup_lib->remConfig($id);
    }
}
