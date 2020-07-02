<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module.
 *
 * @author
 * @website
 */
class Admin_coupons extends Admin_Controller
{
    // This will set the active section tab
    protected $section = 'coupons';

    protected $data;

    public function __construct()
    {
        parent::__construct();
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
        //$data['entries'] = Stripe_Coupon::all();
        $data['entries'] = $this->Payignite->getAllEntries('Coupon');
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/coupons/index', $data);
    }

    public function create()
    {
        $this->load->helper('form');
        $data['entries'] = \Stripe\Coupon::all();
        $this->template
            ->title($this->module_details['name'])
            ->build('admin/coupons/create', $data);
    }

    public function delete($id)
    {
        // $data['entries'] = \Stripe\Coupon::all();

        // foreach ($entries['data'] as $row) {
        //     $id = $row->id;
        // }

        $cpn = \Stripe\Coupon::retrieve($id);
        $cpn->delete();
        header('location: '.$_SERVER['HTTP_REFERER']);
    }
}
