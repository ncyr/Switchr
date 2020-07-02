<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Order extends Public_Controller
{
    protected $customer;

    public function __construct()
    {
        parent::__construct();
        $this->lang->load('payignite');
        $this->load->model('payignite_m', 'Payignite');
    }

    public function index()
    {
        $data['entries']['data'] = array();
        if (isset($this->current_user->id)) {
            if ($this->input->post()) {
                $data['customer'] = $this->Payignite->getCustomerByUserId($this->current_user->id);
                $this->Payignite->createOrder($data['customer']['customer_user_customer_id']);
                redirect($this->module);
                $this->template->title($this->module_details['name'])
                    ->build('order/confirm.php', $data);
            } else {
                $this->template
                    ->append_js('module::jquery-ui-1.10.4.custom.min.js')
                    ->append_js('module::dataTables.js')
                    ->append_css('module::dataTables.css')
                    ->append_css('module::jquery-ui-1.10.4.custom.min.css')
                    ->append_js('module::payignite.js')
                    ->append_css('module::payignite.css')

                    ->title($this->module_details['name'])
                    ->build('order/order.php', $data);
            }
        } else {
            redirect('users/login');
        }
    }

    public function getTotalPrice()
    {
        //$gyms = $this->input->post('gym_name');
        //$gym_visits = $this->input->post('gym_visits');
        $couponId = $this->input->post('discount');

        //global $gymData;

        //$gymData = array();
        //$total = 0;

        //get discount
        /*
        if ($couponId != 'MONTHLY' && $couponId != '') {
            $coupon = \Stripe\Coupon::retrieve($couponId);
        } else {
            $coupon = (object) 0;
            $coupon->percent_off = 0;
        }
        */

        //theres got to be some sort of array func we can use here
        /*
        for ($i = 0; $i <= (count($gyms) - 1); ++$i) {
            if ($gyms[$i] != '' && $gyms[$i] != null && $gyms[$i] != 0) {
                if (!isset($gymData[$gyms[$i]])) {
                    $gymData[$gyms[$i]] = $gym_visits[$i];
                } else {
                    $gymData[$gyms[$i]] = ($gym_visits[$i] + $gymData[$gyms[$i]]);
                }
            }
        }
        foreach ($gymData as $gym => $value) {
            $subTotal = ($this->Payignite->getGym($gym)->gym_price * $value);

            if ($subTotal != null) {
                $total = ($subTotal + $total);
            }
        }
        $discAmount = ($coupon->percent_off / 100) * $total;
        $total = ($total - $discAmount);
        echo number_format($total, 2);
        */
    }

    public function ajax_order()
    {
        $data['entries']['data'] = array();
        if (isset($this->current_user->id)) {
            if ($this->input->post()) {
                $data['customer'] = $this->Payignite->getCustomerByUserId($this->current_user->id);
                $orderStatus = $this->Payignite->createOrder($data['customer']['customer_user_customer_id']);

                //echo $orderStatus;
                return $orderStatus;
            }
        } else {
            //echo false;
            return false;
        }
        //die();
    }
}
