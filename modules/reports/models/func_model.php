<?php
class Func_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createRpt($type, $code, $date)
    {
        $command = ssh2_exec($this->sftp->connection, 'cmd /C %IBERDIR%\BIN\RPT.EXE /DATE ' . $date . ' /X'.$code. ' /load posignite.' . $type . '.set');
    }

    public function showRpt($id, $type)
    {
        $row = 1;
        if (($handle = fopen('tmp/' . $id . '.' . $type . '.csv', "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                echo '<tr>';
                $num = count($data);
                for ($c=1; $c < $num; $c++) {
                    if (!preg_match('/[*|(]/i', $data[$c])) {
                        echo $data[1] =='' ? '<td>' . $data[$c] . '</td><td></td>' : '<td>' . $data[$c] . '</td>';
                    }
                }
                echo '</tr>';
            }
            fclose($handle);
        }
    }

    //BOOLEAN
    public function checkEOD($id)
    {
        if ($file = file("tmp/$id.eodts.ini")) {
            foreach ($file as $row) {
                if ($data = str_getcsv($row, "=")) {
                    if ($data[0] == "DOB") {
                        return $data[1] == date('m d Y');
                    }
                }
            }
        }
    }

    public function fixEOD($id)
    {
        if ($file = file("tmp/$id.eodts.ini")) {
            foreach ($file as $key => $row) {
                if ($data = str_getcsv($row, "=")) {
                    if ($data[0] == "DOB") {
                        if ($data[1] != date('m d Y')) {
                            $file[$key] = "DOB=".date('m d Y')."\n";
                            file_put_contents("tmp/$id.aloha.ini", $file);
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            }
        }
    }

    //BOOLEAN
    public function setConfig($id, $file, $field, $value)
    {
        if ($handle = file("tmp/$id.$file")) {
            foreach ($handle as $key => $row) {
                if ($data = str_getcsv($row, "=")) {
                    if ($data[0] == "$field") {
                        $handle[$key] = "$field=$value\n";
                        file_put_contents("tmp/1.$file.ini", $handle);

                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    //TRUE RETURNS DATA
    public function readConfig($id, $file, $field)
    {
        if ($handle = file("tmp/$id.$file")) {
            foreach ($handle as $key => $row) {
                if ($data = str_getcsv($row, "=")) {
                    if ($data[0] == $field) {
                        return $data[1];
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    //Backup & Restore

    public function showBackups($storeId)
    {
        $dir = scandir('/home/ec2-user/dev/users/'.$storeId);
        for ($x=0; $x<=count($dir); $x++) {
            if ($dir[$x] != '..' && $dir[$x] != '.'&& $dir[$x] != ' ') {
                $list[] = $dir[$x];
            }
        }
        return $list;
    }

    // TODO: Error message
    public function backupDay($storeId, $date)
    {
        if (mkdir('/home/ec2-user/dev/users/'.$storeId.'/'.$date, 0755)) {
            $fileSys = $this->sftp->scanFilesystem('/'.$date);
            foreach ($fileSys as $row) {
                $this->sftp->receiveFile("/$date/$row", '/home/ec2-user/dev/users/'.$storeId.'/'.$date.'/'. $row);
            }
        } else {
            return false;
        }
    }

    // TODO: Error message
    public function restoreDay($userId, $date)
    {
        if ($this->sftp->mkdir($date, 644)) {
            $d = dir('c:\web\users\\'.$userId.'\\'.$date);
            while (false !== ($row = readdir($d->handle))) {
                print_r($row);
                exit();
            }
        } else {
            return false;
        }
    }

    // System checks

    public function ping($term)
    {
        $command = ssh2_exec($this->sftp->connection, 'cmd /C ping '.$term);
        stream_set_blocking($command, true);
        $contents = stream_get_contents($command);
        if (preg_match('/Reply from/i', $contents)) {
            return true;
        }
    }
    // TODO: Fix environment variables not being taken
    public function checkDT($id, $term, $share)
    {
        //open downtime ini on termx (numterms)
        ssh2_exec($this->sftp->connection, "cmd /C XCOPY \\$term\\$share\%IBERROOT%\DOWNTIME.INI %IBERDIR%\\");
        $this->sftp->receiveFile('/DOWNTIME.INI', "c:\web\users\\$id.DOWNTIME.INI");
    }
    public function spoolBackup()
    {
        // Copy spools from BOH
        ssh2_exec($this->sftp->connection, "cmd /C COPY \\$term\\$share\%IBERROOT%\EDC\*.spl %IBERDIR%\EDC");
    }
    public function spoolRestore()
    {
        // Copy spools to BOH
        ssh2_exec($this->sftp->connection, "cmd /C COPY %IBERDIR%\EDC \\$term\\$share\%IBERROOT%\EDC\*.spl");
    }

    public function refresh()
    {
        // Reset it?
        ssh2_exec($this->sftp->connection, "cmd /C COPY %IBERDIR%\STOP D:\aloha\data\STOP");
    }

    // Logging Methods

    public function parseLog($file, $error)
    {
        $arr = array();
        $rows = file($file);
        for ($i=4; $i<=(count($rows)-1); $i++) {
            if (preg_match('/^ERROR: '.$error.'/i', $rows[$i])) {
                $arr[] = $rows[$i];
            }
        }
        return $arr;
    }
    public function logError($type, $error, $userId, $storeId = false)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($storeId) {
            if ($this->db->insert('logs', array('store_id'=>$storeId, 'user_id'=>$userId, 'error_type'=>$type, 'error_desc'=>$error, 'ip'=>$ip))) {
                return true;
            }
            return false;
        } else {
            if ($this->db->insert('logs', array('user_id'=>$userId, 'error_type'=>$type, 'error_desc'=>$error, 'ip'=>$ip))) {
                return true;
            }
            return false;
        }
    }

    public function get_store($storeId = false)
    {
        if ($this->ion_auth->logged_in()) {
            if (!$storeId) {
                $stores = $this->db->get_where('stores', array('id'=>$this->session->userdata('current_store')));
            } elseif ($this->ion_auth->is_admin()) {
                $stores = $this->db->get_where('stores', array('id'=>$storeId));
            }
            return $stores->result();
        }
        return false;
    }

    public function get_last_fileserver()
    {
        if ($this->sftp) {
            $command = ssh2_exec($this->sftp->connection, 'cmd /C echo %SERVER%');
            stream_set_blocking($command, true);
            $server = stream_get_contents($command);

            $command2 = ssh2_exec($this->sftp->connection, 'cmd /C type \\\\term1\\aloha\\downtime.ini');
            stream_set_blocking($command2, true);
            $contents = stream_get_contents($command2);

            return strpos($contents, 'LastFileserver='.$server) ? $server : $contents;
        }
        return false;
    }

    public function check_spooling()
    {
        if ($this->sftp) {
            $command = ssh2_exec($this->sftp->connection, "cmd /C dir %IBERDIR%\\SPOOLING /s");
            stream_set_blocking($command, true);
            $contents = stream_get_contents($command);
            if (preg_match('/File(s)/i', $contents)) {
                return true;
            }
        }
        return false;
    }

    public function getNetView()
    {
        if ($this->sftp) {
            $command = ssh2_exec($this->sftp->connection, "cmd /C NET view");
            stream_set_blocking($command, true);
            $contents = stream_get_contents($command);
            preg_match_all('#\\\\[A-Z0-9]+#i', $contents, $matches);
            return $matches[0];
        }
        return false;
    }

    public function notifications()
    {
        if ($this->sftp) {
            $command = ssh2_exec($this->sftp->connection, "cmd /C type %IBERDIR%\\TMP\\VERIFY.TXT");
            stream_set_blocking($command, true);
            $contents = stream_get_contents($command);
            return $contents;
        }
        return false;
    }
}
