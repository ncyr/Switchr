<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module.
 *
 * @author
 * @website
 */
class Admin_customers extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'customers';

    protected $data;

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('stripe/lib/stripe');
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));
        //$this->load->helper('url');
        //require_once base_url().$this->module_details['path'].'/libraries/stripe/init.php';
        //\Stripe\Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));

        $this->lang->load('payignite');
        $this->load->driver('Streams');
        $this->load->model('payignite_m', 'Payignite');

        $this->template
            ->append_js('module::jquery.1_10_2.min.js')
            ->append_js('module::jquery-ui-1.10.4.custom.min.js')
            ->append_css('module::jquery-ui-1.10.4.custom.min.css')
            ->append_js('module::dataTables.js')
            ->append_css('module::dataTables.css')
            ->append_js('module::payignite.js')
            ->append_css('module::payignite.css');
    }

    public function index()
    {
        //$data['entries'] = Stripe_Customer::all(array('limit' => 100));
        $data['entries'] = $this->Payignite->getAllEntries('Customer');
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/customers/index', $data);
    }

    public function create()
    {
        $this->load->helper('form');
        $data['Coupons'] = \Stripe\Coupon::all();
        $data['entries'] = \Stripe\Plan::all();
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/customers/create', $data);
    }

    public function edit($id)
    {
        //$data['entries'] = Stripe_Customer::all();

        //foreach ($data['entries'] as $row) {
        //    $id = $row->id;
        //}

        $data['customer'] = \Stripe\Customer::retrieve($id);
/*        $cu_data = array(
                        'email'             => $email,
                        'account_balance'   => $account_balance,
                        'plan'      => $plan,
                        'quantity'  => $plan_quantity,
                        'trial_end' => $trial_end,
                        'coupon'    => $coupon,

                        // CC Data
                        'cards' => array(
                                        'name'      => $card_name,
                                        'number'    => $card_number,
                                        'cvc'       => $card_cvc,
                                        'exp_month' => $card_exp_month,
                                        'exp_year'  => $card_year,
                                        'address_line1'  => $card_add1,
                                        'address_line2'  => $card_add2,
                                        'address_city'   => $card_city,
                                        'address_state'  => $card_state,
                                        'address_zip'    => (string)$card_zip,
                        )
                    );
*/
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/customers/edit', $data);
        //$cu->save();
        //header("location: " . $_SERVER['HTTP_REFERER']);
    }

    public function view($id)
    {
        $customer['details'] = \Stripe\Customer::retrieve($id);
        $customer['invoices'] = \Stripe\Invoice::all(array(
            'limit' => 100,
            'customer' => $id,
        ));

        $this->template
            ->title($this->module_details['name'])
            ->build('admin/customers/view', $customer);
    }

    public function delete($id = 0)
    {
        /*
        $data['entries'] = \Stripe\Customer::all();
        if (isset($data['entries'])) {
            foreach ($data['entries'] as $row) {
                $id = $row->id;
            }
        }
        */
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

        // Loop all the above $entries and delete
        foreach ($entries as $namespace => $stream) {
            foreach ($stream as $stream_name => $entries2) {
                foreach ($entries2['entries'] as $value) {
                    $this->streams->entries->delete_entry($value['id'], $stream_name, $namespace);
                }
            }
        }

        // Delete subscription from database
        $this->streams->entries->delete_entry($payignite_entries['entries'][0]['id'], 'subscriptions', 'payignite');

        header('location: '.$_SERVER['HTTP_REFERER']);
    }
}
