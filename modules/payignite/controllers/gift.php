<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module.
 *
 * @author
 * @website
 */
class Gift extends Public_Controller
{
    // This will set the active section tab
    //protected $section = 'gift';

    protected $data;

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('stripe/lib/stripe');
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));

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
        $this->load->helper('form');
        $data['entries'] = \Stripe\Coupon::all();
        $this->template
                ->title($this->module_details['name'])
                ->build('gift/index', $data);
    }
}
