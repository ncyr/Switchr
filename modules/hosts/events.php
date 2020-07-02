<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Sample Events Class
 *
 * @package     PyroCMS
 * @subpackage  Sample Module
 * @category    events
 * @author      PyroCMS Dev Team
 */
class Events_Hosts
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        Events::register('streams_post_insert_entry', array($this, 'stream_insert'));
        // Create Guac user, and if Patreon sign-up, make user beta group.
        Events::register('post_user_register', array($this, 'create_guac_user'));
        Events::register('post_user_login', array($this, 'login_guac_user'));
        Events::register('pre_user_logout', array($this, 'logout_guac_user'));
    }

    public function stream_insert($trigger_data)
    {
        $stream = $trigger_data['insert_data'];
        $entry_id = $trigger_data['entry_id'];

        if ($trigger_data['stream']->stream_slug == 'hosts') {
            $this->ci->load->library('Connect');
            $this->ci->load->model('hosts/hosts_m');
            $this->ci->load->model('license/license_m');
            $this->ci->load->driver('Streams');

            // If the node is down then we have to undo all of the fucked up shit
            // that Pyro just did for no reason.
            $server_ip = $this->ci->streams->entries->get_entry(
                $trigger_data['insert_data']['host_server_id'],
                'servers',
                'servers',
                false
            )->server_ip;
            if (!$this->ci->connect->serverIsUp($server_ip)) {
                $this->ci->streams->entries->delete_entry(
                    $entry_id,
                    'hosts',
                    'hosts'
                );
                echo "The server is down.";
                die(header("HTTP/1.0 500 Server Error"));
            }

            //create the db host first
            //then create the shell user for the host on the server
            if ($this->ci->hosts_m->createHost($trigger_data)) {
                //then assign the license to the host
                $this->ci->license_m->assignLicense($entry_id);
                //$this->ci->hosts_m->createGuac($trigger_data);
            // If the connection to server via createHost() fails then remove the DB entry.
            } else {
                $this->ci->streams->entries->delete_entry(
                    $entry_id,
                    'hosts',
                    'hosts'
                );
                echo "Incorrect server settings. Please contact the server administrator.";
                die(header("HTTP/1.0 500 Server Error"));
            }
        }
    }

    public function stream_update()
    {
        //$this->ci->logging->create( 'Hosts', 'A host has been modified.', 1, 1, $trigger_data['entry_id'] );
    }


    /**
     * This is tied to the custom code inserted into system/cms/modules/users/controllers/users.php/register():352-359.
     * When a new user is registered then we create the user in the guacamole database.
     * @param  object  $user  $user->username = $username;
     *                        $user->display_name = $username;
     *                        $user->email = $email;
     *                        $user->password = $password;
     *                        $user->id = $id;
     */
    public function create_guac_user($user)
    {
        $password_hex = hex2bin(hash('sha256', $user->password));
        $guac_db = $this->ci->load->database('guac_db', true);
        $guac_db->set('user_id', $user->id);
        $guac_db->set('username', $user->email);
        $guac_db->set('password_hash', $password_hex);
        $guac_db->set('password_date', date('Y-m-d H:i:s'));
        $guac_db->insert('guacamole_user');
        $guac_db->close();

        // Update the user group to 'beta' (4) if Patreon sign-up.
        $user_row = $this->ci->db->get_where(SITE_REF.'_payignite_subscriptions', array('sub_customer_id' => $user->email), 1)->result();
        if (count($user_row) === 1) {
            $this->ci->db->where('id', $user->id);
            $this->ci->db->update(SITE_REF.'_users', array('group_id' => 4));
        }
    }

    /**
     * This is tied to the custom code inserted into system/cms/modules/users/controllers/users.php/login():132.
     * When a user logs in then we make a login API call to Guacamole and insert the returned token as a cookie
     * to be used in a redirect, ie:
     * redirect('http://192.168.56.200:8080/guacamole/#/client/MQBjAG15c3Fs?token='.$token);
     *
     * We need to store the auth token for each server the user has a client on.
     * This function assumes every server node has a Guac server.
     * @param  object  $user  $user->email    = $email;
     *                        $user->password = $password;
     */
    public function login_guac_user($user)
    {
        // $user_id = $this->ci->db->get_where(SITE_REF.'_users', array('email' => $user->email), 1)->result()[0]->id;
        // $this->ci->db->select('host_server_id');
        // Get all server IDs that this user has a host on.
        // $host_server_ids = $this->ci->db->get_where(SITE_REF.'_hosts_hosts', array('created_by' => $user_id))->result();
        // $server_ids = array();
        // foreach ($host_server_ids as $host) {
        //     // Add server ID to the list.
        //     $server_ids[] = $host->host_server_id;
        // }
        // // Get all hosts assigned to this user,
        // // then do a DB lookup to get the server ID.
        // $assigned_hosts = $this->ci->db->get_where(SITE_REF.'_hosts_host_users', array('user_id' => $user_id))->result();
        // foreach ($assigned_hosts as $assigned_host) {
        //     $host = $this->ci->db->get_where(SITE_REF.'_hosts_hosts', array('id' => $assigned_host->host_id))->result()[0];
        //     // Add server ID to the list.
        //     $server_ids[] = $host->host_server_id;
        // }

        $this->ci->db->select('id, server_ip');
        $servers = $this->ci->db->get(SITE_REF.'_servers_servers')->result();

        // For each server get a Guac auth token.
        foreach ($servers as $server) {
            // $this->ci->db->select('server_ip');
            // Get server IP address.
            // $server_ip = $this->ci->db->get_where(SITE_REF.'_servers_servers', array('id' => $server_id), 1)->result()[0]->server_ip;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_PORT => "8080",
                CURLOPT_URL => "http://{$server->server_ip}:8080/remote/api/tokens",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "username=".urlencode($user->email)."&password=".urlencode($user->password),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
                die;
            } else {
                $token = json_decode($response)->authToken;
                // Store each Guac auth token in a cookie.
                setcookie("remote_token_{$server->id}", $token, time()+3600, '/hosts');
            }
        }
    }

    /**
     * On user logout we want to destroy the auth token in guacamole
     * and then remove it from the cookie.
     * TODO: Can't figure out how to delete the token from guac's side; security issue?
     *       We still need to unset the token in the cookie, but this also seems not possible.
     */
    public function logout_guac_user()
    {
        // $curl= curl_init();
        //
        // curl_setopt_array($curl, array(
        //     CURLOPT_PORT => "8080",
        //     CURLOPT_URL => "http://192.168.56.200:8080/guacamole/api/tokens/".$_COOKIE['remote_token'],
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "DELETE",
        //     // CURLOPT_POSTFIELDS => "username=guacadmin&password=guacadmin",
        //     // CURLOPT_POSTFIELDS => "username=".urlencode($user->email)."&password=".urlencode($user->password),
        // ));
        //
        // $response = curl_exec($curl);
        // $err = curl_error($curl);
        //
        // curl_close($curl);
        //
        // if ($err) {
        //     echo "cURL Error #:" . $err;
        //     die;
        // } else {
        // var_dump($response);
        // die;

        // Not possible to unset a cookie if not in the correct URI (/hosts)?
        // Unset Guacamole auth-token cookie.
        // array_filter($_COOKIE, function ($key) {
        //     if (strpos($key, 'remote_token') !== false) {
        //         setcookie($_COOKIE[$key], null, time()-1, '/hosts');
        //     }
        // });
    }
}
/* End of file events.php */
