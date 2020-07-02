<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module.
 *
 * @author
 * @website
 */
class Admin_payments extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'payments';

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
        //$data['entries'] = Stripe_Charge::all();
        $data['entries'] = $this->Payignite->getAllEntries('Charge');
        $this->template
                ->title($this->module_details['name'])
                ->build('admin/payments/index', $data);
    }

    public function create()
    {
        $extra['title'] = 'lang:dropship:new';

        $extra = array(
            'return' => 'admin/dropship/categories/index',
            'success_message' => lang('dropship:submit_success'),
            'failure_message' => lang('dropship:submit_failure'),
            'title' => lang('dropship:categories:new'),
        );

        $this->streams->cp->entry_form('categories', 'dropship', 'new', null, true, $extra);
    }

    public function edit($id = 0)
    {
    }

    public function refund($id)
    {
        $data['entries'] = \Stripe\Charge::all();

        foreach ($data['entries'] as $row) {
            $id = $row->id;
        }

        $ch = \Stripe\Charge::retrieve($id);
        $ch->refund();
        header('location: '.$_SERVER['HTTP_REFERER']);
    }
}
