<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Visits extends Public_Controller
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
    }

    public function use_visit($visit_id, $visits_purchased, $current_visits = 0)
    {
        $is_owner = $this->Payignite->isVisitOwner($visit_id);
        if ($is_owner && $current_visits < $visits_purchased) {
            $entry_data = array(
        'visit_used_visits' => ($current_visits + 1),
        );
            $this->streams->entries->update_entry($visit_id, $entry_data, 'visits', 'payignite');
            redirect($this->module);
        } else {
            redirect($this->module);
        }
    }
    public function pay_all_visits($visit_id, $current_visits = false, $remove = false)
    {
        if ($remove == 'remove') {
            --$current_visits;
        } else {
            ++$current_visits;
        }
        $entry_data = array(
        'visit_paid' => $current_visits,
        );
        $this->streams->entries->update_entry($visit_id, $entry_data, 'visits', 'payignite');
        redirect($this->module);
    }
}
