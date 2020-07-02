<?php //if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends Public_Controller
{
    //protected $data;
    private $s_key = 'okeydokeydoctorjonesholdontoyourpotatoes';

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

/* Card Expiration */
    public function card_expired($key)
    {
        if ($key != $this->s_key) {
            http_response_code(403);
            //header('HTTP/1.1 403 Forbidden', true, 403);
            die();
        }
        $now = strtotime('now');

        $params = array(
            'stream' => 'cards',
            'namespace' => 'payignite',
            'where' => "email_sent = 'false'",
        );
        $entries = $this->streams->entries->get_entries($params);

        foreach ($entries['entries'] as $row) {
            $entry_id = $row['id'];
            $customer_id = $row['card_customer_id'];
            $card_id = $row['card_id'];
            $card_last4 = $row['card_last4'];
            $exp_time = $row['exp_time'];
            $mail_time = $row['mail_time'];
            $customer_email = $row['customer_email'];
            $email_sent = $row['email_sent'];

            if ($now > $mail_time) {

                // Setting up the email
                $email_array = array(

                    // Info you want to be available to the email template and call with Lex {{ }} tags.
                    'card_last4' => $card_last4, // access via {{ card_last4 }}
                    'card_id' => $card_id,
                    'customer_id' => $customer_id,
                    'expire_time' => date('m/d/y', $exp_time),

                    // email info
                    'slug' => 'card-expire',  // The email template to use
                    'to' => $customer_email,
                    'reply-to' => Settings::get('contact_email'), // whom to reply
                    'from' => Settings::get('server_email'),  // address the email is from
                    'name' => Settings::get('site_name'),     // name the email is from
                );

                // Email user
                if (Events::trigger('email', $email_array, 'array')) {

                    // update field entry
                    $entry_data = array(
                        'email_sent' => 'true',
                    );
                    if ($this->streams->entries->update_entry($entry_id, $entry_data, 'cards', 'payignite')) {
                        http_response_code(200);
                        //header('HTTP/1.1 200 OK', true, 200);
                    } else {
                        http_response_code(500);
                        //header('HTTP/1.1 500 Internal Server Error', true, 500);
                    }
                }
            }
        }
    }

/* Subscription End */
    public function subscription_end($key)
    {
        if ($key != $this->s_key) {
            http_response_code(403);
            //header('HTTP/1.1 403 Forbidden', true, 403);
            die();
        }
        $now = strtotime('now');

        $params = array(
            'stream' => 'subscriptions',
            'namespace' => 'payignite',
            'where' => "sub_email_sent = 'false'",
        );
        $entries = $this->streams->entries->get_entries($params);

        foreach ($entries['entries'] as $row) {
            $entry_id = $row['id'];
            $customer_id = $row['sub_customer_id'];
            $subscription_id = $row['subscription_id'];
            $exp_time = $row['sub_exp_time'];
            $mail_time = $row['sub_mail_time'];
            $customer_email = $row['sub_customer_email'];
            $email_sent = $row['sub_email_sent'];

            if ($now > $mail_time) {

                // Setting up the email
                $email_array = array(

                    // Info you want to be available to the email template and call with Lex {{ }} tags.
                    'subscription_id' => $subscription_id,
                    'customer_id' => $customer_id,
                    'expire_time' => date('m/d/y', $exp_time),

                    // email info
                    'slug' => 'subscription-end',  // The email template to use
                    'to' => $customer_email,
                    'reply-to' => Settings::get('contact_email'), // whom to reply
                    'from' => Settings::get('server_email'),  // address the email is from
                    'name' => Settings::get('site_name'),     // name the email is from
                );

                // Email user
                if (Events::trigger('email', $email_array, 'array')) {

                    // update field entry
                    $entry_data = array(
                        'sub_email_sent' => 'true',
                    );
                    if ($this->streams->entries->update_entry($entry_id, $entry_data, 'subscriptions', 'payignite')) {
                        http_response_code(200);
                        //header('HTTP/1.1 200 OK', true, 200);
                    } else {
                        http_response_code(500);
                        //header('HTTP/1.1 500 Internal Server Error', true, 500);
                    }
                }
            }
        }
    }
    public function reset_visits($key)
    {
        if ($key != $this->s_key) {
            http_response_code(403);
        //header('HTTP/1.1 403 Forbidden', true, 403);
        die();
        }
    //get start times of visits
    $params = array(
                    'stream' => 'visits',
                    'namespace' => 'payignite',
                    );
        $result = $this->streams->entries->get_entries($params);

        foreach ($result['entries'] as $row) {
            $timeDiff = abs(date('U') - $row['created']);
            $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
        $numberDays = intval($numberDays);
            $divisible = ($numberDays / 30);
        //if it is not a float value, it is 30 days from the created date.
            if (!is_float($divisible) && $numberDays != 0) {
                //reset the visit_counter
                $entry_data = array(
                                'visit_used_visits' => 0,
                                );
                if ($this->streams->entries->update_entry($row['id'], $entry_data, 'visits', 'payignite')) {
                    http_response_code(200);
                }
            } else {
                http_response_code(500);
            }
        }
    }
}
