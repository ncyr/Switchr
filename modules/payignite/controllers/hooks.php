<?php //if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hooks extends Public_Controller
{
    //protected $data;

    public function __construct()
    {
        parent::__construct();

        //$this->load->library('stripe/lib/stripe');
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));

        $this->lang->load('payignite');
        $this->load->driver('Streams');
        $this->load->model('payignite_m', 'Payignite');
        $this->load->model('users/user_m', 'Users');
    }

    public function stripe_event()
    {
        $input = @file_get_contents('php://input');
        $event_json = json_decode($input);

        $event_id = $event_json->id;
        $event = \Stripe\Event::retrieve($event_id);
        $object = $event->data->object;
        $id = $event->data->object->id;

/* Charge Failed */
        if ($event->type == 'charge.failed') {
            $ch = \Stripe\Charge::retrieve($id);

            // Setting the variables
            $fail_code = $ch->failure_code;
            $fail_message = $ch->failure_message;
            $charge_id = $ch->id;
            $customer_id = $ch->customer;
            $invoice_id = $ch->invoice;
            $amount = '$'.substr_replace($ch->amount, '.', -2, -2);
            $card_id = $ch->card['id'];
            $card_last4 = $ch->card['last4'];
            $customer_email = $ch->receipt_email;
            /* Leave the following two here for future reference */
            //$admin          = $this->Users->get(array('id' => '1'));
            //$admin_email    = $admin->email;

            /* Get the subscription id */
            $sub = \Stripe\Invoice::retrieve($invoice_id);
            $sub_id = $sub->subscription;

            /* BEGIN: get email address of gym owners */
            $params = array(
                'stream' => 'visits',
                'namespace' => 'payignite',
                'where' => 'visit_subscription = '."'".$sub_id."'",
            );
            $entries = $this->streams->entries->get_entries($params);

            $gym_emails = null;
            foreach ($entries['entries'] as $row) {
                $gym_owner[] = $row['gym_id']['gym_owner_id'];
            }

            foreach ($gym_owner as $owner) {
                $gym_owners[] = $this->Users->get($owner);
            }

            foreach ($gym_owners as $o) {
                $gym_emails[] = $o->email;
            }

            $gym_emails = array_unique($gym_emails);
            /* END: get email address of gym owners */

            // Setting up the email
            $email_array = array(

                // Info you want to be available to the email template and call with Lex {{ }} tags.
                'card_last4' => $card_last4, // access via {{ card_last4 }}
                'fail_code' => $fail_code,
                'fail_message' => $fail_message,
                'card_id' => $card_id,
                'charge_id' => $charge_id,
                'customer_id' => $customer_id,
                'invoice_id' => $invoice_id,
                'amount' => $amount,

                // email info
                'slug' => 'charge-failed',  // The email template to use
                'to' => array($customer_email, Settings::get('contact_email'), $gym_emails),
                'reply-to' => Settings::get('contact_email'), // whom to reply
                'from' => Settings::get('server_email'),  // address the email is from
                'name' => Settings::get('site_name'),     // name the email is from
            );

            // Email user
            if (Events::trigger('email', $email_array, 'array')) {
                //http_response_code(200);
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
            }
        }

        // TESTING -- works
        /*
        elseif ($event->type == 'customer.created') {

            $cus = Stripe_Customer::retrieve($id);
            $customer_id    = $cus->id;
            //$customer     = $ch->customer;
            //$invoice      = $ch->invoice;
            //$amount       = '$' . substr_replace($ch->amount, '.', -2, -2);
            //$card_id      = $ch->card['id'];
            $customer_email = $cus->email;
            //$admin          = $this->Users->get(array('id' => '1'));
            //$admin_email    = $admin->email;

            // Setting up the email
            $email_array = array(

                // Info you want to be available to the email template and call with Lex {{ }} tags.
                'customer_id'   => $customer_id,

                // email info
                'slug'          => 'customer-created',  // The email template to use
                'to'            => array($customer_email, $admin_email, Settings::get('contact_email')),
                'reply-to'      => $admin_email, // whom to reply
                'from'          => Settings::get('server_email'),  // from whom the email is from
                'name'          => Settings::get('site_name'),
            );

            // email user
            if (Events::trigger('email', $email_array, 'array')) {
                http_response_code(200);
            }

        }
        */

/* Customer Card Created */
        if ($event->type == 'customer.card.created') {
            $cus = \Stripe\Customer::retrieve($object->customer);
            $card = $cus->cards->retrieve($id);

            // Setting the variables
            $customer_id = $card->customer;
            $card_id = $card->id;
            $card_last4 = $card->last4;
            $exp_month = $card->exp_month;
            $exp_year = $card->exp_year;
            $customer_email = $cus->email;

            // Setting up the email
            $email_array = array(

                // Info you want to be available to the email template and call with Lex {{ }} tags.
                'card_id' => $card_id,    // access via {{ card_id }}
                'card_last4' => $card_last4,
                'exp_month' => $exp_month,
                'exp_year' => $exp_year,
                'customer_id' => $customer_id,

                // email info
                'slug' => 'card-created',  // The email template to use
                'to' => array($customer_email, Settings::get('contact_email')),
                'reply-to' => Settings::get('contact_email'), // whom to reply
                'from' => Settings::get('server_email'),  // address the email is from
                'name' => Settings::get('site_name'),     // name the email is from
            );

            // convert string 'month/day/year' to Unix time
            $exp_time = strtotime("$exp_month/01/$exp_year");
            $mail_time = strtotime('-3 months', $exp_time); // 3 months before exp.

            // set up field entries
            $entry_data = array(
                'card_id' => $card_id,
                'card_last4' => $card_last4,
                'card_customer_id' => $customer_id,
                'customer_email' => $customer_email,
                'exp_time' => $exp_time,
                'mail_time' => $mail_time,
                'email_sent' => 'false',
            );

            // Insert entries to table
            if ($this->streams->entries->insert_entry($entry_data, 'cards', 'payignite')) {
                //http_response_code(200);
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
            }
        }

/* Customer Card Deleted */
        if ($event->type == 'customer.card.deleted') {

            // Setting the variables
            $card_id = $id;  // $id is grabbed from the JSON object sent by Stripe (see top of file)

            // get entry ID
            $params = array(
                'stream' => 'cards',
                'namespace' => 'payignite',
                'where' => 'card_id = '."'".$card_id."'",
            );
            $entries = $this->streams->entries->get_entries($params);
            //foreach ($entries['entries'] as $row) { $entry_id = $row['id']; }
            $entry_id = $entries['entries']['0']['id'];
            // Delete entry
            if ($this->streams->entries->delete_entry($entry_id, 'cards', 'payignite')) {
                //http_response_code(200);
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
            }
        }

/* Customer Subscription Created
*  CHANGE 'insert_entry' to 'update_entry'
*  need to use 'get_entries' with a 'where' paramater
*/
        if ($event->type == 'customer.subscription.created') {
            $cus = \Stripe\Customer::retrieve($object->customer);
            $sub = $cus->subscriptions->retrieve($id);

            // Setting the variables
            $customer_id = $sub->customer;
            $subscription_id = $sub->id;
            $exp_time = $sub->current_period_end;
            $customer_email = $cus->email;

            // Setting up the email
            $email_array = array(

                // Info you want to be available to the email template and call with Lex {{ }} tags.
                'subscription_id' => $subscription_id,  // access via {{ subscription_id }}
                'exp_time' => $exp_time,
                'customer_id' => $customer_id,
                );

            $mail_time = ($exp_time - 1296000); // 15 days before exp.

            $email_array = array(
                // email info
                'slug' => 'new-subscription-user-confirmation',  // The email template to use
                'to' => array($customer_email, Settings::get('contact_email')),
                'reply-to' => Settings::get('contact_email'), // whom to reply
                'from' => Settings::get('server_email'),  // address the email is from
                'name' => Settings::get('site_name'),     // name the email is from
            );

            // Email user
            Events::trigger('email', $email_array, 'array');
            $email_array['slug'] = 'new-subscription-admin-notification';
            Events::trigger('email', $email_array, 'array');

            // set up field entries
            /*
            $entry_data = array(
                'subscription_id'    => $subscription_id,
                'sub_customer_id'    => $customer_id,
                'sub_customer_email' => $customer_email,
                'sub_exp_time'       => $exp_time,
                'sub_mail_time'      => $mail_time,
                'sub_email_sent'     => 'false',
            );

            // Insert entries to table
            if ($this->streams->entries->insert_entry($entry_data, 'subscriptions', 'payignite')) {
            */
                //http_response_code(200);
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
            //}
        }

/* Customer Subscription Deleted */
        if ($event->type == 'customer.subscription.deleted') {

            // Setting the variables
            $subscription_id = $id;  // $id is grabbed from the JSON object sent by Stripe (see top of file)

            // get entry ID
            $params = array(
                'stream' => 'subscriptions',
                'namespace' => 'payignite',
                'where' => 'subscription_id = '."'".$subscription_id."'",
            );
            $entries = $this->streams->entries->get_entries($params);
            //foreach ($entries['entries'] as $row) { $entry_id = $row['id']; }
            $entry_id = $entries['entries']['0']['id'];
            // Delete entry
            if ($this->streams->entries->delete_entry($entry_id, 'subscriptions', 'payignite')) {
                //http_response_code(200);
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
            }
        }

        header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
    }
}
