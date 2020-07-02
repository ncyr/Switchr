<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Ftp_Sftp
{
    public function __construct($host, $port, $username, $password = false, $private_key = false, $key_password = false)
    {
        $path = '/addons/shared_addons/libraries/phpseclib';
        set_include_path($_SERVER['DOCUMENT_ROOT'] . $path);
        // echo get_include_path();

        require_once('Net/SFTP.php');
        require_once('Crypt/RSA.php');

        $this->sftp = $this->connect($host, $port, $username, $password, $private_key, $key_password);
    }

    /**
     * Connect to the SFTP server.
     * @param   string  $host          Hostname or IP.
     * @param   int     $port
     * @param   string  $username
     * @param   string  $password
     * @param   string  $private_key
     * @param   string  $key_password
     * @return  stream  $server        SFTP stream to be used with sftp functions.
     */
    private function connect($host, $port, $username, $password, $private_key, $key_password)
    {
        $this->sftp = new Net_SFTP($host, $port);

        // If using private key to login.
        $key = null;
        if ($private_key) {
            $key = new Crypt_RSA();
            if ($key_password) {
                $key->setPassword($key_password);
            }
            $key->loadKey(trim($private_key));
        }

        if ($key) {
            $this->sftp->login($username, $key);
        }
        // If using password to login.
        if ($password) {
            $this->sftp->login($username, $password);
        }

        return $this->sftp;
        // Errors: $this->sftp->getLastSFTPError()

        // return false;
    }

    /**
     * Print working directory.
     * @return  string  Will be the user's home (~) on login, but '~' is not accepted as a valid lookup.
     */
    public function pwd()
    {
        return $this->sftp->pwd();
    }

    /**
     * List all directories of specified path.
     * '~' is not accepted as a valid lookup.
     * @param   string  $dir    Path can be absolute or relative.
     * @return  array   $items  Indexed array of directories.
     */
    public function listDirs($dir)
    {
        if (is_array($children = $this->sftp->rawlist($dir))) {
            $items = array();

            foreach ($children as $child => $info) {
                // phpseclib 'type' '2' is a directory.
                // We also want to disregard '.' and '..'.
                if ($info['type'] == 2 && $child != '.' && $child != '..') {
                    $items[] = $child;
                }
            }
            return $items;
        }
    }

    /**
     * Change directory and list directories after the change.
     * '~' is not accepted as a valid lookup.
     * @param   string  $dir  Directory to change to. Accepts ".." to go up, absolute paths, and relative paths.
     * @return  array         Indexed array of directories.
     */
    public function chDir($dir)
    {
        $this->sftp->chdir($dir);
        return $this->listDirs($this->pwd());
    }

    /**
     * Make new directory in current directory.
     * @param   string  $dir_name  Directory to create.
     * @return  array              Indexed array of directories including new directory. False if mkdir failed.
     */
    public function mkDir($current_path, $new_dir)
    {
        if ($this->sftp->mkdir($new_dir)) {
            return $this->chDir($current_path);
        } else {
            return false;
        }
    }
}
