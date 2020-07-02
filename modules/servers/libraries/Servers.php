<?php
class Servers
{
    public function __construct(/*$host, $server*/)
    {
        $this->_ci = &get_instance();
        $this->_ci->load->library('Connect');
        $this->_ci->load->model('servers/servers_m');
    }

    // In order for the user www-data to use sudo, www-data must be in the sudoers file:
    // www-data ALL=(ALL) NOPASSWD: ALL
    public function uploadKey($key, $server_id)
    {
        $site = SITE_REF;

        $user = '';

        //set the location/username for ssl storage
        if ($_SERVER['PYRO_ENV'] == 'production') {
            $user = 'switchr';
            $ssl_location = '../ssl.switchr';
        } elseif ($_SERVER['PYRO_ENV'] == 'staging') {
            $user = 'switchr';
            $ssl_location = '../ssl.switchr_beta';
        } else {
            $user = 'switchr';
            $ssl_location = '../ssl.switchr';
        }

        //check if dir exists for site
        if (!is_dir("$ssl_location")) {
            mkdir("$ssl_location");
        }
        if (!is_dir("$ssl_location/$site")) {
            mkdir("$ssl_location/$site");
        }

        $dir = "$ssl_location/$site/";
        //get posted data from form and upload key to the ssl.switchr folder.
        exec("sudo ssh-keygen -y -f $ssl_location/$site/$server_id > $ssl_location/$site/$server_id.pub");
        exec("sudo chown -Rf $user:$user $ssl_location/$site/*");
        exec("sudo chmod 644 $ssl_location/$site/*");
        $file = fopen($dir . $server_id, 'w');
        fwrite($file, $key);
        fclose($file);

        // Get server object.
        $server = $this->_ci->servers_m->get_server($server_id);
        // Insert iptables rules if necessary.
        $this->_ci->connect->iptablesSet($server);
    }
}
