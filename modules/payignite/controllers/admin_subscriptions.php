<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module.
 *
 * @author
 * @website
 */
class Admin_subscriptions extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'subscriptions';

    protected $data;

    public function __construct()
    {
        parent::__construct();

        //$this->load->library('stripe/lib/stripe');
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));
        $this->load->model('payignite_m', 'Payignite');

        $this->lang->load('payignite');
        $this->load->driver('Streams');
    }

    public function index()
    {
        $data['entries'] = \Stripe\Plan::all(array());
        $this->template
                ->title($this->module_details['name'])
                ->build('admin/subscriptions/index', $data);
    }

    public function create()
    {
    }

    public function edit($id = 0)
    {
    }

    public function delete($id = 0)
    {
    }
}
