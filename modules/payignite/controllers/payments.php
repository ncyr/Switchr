<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Payments extends Public_Controller
{
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
        $params = array(
            'stream' => 'customer_user',
            'namespace' => 'payignite',
            'where' => 'customer_user_id ='.$this->current_user->id,
        );

        $data['current_customer'] = $this->streams->entries->get_entries($params);

        $data['entries'] = \Stripe\Charge::all();
        $this->template
            ->title($this->module_details['name'])
            ->build('payments/index', $data);
    }
}

/* End of file payignite.php */
