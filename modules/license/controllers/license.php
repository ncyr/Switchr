<?php

class License extends Public_Controller
{
    public $hostId;
    public $host;
    public $key;
    public $server_stream;
    public function __construct()
    {
        parent::__construct();
        $siteConfig = null;
        $this->load->model('hosts/hosts_m', "Hosts");
        $this->load->model('servers/servers_m', "Servers");
        $this->lang->load('license');
        $this->load->driver('Streams');
        $this->load->model('license_m', 'License');
        $this->load->model('ports/ports_m', 'Ports');
        $this->load->library('ConfigEncoder');
        $this->load->library('logging/logging');
        $this->load->library('encrypt');
        $this->load->helper('Xml');
    }

    public function index($host_id = false)
    {
        if ($host_id) {
            $this->template->set('host_id', $host_id);
            // FIXME: host_license in the stream is NULL, even though it exists in the db. Dafuq?
            // Current workaround: use the function get_host_license($host_id) to get the license id.
            // $host = $this->Hosts->get_host($host_id);
            // $this->template->set('license_id', $host->host_license);
            $this->template->set('license_id', $this->Hosts->get_host_license($host_id));

            $this->template
                ->title($this->module_details['name'])
                ->append_js('module::license.js')->append_js('theme::clipboard.min.js')
                ->build('license_index');
        } else {
            $this->template
                ->title($this->module_details['name'])
                ->append_js('module::license.js')->append_js('theme::clipboard.min.js')
                ->build('license_index');
        }
    }

    public function get()
    {
        $config = '';
        //make sure this key is POST before commit to production.
        if ($this->input->post('key')) {
            $this->key = $this->input->post('key');

            $this->host = $this->Hosts->get_host_by_key($this->key);
            $this->host_id = $this->host['id'];

            if ($this->keyIsValid($this->key)) {
                //$msg = $this->lang->line('license:'.$this->reason);

                $msg = $this->lang->line('license:'.$this->reason);
                $this->output->set_status_header($this->status, $msg);

                $data = array(
                    'slug' => 'install-attempt',
                    'to' => 'admin@switchr.io',
                    'name' => 'Switchr Admin',
                    'key' => $this->key
                );
                Events::trigger('email', $data, 'array');
                //return $this->output->set_output($msg);

                $this->load->library('Connect');
                $config = $this->configencoder->load()->fromString($this->connect->getBaseData($this->License->getHostByKey($this->key)));
                $this->output
                    ->set_content_type('text/plain')
                    ->set_output("KEY=" . $config->encrypt()->asBase64());
            } else {
                $this->output
                    ->set_content_type('text/plain')
                    ->set_output("ERROR=This serial number is not valid, or has already been used.");
            }
        }
    }

    //	public function getBackupConfig($host_id)
    //	{
    //		$this->Hosts->connectSFTP();
//
    //		$config = $this->configencoder->load()->fromString($this->backupConfig($host_id));
    //		$config = $config->encrypt()->asBase64();
//
    //		$stream = ssh2_exec($this->sftp->connection, "echo " . $config . " > C:\\Progra~1\Switchr\Switchrf.p");
//
    //	}

    private function keyIsValid($key)
    {
        //Status code 1 is ACTIVE
        $entry_data = array(
            'stream' => 'license_serials',
            'namespace' => 'license',
            'where' => "license_serial='$key'",
            'limit' => 1
        );
        $license = $this->streams->entries->get_entries($entry_data);

        if (count($license['entries']) == 1) {
            $entry_data = array(
                'stream' => 'hosts',
                'namespace' => 'hosts',
                'where' => "host_license='". $license['entries'][0]['id'] . "'",
                'limit' => 1
            );
            $host = $this->streams->entries->get_entries($entry_data);

            $this->host_id = $host['entries'][0]['id'];
            $this->license_id = $license['entries'][0]['license_serial'];
            $this->license_status = $license['entries'][0]['license_status'];
            $this->license_exp = $license['entries'][0]['license_exp'];
        }
        //check to make sure license has not been used or expired
        if ($this->license_status['key'] === "0" || date($this->license_exp) < date('U')) {
            $this->License->changeStatus($this->key, 1);
            $this->status = 200;
            return true;
        }
        switch ($key) {
                case ($this->license_exp > date('Y-m-d h-i-s')):
                {
                    $this->status = 403;
                    $this->reason = 'expired';
                    break;
                }
                case ($this->license_status == 2):
                {
                    $this->status = 410;
                    $this->reason = 'blocked';
                    break;
                }
                default:
                {
                    $this->status = 404;
                    $this->reason = 'invalid';
                }
            }
    }

    public function check()
    {
        if ($this->input->post('key')) {
            $this->key = $this->input->post('key');

            $this->host = $this->Hosts->get_host_by_key($this->key);
            $this->host_id = $this->host['id'];

            $entry_data = array(
                'stream' => 'license_serials',
                'namespace' => 'license',
                'where' => "license_serial='$key'",
                'limit' => 1
            );
            $license = $this->streams->entries->get_entries($entry_data)['entries'][0];

            if ($license['license_status'] == false) {
                $this->load->library('Connect');
                $this->connect->delConfig($this->host_id);
            }
        }
    }

    public function emailLicense()
    {
        $email = $_POST['email'];
        $license = $_POST['license'];

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocol  = "https://";
        } else {
            $protocol  = "http://";
        }
        $url = $protocol.$_SERVER['SERVER_NAME'];

        //email to default user
        $config['protocol'] = Settings::get('mail_protocol');
        $config['charset'] = 'iso-8859-1';
        $config['wordwrap'] = true;
        $config['smtp_host'] = Settings::get('mail_smtp_host');
        $config['smtp_user'] = Settings::get('mail_smtp_user');
        $config['smtp_pass'] = Settings::get('mail_smtp_pass');
        $config['smtp_port'] = Settings::get('mail_smtp_port');

        $this->email->initialize($config);
        $this->email->from('no-reply@switchr.io', 'Switchr Reports');
        $this->email->to($email);
        $this->email->subject('Switchr License');
        $this->email->set_mailtype("html");

        $this->email->message("
        <p><strong>License: </strong>$license</p>
        <p><a href= '$url/files/Install_Remote.exe'>Download Switchr</a></p>
        ");

        $this->email->send();
    }
}
