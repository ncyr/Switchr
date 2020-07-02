<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * FAQ Module.
 *
 * This is a sample module for PyroCMS
 * that illustrates how to use the streams core API
 * for data management. It is also a fully-functional
 * FAQ module so feel free to use it on your sites.
 *
 * Most of these functions use the Streams API CP driver which
 * is designed to handle repetitive CP tasks, down to even loading the page.
 *
 * @author 		Adam Fairholm - PyroCMS Dev Team
 */
class Admin_forms extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'payignite';

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('stripe/lib/stripe');
        //Stripe::setApiKey(Settings::get('payignite_setting_stripe_key'));
        $this->load->model('payignite_m', 'Payignite');

        $this->lang->load('payignite');
        $this->load->driver('Streams');
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
        $data['entries'] = \Stripe\Invoice::all(array(
            'count' => 3,
        ));
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/index', $data);
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

    public function edit($id = 0)
    {
        $extra = array(
            'return' => 'admin/payignite',
            'success_message' => lang('payignite:submit_success'),
            'failure_message' => lang('payignite:submit_failure'),
            'title' => 'lang:payignite:edit',
        );

        $this->streams->cp->entry_form('invoices', 'payignite', 'edit', $id, true, $extra);
    }

    public function delete($id = 0)
    {
        $cu = \Stripe\Invoice::retrieve($id);
        $cu->delete();

        redirect('admin/payignite/');
    }
}
