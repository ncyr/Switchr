<?php defined('BASEPATH') or exit('No direct script access allowed');

class Test_m extends MY_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('stripe/lib/stripe');
        Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));
    }

    public function getCustomerByUserId($user_id)
    {
        $customer = Stripe_Customer::create($entry_data);

        $params = array(
            'stream' => 'customer_user',
            'namespace' => 'payignite',
            'where' => 'customer_user_id = '.$user_id,
            'limit' => 1,
        );

        $entries = $this->streams->entries->get_entries($params);
        var_dump($entries['entries']);
        die();

        return $entries;
    }
}
