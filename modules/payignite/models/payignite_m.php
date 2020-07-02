<?php
// require_once dirname(__FILE__).'/../libraries/vendor/patreon/patreon/src/patreon.php';
// use Patreon\API;
// use Patreon\OAuth;

defined('BASEPATH') or exit('No direct script access allowed');

class Payignite_m extends MY_Model
{
    private $client_secret;

    public function __construct()
    {
        parent::__construct();

        require_once dirname(__FILE__).'/../libraries/stripe/init.php';
        \Stripe\Stripe::setApiKey(Settings::get('payignite_setting_secret_key'));


        // $this->access_token  = '-7xIKQKy1Tlut74zpGLB3tmcBxcA1Pi4LeDMWhmouPA';
        // $this->refresh_token = 'on4skuS2hDjInCyqsrB3E5N1O7d8D4kYL_ZAdbWKJ64';
        // $this->client_id     = '83118571230b5c95910ce96595568cd50e3a6ef57e79a6a579e46092d3321af6';
        // // $this->client_secret = 'c45803fcd42019049d1c77ec21199be1613429b9494e8985c9b48f636e180877';
        // $this->client_secret = 'bvlReT_NbRLqBrdjfEwG68eRpj66fvt7TapJvqyK2BI2amDWWow7V7anGibeWY0R';
        // $this->creator_id    = 'switchrio';

        // $oauth_client = new \Patreon\OAuth($client_id, $client_secret);
        // $api_client = new \Patreon\API($access_token);


        $this->load->model('payignite/plan_m', 'Plan');
        $this->load->driver('Streams');
    }

    // public function patreon()
    // {
    //     $oauth_client = new Patreon\OAuth($this->client_id, $this->client_secret);
    //
    //     // Replace http://localhost:5000/oauth/redirect with your own uri
    //     $redirect_uri = "https://76.92.182.208/hosts";
    //     // Make sure that you're using this snippet as Step 2 of the OAuth flow: https://www.patreon.com/platform/documentation/oauth
    //     // so that you have the 'code' query parameter.
    //     // $tokens = $oauth_client->get_tokens($_GET['code'], $redirect_uri);
    //     // $access_token = $tokens['access_token'];
    //     // $refresh_token = $tokens['refresh_token'];
    //
    //     // $api_client = new Patreon\API($access_token);
    //     // $patron_response = $api_client->fetch_user();
    //     // $patron = $patron_response['data'];
    //     // $included = $patron_response['included'];
    //     $pledge = null;
    //     if ($included != null) {
    //         foreach ($included as $obj) {
    //             if ($obj["type"] == "pledge" && $obj["relationships"]["creator"]["data"]["id"] == $this->creator_id) {
    //                 $pledge = $obj;
    //                 break;
    //             }
    //         }
    //     }
    //     var_dump($oauth_client->get_tokens($_GET['code'], $redirect_uri));
    //     die;
    // }

    public function getCustomerId()
    {
        $params = array(
            'stream' => 'subscriptions',
            'namespace' => 'payignite',
            'where' => SITE_REF.'_payignite_subscriptions.created_by = '.$this->current_user->id,
            'limit' => 1,
        );

        $entries = $this->streams->entries->get_entries($params);

        return (!empty($entries['entries'][0])) ? $entries['entries'][0]['sub_customer_id'] : null;
    }

    /**
     * Amount of current hosts used.
     *
     * @return int
     */
    public function hostsUsed()
    {
        $current_hosts = $this->streams->entries->get_entries(array(
            'stream' => 'hosts',
            'namespace' => 'hosts',
            'where' => SITE_REF.'_hosts_hosts.created_by = '.$this->current_user->id,
        ));
        $current_hosts_amount = count($current_hosts['entries']);

        return $current_hosts_amount;
    }

    /**
     * How many more hosts the user can add.
     *
     * @return int
     */
    public function hostsAvailable()
    {
        $plan = $this->Plan->getPlan();
        if ($plan == false) {
            return 0;
        }
        $current_hosts_amount = $this->hostsUsed();
        $plan_hosts_amount = $this->Plan->planParse($plan->id)->hosts;
        $hosts_available = $plan_hosts_amount - $current_hosts_amount;

        return $hosts_available;
    }

    // /**
    //  * Amount of current backups used.
    //  *
    //  * @return array ['s3' => int, 'ftp' => int, 'local' => int]
    //  */
    // public function backupsUsed()
    // {
    //     $current_backups = $this->streams->entries->get_entries(array(
    //         'stream' => 'backup_dest',
    //         'namespace' => 'backups',
    //         'where' => 'default_backups_backup_dest.created_by = '.$this->current_user->id,
    //     ));
    //
    //     $current_s3_amount = 0;
    //     $current_ftp_amount = 0;
    //     $current_local_amount = 0;
    //
    //     foreach ($current_backups['entries'] as $backup) {
    //         switch ($backup['backup_dest_type']) {
    //             case 'awss3':
    //                 $current_s3_amount += 1;
    //                 break;
    //             case 'ftp':
    //                 $current_ftp_amount += 1;
    //                 break;
    //             case 'local':
    //                 $current_local_amount += 1;
    //                 break;
    //             default:
    //                 break;
    //         }
    //     }
    //
    //     $backups_used['s3'] = $current_s3_amount;
    //     $backups_used['ftp'] = $current_ftp_amount;
    //     $backups_used['local'] = $current_local_amount;
    //
    //     return $backups_used;
    // }
    //
    // /**
    //  * How many more backups the user can add.
    //  *
    //  * @return array ['s3' => int, 'ftp' => int, 'local' => int]
    //  */
    // public function backupsAvailable()
    // {
    //     $plan = $this->Plan->getPlan();
    //
    //     if ($plan == false) {
    //         return false;
    //     }
    //
    //     $backups_used = $this->backupsUsed();
    //
    //     $plan_s3_amount = $this->Plan->planParse($plan['sub_plan_id'])->s3;
    //     $plan_ftp_amount = $this->Plan->planParse($plan['sub_plan_id'])->ftp;
    //     $plan_local_amount = $this->Plan->planParse($plan['sub_plan_id'])->local;
    //
    //     $backups_available['s3'] = $plan_s3_amount - $backups_used['s3'];
    //     $backups_available['ftp'] = $plan_ftp_amount - $backups_used['ftp'];
    //     $backups_available['local'] = $plan_local_amount - $backups_used['local'];
    //
    //     return $backups_available;
    // }

    /**
     * Uses Stripe\[$object]::all() to get all entries of $object
     * Example: getAllEntries('Invoice') == Stripe\Invoice::all().
     *
     * @param string $object Which class to use.
     * @param string $cu_id  Customer ID
     *
     * @return array
     */
    public function getAllEntries($object, $cu_id = false)
    {
        $data['data'] = array();
        $cmd = '\\Stripe\\'.$object;

        if ($cu_id === false) {
            $stripe = $cmd::all(array('limit' => 100));
        } else {
            $stripe = $cmd::all(array('limit' => 100, 'customer' => $cu_id));
        }

        while ($stripe['has_more'] === true) {
            $data2 = $stripe['data'];
            $end = end($data2);         // move the internal pointer to the end of the array
            $data_id = $end['id'];
            $d[] = $data2;

            if ($cu_id === false) {
                $stripe = $cmd::all(array('limit' => 100, 'starting_after' => $data_id));
            } else {
                $stripe = $cmd::all(array('limit' => 100, 'starting_after' => $data_id, 'customer' => $cu_id));
            }
        }

        // Do it one more time to get the last group of entries...
        $data2 = $stripe['data'];
        $end = end($data2);         // move the internal pointer to the end of the array
        $data_id = $end['id'];
        $d[] = $data2;

        /* I don't think this last call does anything... need to test over 100 entries.
        if ($cu_id === false) {
            $stripe = $cmd::all(array('limit' => 100, 'starting_after' => $data_id));
        } else {
            $stripe = $cmd::all(array('limit' => 100, 'starting_after' => $data_id, 'customer' => $cu_id));
        }
        */

        // Put all entries into one array called 'data'
        foreach ($d as $v1) {
            foreach ($v1 as $v2) {
                $data['data'][] = $v2;
            }
        }

        return $data;
    }

    public function createOrder($customer = false)
    {
        //$customer = $this->getCustomerByUserId($this->current_user->id);

        //$gym_visits = $this->input->post('gym_visits');
        //$gyms = $this->input->post('gym_name');

        //$accountbalance = $this->input->post('account_balance');
        //$accountbalance == false ? $account_balance = null :
        //$account_balance = (int) str_replace('.', '', $accountbalance);

        //$this->input->post('plan_quantity') == false ? $plan_quantity = null :
        //$plan_quantity = (int) $this->input->post('plan_quantity');

        //$this->input->post('trial_end') == false ? $trial_end = null :
        //$trial_end = strtotime($this->input->post('trial_end')); //+ 82799;

        //$this->input->post('coupon_code') == 'none' ? $coupon = null :
        //$coupon_id = $this->input->post('discount');

        //$cardnumber = $this->input->post('card_number');
        //$card_number = preg_replace('/[^0-9]/', '', $cardnumber);
        $stripe_token = $this->input->post('stripeToken');

        //get the customer object
        $cu = \Stripe\Customer::retrieve($customer['customer_user_customer_id']);

        if ($cu == false) {
            \Stripe\Customer::create(array(
                //'description' => 'Customer for test@example.com',
                'source' => $stripe_token, // obtained with Stripe.js
            ));
        }

        /*
        //get discount
        if ($coupon_id != 'MONTHLY' && $coupon_id != '') {
            $coupon = \Stripe\Coupon::retrieve($coupon_id);
        } else {
            $coupon = (object) 0;

            $coupon->percent_off = 0;
        }
        */

        /*
        //!need to set this up better later
        if ($cu->deleted == true) {
            return false;
        }
        */

        //we'll create the plan here if it doesn't exist based on the total visit costs
        $plan = $this->getAllEntries('Plan');

        /*
        //global $plan_exists;
        $plan_exists = false;

        foreach ($plan['data'] as $row) {
            if ($row->name == $cu->id) {
                $plan_exists = true;
            }
        }
        */

        //global $subtotal;
        //global $total;

        //$subtotal = null;
        //$total = null;

        /*
        if ($gym_visits > 0) {
            global $gymData;
            $gymData = array();
            ///theres got to be some sort of array func we can use here
            for ($i = 0; $i <= (count($gyms) - 1); ++$i) {
                if ($gyms[$i] != '' && $gyms[$i] != null && $gyms[$i] != 0) {
                    if (!isset($gymData[$gyms[$i]])) {
                        $gymData[$gyms[$i]] = $gym_visits[$i];
                    } else {
                        $gymData[$gyms[$i]] = ($gym_visits[$i] + $gymData[$gyms[$i]]);
                    }
                }
            }
            foreach ($gymData as $gym => $value) {
                $subtotal = ($this->getGym($gym)->gym_price * $value);

                if ($subtotal != null) {
                    $total = ($subtotal + $total);
                }
            }
            $discAmount = ($coupon->percent_off / 100) * $total;
            $total = ($total - $discAmount);
        }
        */

        /*
        $stripe_total = str_replace('.', '', number_format($total, 2));

        if ($plan_exists == false) {
            $plan_id = $this->current_user->id;
            $plan_name = $cu->id;
        } else {
            $plan_id = $this->current_user->id.'-'.rand(111111, 999999);
            $plan_name = $cu->id;
        }
        */

        /*
        $interval_count_raw = $this->input->post('discount');
        switch ($interval_count_raw) {
            case 'MONTHLY':
            $interval_count = 1;
            break;
            case '3MONTHS':
            $interval_count = 3;
            break;
            case '6MONTHS':
            $interval_count = 6;
            break;
            case '12MONTHS':
            $interval_count = 12;
            break;
            default:
            echo 'Uh-oh... invalid interval_count_raw';
        }
        */

        /*
        // There should already be a Plan. Create Customer instead, or add Subscription to existing Customer.
         \Stripe\Plan::create(array(
                 'amount' => $stripe_total,
                 'interval' => 'month',
                 //"interval_count" => (int)$interval_count,
                 'name' => $plan_name,
                 'currency' => 'usd',
                 'id' => $plan_id,
                 )
             );
         */

        //add subscription of a plan to a customer customer with the discount object
        $sub = $cu->subscriptions->create(array('plan' => $plan_id));

        if ($sub->status == 'active') {
            $customer_email = $cu->email;

            // Email user new subscription information
            $email_array = array(

                'slug' => 'new-subscription-user-confirmation',  // The email template to use
                // No need to have ('to') as an array AND a separate admin email... pick one.
                'to' => array($customer_email, Settings::get('contact_email')),
                'reply-to' => Settings::get('contact_email'),
                'from' => Settings::get('server_email'),
                'name' => Settings::get('site_name'),     // name the email is from
            );
            $email_array['full_name'] = $cu->name;
            //$email_array['interval'] = $interval_count;
            $email_array['email'] = $cu->email;
            //$email_array['visit_count'] = $value;
            //$email_array['visit_discount'] = $coupon->percent_off;
            $email_array['date'] = date('F j, Y');

            //foreach ($gymData as $gym => $visits) {
            //    $email_array['gyms'][] = array('gym_name' => $this->getGym($gym)->gym_name, 'gym_visits' => $visits);
            //}

            //$email_array['duration'] = $coupon_id;

            Events::trigger('email', $email_array, 'array');

            // Email admin new subscription notification
            $email_array['slug'] = 'new-subscription-admin-notification';
            $email_array['to'] = Settings::get('contact_email');
            Events::trigger('email', $email_array, 'array');

            $mail_time = ($sub->current_period_end - 1296000); // 15 days before exp.

            $subscription_params = array(
                            'subscription_id' => $sub->id,
                            'sub_interval' => $interval_count,
                            'sub_customer_id' => $cu->id,
                            'sub_customer_email' => $cu->email,
                            'sub_exp_time' => $sub->current_period_end,
                            'sub_mail_time' => $mail_time,
                            'sub_email_sent' => 'false',
                            );
            $this->streams->entries->insert_entry($subscription_params, 'subscriptions', 'payignite');

            /*
            if ($gym_visits > 0) {
                global $gymData;

                foreach ($gymData as $key => $value) {
                    $params = array(
                                    'gym_id' => $key,
                                    'customer_id' => $this->current_user->id,
                                    'visit_count' => $value,
                                    'visit_discount' => $coupon->percent_off,
                                    'visit_price' => $this->getGym($key)->gym_price,
                                    'visit_subscription' => $sub->id,
                                    'visit_used_vists' => 0,
                                    'visit_sub_interval' => $interval_count,
                                    );
                    $this->streams->entries->insert_entry($params, 'visits', 'payignite');

                    // Email gym new subscription notification
                    $gym_info = $this->getGym($key);
                    $gym_owner_id = $gym_info->gym_owner_id;
                    $gym_user_info = $this->Users->get(array('id' => $gym_owner_id));
                    $gym_email = $gym_user_info->email;

                    $email_array['slug'] = 'new-subscription-gym-notification';
                    $email_array['to'] = $gym_email;
                    Events::trigger('email', $email_array, 'array');
                }
            }
            */
            return true;
        } else {
            return false;
        }
    }

    public function randomString($length = 12)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    public function getSubscription()
    {
        $cus_id = $this->getCustomerId();
        $cu = \Stripe\Customer::retrieve($cus_id);
        $subscription = $cu->subscriptions->retrieve($cu->subscriptions->data[0]->id);

        return $subscription;
    }
}
