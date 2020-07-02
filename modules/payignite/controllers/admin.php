<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module.
 *
 * @author
 * @website
 */
class Admin extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'payignite';

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('stripe/lib/stripe');
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));
        //require_once '/var/www/addons/shared_addons/libraries/stripe/init.php';
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

    /**
     * List all FAQs using Streams CP Driver.
     *
     * We are using the Streams API to grab
     * data from the payignites database. It handles
     * pagination as well.
     */
    public function index()
    {
        $data['entries'] = $this->Payignite->getAllEntries('Invoice');
        //$data['entries'] = Stripe_Invoice::all(array('limit' => 100));
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/index', $data);
    }

    public function view($id)
    {
        $data['entries'] = \Stripe\Invoice::retrieve($id);
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/view', $data);
    }

    public function index_alt()
    {
        // Get our entries. We are simply specifying
        // the stream/namespace, and then setting the pagination up.
        $params = array(
            'stream' => 'invoices',
            'namespace' => 'payignite',
            'paginate' => 'yes',
            'limit' => 4,
            'pag_segment' => 4,
        );
        $data['payignite'] = $this->streams->entries->get_entries($params);

        // Build the page. See views/admin/index.php
        // for the view code.
        $this->template
                    ->title($this->module_details['name'])
                    ->build('admin/index', $data);
    }

    public function create()
    {
        if ($this->input->post()) {
            $customer = $this->input->post('customer');
            \Stripe\Invoice::create(array(
                'customer' => $customer,
            ));
        } else {
        }
    }

    public function close($id)
    {
        $invoice = \Stripe\Invoice::retrieve($id);
        $invoice->closed = true;
        $invoice->save();
        header('location: '.$_SERVER['HTTP_REFERER']);
    }

    /*
    public function delete($id)
    {
        $ii = Stripe_InvoiceItem::retrieve($id);
        $ii->delete();
        header("location: " . $_SERVER['HTTP_REFERER']);
        //redirect('admin/payignite/');
    }
    */
}
