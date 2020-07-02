<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @category events
 *
 * @author PyroCMS Dev Team
 */
class Events_Payignite
{
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();

        //Events::trigger('post_user_register', $id)
        // Events::register('post_user_register', array($this, 'create_stripe_customer'));
    }

    public function create_stripe_customer($user)
    {
        // Load resources
        $this->ci->load->driver('Streams');
        $this->ci->load->model('payignite/payignite_m', 'Payignite');
        //$this->ci->load->library('stripe/lib/stripe'); // This lib needs to be in root lib folder "..addons/shared-addons/libraries"
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));
        //require_once '/var/www/addons/shared_addons/libraries/stripe/init.php';
        //\Stripe\Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));

        // Setup info to insert into Stripe and database.
        $customer = \Stripe\Customer::create(array('email' => $user->email));
        $this->ci->streams->entries->insert_entry(array('sub_customer_id' => $customer->id), 'subscriptions', 'payignite');
    }
}
/* End of file events.php */
