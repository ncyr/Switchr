<?php

/**
 * NOTE: SITE_REF may not work here for Patreon functions because this is being called remotely; don't tempt fate.
 */
class Payignite extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->lang->load('payignite');
        $this->load->driver('Streams');
        $this->load->library('Logging');
        $this->load->library('Connect');
        $this->load->model('payignite_m', 'Payignite');
        $this->load->model('hosts/hosts_m');
        $this->load->model('license/license_m');
    }

    /**
     * Webhook API endpoint for Patreon pledge create.
     */
    public function patreon_create()
    {
        // Grab the data. $_POST doesn't work because payload is JSON.
        $data_raw = file_get_contents('php://input');
        $data = json_decode($data_raw);

        $email = null;
        // Get the pledge amount.
        $amount_cents = $data->data->attributes->amount_cents;
        // Get the user's email address from the data.
        // The "included" object is an indexed array,
        // so we need to loop it until we find the one we need.
        foreach ($data->included as $index => $obj) {
            if ($obj->type == 'user') {
                $email = $obj->attributes->email;
                break;
            }
        }

        // Assign the plan code by the subscription amount.
        // These prices are for Patreon users only.
        $plan_id = null;
        switch ($amount_cents) {
            case 500:
                $plan_id = 'H1';
                break;
            case 1000:
                $plan_id = 'H2';
                break;
            case 2000:
                $plan_id = 'H5';
                break;
            default:
                $plan_id = 'H0';
                break;
        }

        // Insert Patreon subscription into database.
        if ($plan_id) {
            $this->db->insert(SITE_REF.'_payignite_subscriptions', array(
                'created'         => date('Y-m-d H:i:s'),
                'sub_customer_id' => $email,
                'sub_plan_id'     => $plan_id
            ));
        }
    }

    /**
     * Webhook API endpoint for Patreon pledge edit.
     * TODO: Upgrading a plan is okay, but what about downgrading?
     *       If plan has been downgraded then upon login the user
     *       needs to select which hosts to delete.
     *       This will require a host available/used check upon login
     *       and redirect to a host-delete view if 'used' is more than 'available'.
     */
    public function patreon_edit()
    {
        // Grab the data. $_POST doesn't work because payload is JSON.
        $data_raw = file_get_contents('php://input');
        $data = json_decode($data_raw);

        $email = null;
        // Get the pledge amount.
        $amount_cents = $data->data->attributes->amount_cents;
        // Get the user's email address from the data.
        // The "included" object is an indexed array,
        // so we need to loop it until we find the one we need.
        foreach ($data->included as $index => $obj) {
            if ($obj->type == 'user') {
                $email = $obj->attributes->email;
                break;
            }
        }

        // Assign the plan code by the subscription amount.
        // These prices are for Patreon users only.
        $plan_id = null;
        switch ($amount_cents) {
            case 500:
                $plan_id = 'H1';
                break;
            case 1000:
                $plan_id = 'H2';
                break;
            case 2000:
                $plan_id = 'H5';
                break;
            default:
                $plan_id = 'H0';
                break;
        }

        // Update Patreon subscription in database.
        if ($plan_id) {
            $this->db->where('sub_customer_id', $email);
            $this->db->update(SITE_REF.'_payignite_subscriptions', array(
                'updated'      => date('Y-m-d H:i:s'),
                'sub_plan_id'  => $plan_id
            ));
        }
    }

    /**
     * Webhook API endpoint for Patreon pledge delete.
     * Delete all hosts, then the user from Guac database and website database (default_users table).
     */
    public function patreon_delete()
    {
        // Grab the data. $_POST doesn't work because payload is JSON.
        $data_raw = file_get_contents('php://input');
        $data = json_decode($data_raw);

        $email = null;
        // Get the pledge amount.
        $amount_cents = $data->data->attributes->amount_cents;
        // Get the user's email address from the data.
        // The "included" object is an indexed array,
        // so we need to loop it until we find the one we need.
        foreach ($data->included as $index => $obj) {
            if ($obj->type == 'user') {
                $email = $obj->attributes->email;
                break;
            }
        }

        // Get ID of user by email adrress.
        //$user_id = $this->db->get_where(SITE_REF.'_payignite_subscriptions', array('sub_customer_id' => $email), 1)->result()[0]->id;
        $user_id = $this->db->get_where(SITE_REF.'_users', array('email' => $email), 1)->result()[0]->id;

        if ($user_id) {
            // Get all hosts belonging to user.
            $hosts = $this->hosts_m->get_hosts($user_id);

            // Delete hosts.
            //TODO REMOVE BACKUPS
            foreach ($hosts as $index => $host) {
                // If no server, don't continue.
                $server_ip = $this->streams->entries->get_entry(
                    $host['host_server_id'],
                    'servers',
                    'servers',
                    false
                )->server_ip;
                if (!$this->connect->serverIsUp($server_ip)) {
                    echo "The server is down.";
                    die(header("HTTP/1.0 500 Server Error"));
                }

                //disable port if active.
                $entry_data = array(
                    'namespace' => 'ports',
                    'stream' => 'user_port',
                    'where' => 'host_id='.$host['id'],
                );
                $port = $this->streams->entries->get_entries($entry_data);

                foreach ($port['entries'] as $port) {
                    if ($port['is_active']['key'] == '1') {
                        //toggle port
                        $port_obj = new stdClass();

                        foreach ($port as $k => $v) {
                            $port_obj->{$k} = $v;
                        }
                        $this->connect->switchPort($port_obj, 'tcp', $port['ip_rule'], $port['host_id']['id']);
                    }

                    $this->streams->entries->delete_entry($port['id'], 'user_port', 'ports');
                }

                //push the cfg to the host, restarting the service. Hangs if the host isn't connected.
                //$this->connect->pushConfig($host->id);
                $this->connect->delConfig($host['id']);

                $this->connect->removeUser($host['id']);

                $this->streams->entries->delete_entry($host['id'], 'hosts', 'hosts');

                // Delete license associated with host.
                $this->streams->entries->delete_entry($host['host_license'], 'license_serials', 'license');

                // Delete logs associated with host.
                $logs = $this->streams->entries->get_entries(array(
                    'namespace' => 'logging',
                    'stream'    => 'logging',
                    'where'     => 'logging_host_id='.$host['id'],
                ));
                foreach ($logs['entries'] as $log) {
                    $this->streams->entries->delete_entry($log['id'], 'logging', 'logging');
                }
            }

            // Delete user from Guacamole database.
            $guac_db = $this->load->database('guac_db', true);
            $guac_db->delete('guacamole_user', array('username' => $email));
            $guac_db->close();

            // Delete user from website. This may cause a bug if the user is re-added, not sure.
            $this->db->delete(SITE_REF.'_payignite_subscriptions', array('sub_customer_id' => $email));
            $this->db->delete(SITE_REF.'_users', array('email' => $email));
        }
    }

    public function index()
    {
        if ($this->Payignite->getCustomerId() == false) {
            redirect('/payignite/create');
        }

        // Stripe Customer object -> subscriptions[0]
        // $data['subscription'] = \Stripe\Customer::retrieve($this->Payignite->getCustomerId())->subscriptions->data[0];
        //$data['subscription'] = $this->Payignite->getSubscription();

        // payignite_subscriptions table row.
        $data['subscription'] = $this->db->get_where(SITE_REF.'_payignite_subscriptions', array('created_by' => $this->current_user->id), 1)->result()[0];
        // Retrieved Stripe Plan object.
        $data['plan'] = $this->Payignite->Plan->getPlan();
        // parsed plan (hosts, s3, ftp, local)
        $data['plan_parsed'] = $this->Payignite->Plan->planParse($data['plan']['id']);

        $this->template
            ->title('Your Plan')
            ->build('index', $data);
    }

    public function create()
    {
        $this->template
            ->title('Create Plan')
            ->build('create');
    }

    public function create_reseller()
    {
        $this->template
            ->title('Create Plan')
            ->build('create_reseller');
    }

    public function edit($id)
    {
        // Stripe Customer object -> subscriptions[0]
        $data['subscription'] = $this->db->get_where(SITE_REF.'_payignite_subscriptions', array('created_by' => $this->current_user->id), 1)->result()[0];
        // subscriptions table
        $data['plan'] = $this->Payignite->Plan->getPlan();
        // parsed plan (hosts, s3, ftp, local)
        $data['plan_parsed'] = $this->Payignite->Plan->planParse($data['plan']['id']);

        // For AJAX check on edit view/partial
        // Amount of hosts used.
        $data['hosts_used'] = $this->Payignite->hostsUsed();
        // Amount of backups used. Need to create this function.
        // $data['backups_used'] = $this->Payignite->backupsUsed();

        // When the update actually gets posted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Create/retrieve new plan
            $hosts = $this->input->post('hosts');
            // $s3 = $this->input->post('s3');
            // $ftp = $this->input->post('ftp');
            // $local = $this->input->post('local');

            // Must be using <= amount of resources being paid for, duh. This is a failsafe for AJAX in edit.php view.
            if ($this->Payignite->hostsUsed() > (int) $hosts) {
                // || $this->Payignite->backupsUsed()['s3'] > (int) $s3
                // || $this->Payignite->backupsUsed()['ftp'] > (int) $ftp
                // || $this->Payignite->backupsUsed()['local'] > (int) $local) {
                redirect("/payignite/edit/$id", 'refresh');
            }

            $plan_id = "H$hosts";//."S$s3"."FTP$ftp"."L$local";
            $this->Payignite->Plan->createPlan($plan_id);

            if (!$data['subscription']) { //prevent error if subscription was canceled
                //direct user to purchase one
                redirect('/payignite/create');
            }
            // Update subscription with new plan
            // Update Stripe
            $subscription = \Stripe\Subscription::retrieve($data['subscription']['id']);
            $subscription->plan = $plan_id;
            $subscription->save();

            // Update local database
            $values = array(
                'sub_plan_id' => $plan_id,
            );
            $this->streams->entries->update_entry($id, $values, 'subscriptions', 'payignite');

            redirect('/payignite');
        }

        $this->template
            ->title('Edit Plan')
            ->build('edit', $data);
    }

    public function delete($id)
    {
        $this->load->library('Connect');

        // Get subscription row; used later to delete entry.
        $params = array(
            'stream' => 'subscriptions',
            'namespace' => 'payignite',
            'where' => 'sub_customer_id = '."'".$id."'",
            'limit' => 1,
        );
        $payignite_entries = $this->streams->entries->get_entries($params);

        // Get all hosts created by user.
        $params = array(
            'stream' => 'hosts',
            'namespace' => 'hosts',
            'where' => 'default_hosts_hosts.created_by = '."'".$payignite_entries['entries'][0]['created_by']['user_id']."'",
        );
        $hosts_entries = $this->streams->entries->get_entries($params);

        // Do stuff and things with each host belonging to user.
        foreach ($hosts_entries['entries'] as $entry) {
            $host = $this->streams->entries->get_entry($entry['id'], 'hosts', 'hosts');
            //disable port if active.
            $entry_data = array(
                'namespace' => 'ports',
                'stream' => 'user_port',
                'where' => 'host_id='.$host->id,
            );
            $port = $this->streams->entries->get_entries($entry_data);

            foreach ($port['entries'] as $port) {
                if ($port['is_active']['key'] == '1') {
                    //toggle port
                    $port_obj = new stdClass();

                    foreach ($port as $k => $v) {
                        $port_obj->{$k} = $v;
                    }
                    $this->connect->switchPort($port_obj, 'tcp', $port['ip_rule'], $port['host_id']['id']);
                }
                $this->streams->entries->delete_entry($port['id'], 'user_port', 'ports');
            }

            //push the cfg to the host, restarting the service. Hangs if the host isn't connected.
            $this->connect->pushConfig($host->id);
            $this->connect->removeUser($entry['id']);
            $this->streams->entries->delete_entry($entry['id'], 'hosts', 'hosts');
        }

        // Stripe objects
        $customer = \Stripe\Customer::retrieve($this->Payignite->getCustomerId());

        if (!$customer->subscriptions) { //prevent error if subscription was canceled
            //direct user to purchase one
            redirect('/payignite/create');
        }
        $subscription = \Stripe\Subscription::retrieve($customer->subscriptions->data[0]->id);

        // Delete subscription and customer
        $subscription->cancel();
        $customer->delete();

        // Get all entries to delete
        $entries['backups']['backup_dest'] = $this->streams->entries->get_entries(array(
            'stream' => 'backup_dest',
            'namespace' => 'backups',
            'where' => 'default_backups_backup_dest.created_by = '.$this->current_user->id,
        ));

        $entries['ports']['user_port'] = $this->streams->entries->get_entries(array(
            'stream' => 'user_port',
            'namespace' => 'ports',
            'where' => 'default_ports_user_port.created_by = '.$this->current_user->id,
        ));

        $entries['api']['keys'] = $this->streams->entries->get_entries(array(
            'stream' => 'keys',
            'namespace' => 'api',
            'where' => 'default_api_keys.created_by = '.$this->current_user->id,
        ));

        $entries['license']['license_serials'] = $this->streams->entries->get_entries(array(
            'stream' => 'license_serials',
            'namespace' => 'license',
            'where' => 'default_license_license_serials.created_by = '.$this->current_user->id,
        ));

        $entries['hosts']['hosts'] = $this->streams->entries->get_entries(array(
            'stream' => 'hosts',
            'namespace' => 'hosts',
            'where' => 'default_hosts_hosts.created_by = '.$this->current_user->id,
        ));

        $entries['hosts']['host_users'] = $this->streams->entries->get_entries(array(
            'stream' => 'host_users',
            'namespace' => 'hosts',
            'where' => 'default_hosts_host_users.created_by = '.$this->current_user->id,
        ));

        $entries['hosts']['host_group'] = $this->streams->entries->get_entries(array(
            'stream' => 'host_group',
            'namespace' => 'hosts',
            'where' => 'default_hosts_host_group.created_by = '.$this->current_user->id,
        ));

        $entries['hosts']['host_bands'] = $this->streams->entries->get_entries(array(
            'stream' => 'host_band',
            'namespace' => 'hosts',
            'where' => 'default_hosts_host_band.created_by = '.$this->current_user->id,
        ));

        $entries['logging']['logging'] = $this->streams->entries->get_entries(array(
            'stream' => 'logging',
            'namespace' => 'logging',
            'where' => 'default_logging_logging.created_by = '.$this->current_user->id,
        ));

        // Loop all the above $entries and delete
        foreach ($entries as $namespace => $stream) {
            foreach ($stream as $stream_name => $entries2) {
                foreach ($entries2['entries'] as $value) {
                    $this->streams->entries->delete_entry($value['id'], $stream_name, $namespace);
                }
            }
        }

        $this->streams->entries->delete_entry($id, 'subscriptions', 'payignite');

        $this->template
            ->title('Cancel Subscription')
            ->build('delete');
    }

    public function ajaxCoupon()
    {
        $coupon = \Stripe\Coupon::retrieve(strtoupper($this->input->post('coupon')));
        $subtotal = $this->input->post('subtotal');

        if ($coupon['valid']) {
            if ($coupon['amount_off']) {
                echo(($subtotal*100) - $coupon['amount_off']) /100;
            } elseif ($coupon['percent_off']) {
                $total = ((100 - $coupon['percent_off']) / 100) * $subtotal;
                echo number_format($total, 2);
            }
        } else {
            echo $subtotal;
        }
    }
}

/* End of file payignite.php */
