<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Ftp
{
    public function __construct($host, $port, $username, $password, $backup_dest_passive)
    {
        $this->conn = $this->connect($host, $port, $username, $password, $backup_dest_passive);
    }

    /**
     * Connect to the FTP server.
     * @param   string  $host                 Hostname or IP.
     * @param   int     $port
     * @param   string  $username
     * @param   string  $password
     * @param   bool    $backup_dest_passive  Boolean from table backups.
     * @return  stream  $server               FTP stream to be used with PHP ftp_* functions.
     */
    private function connect($host, $port, $username, $password, $backup_dest_passive)
    {
        // Connect to ftp server.
        $stream = ftp_connect($host, $port, 3);

        // If the login works then we have a successful connection.
        if (ftp_login($stream, $username, $password)) {
            // Invert $backup_dest_passive because 0==passive and 1==active in the form. This is madness.
            if ($backup_dest_passive) {
                $backup_dest_passive = false;
            } else {
                $backup_dest_passive = true;
            }
            ftp_pasv($stream, $backup_dest_passive);

            return $stream;
        }

        // This will return: $ftp->conn === false
        return false;
    }

    /**
     * Print working directory.
     * @return  string  Will be the user's home (~) on login.
     */
    public function pwd()
    {
        return ftp_pwd($this->conn);
    }

    /**
     * List all directories of specified path.
     * @param   string  $dir    Path can be absolute or relative.
     * @return  array   $items  Indexed array of directories.
     */
    public function listDirs($dir)
    {
        if (is_array($children = @ftp_rawlist($this->conn, $dir))) {
            $items = array();

            foreach ($children as $child) {
                $chunks = preg_split("/\s+/", $child);

                if ($chunks[0]{0} === 'd') {
                    array_splice($chunks, 0, 8);
                    $items[] = implode(" ", $chunks);
                }
            }
            return $items;
        }
    }

    /**
     * Change directory and list directories after the change.
     * @param   string  $dir  Directory to change to. Accepts ".." to go up, absolute paths, and relative paths.
     * @return  array         Indexed array of directories.
     */
    public function chDir($dir)
    {
        ftp_chdir($this->conn, $dir);
        return $this->listDirs($this->pwd());
    }

    /**
     * Make new directory in current directory.
     * @param   string  $dir_name  Directory to create.
     * @return  array              Indexed array of directories including new directory. False if mkdir failed.
     */
    public function mkDir($current_path, $new_dir)
    {
        if (ftp_mkdir($this->conn, $new_dir)) {
            return $this->chDir($current_path);
        } else {
            return false;
        }
    }
}
