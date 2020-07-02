<?php

defined('BASEPATH') or exit('No direct script access allowed');

// class Plan_m extends MY_Model
class Plan_m extends Payignite_m
{
    public function __construct()
    {
        // parent::__construct();

        // require_once dirname(__FILE__).'/../libraries/stripe/init.php';
        // \Stripe\Stripe::setApiKey(Settings::get('payignite_setting_secret_key'));

        // $this->load->model('payignite/payignite_m', 'Payignite');
    }

    /**
     * Get Plan belonging to user.
     * @return array
     */
    public function getPlan()
    {
        // Lookup for regular Stripe subscriptions.
        $params = array(
            'stream' => 'subscriptions',
            'namespace' => 'payignite',
            'where' => SITE_REF.'_payignite_subscriptions.created_by = '.$this->current_user->id,
            'limit' => 1
        );
        $result = $this->streams->entries->get_entries($params);

        if (!empty($result['entries'][0])) {
            // $subscription = $this->Payignite->getSubscription();
            $plan = \Stripe\Plan::retrieve($result['entries'][0]['sub_plan_id']);
            return $plan;
        }

        // Lookup for Patreon subscriptions.
        $params = array(
            'stream' => 'subscriptions',
            'namespace' => 'payignite',
            'where' => SITE_REF.'_payignite_subscriptions.sub_customer_id = '. "'{$this->current_user->email}'",
            'limit' => 1
        );
        $result = $this->streams->entries->get_entries($params);

        if (!empty($result['entries'][0])) {
            // $subscription = $this->Payignite->getSubscription();
            $plan = $result['entries'][0];
            return $plan;
        }

        return false;
    }

    /**
     * Retrieves or creates a Stripe Plan.
     * @param string $plan_id Stripe Plan ID
     * @return object Stripe Plan
     */
    public function createPlan($plan_id = null)
    {
        try {
            $stripe_plan = \Stripe\Plan::retrieve($plan_id);
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $err = $body['error'];

            // If plan doesn't exist, create it.
            if (($e->getHttpStatus() === 404)               // Super-
            && ($err['type'] == 'invalid_request_error')    // strict-
            && ($err['param'] == 'plan')) {                 // checking... because I'm cool like that.
                $stripe_plan = \Stripe\Plan::create(array(
                    'amount' => $this->planAmount($plan_id) * 100,  // Convert to cents.
                    'interval' => 'month',  // Currently there is no 'interval_count', defaults to 1 ...
                    'name' => $plan_id,
                    'currency' => 'usd',
                    'id' => $plan_id,
                    'statement_descriptor' => 'Switchr Plan',
                ));
            }
        }

        return $stripe_plan;
    }

    /**
     * Total cost of the Plan.
     * @param string $plan_id Stripe Plan ID
     * @return int Total cost of Plan
     */
    public function planAmount($plan_id)
    {
        $this->planParse($plan_id);

        // Hosts: $35 or $5 each
        if (strpos($plan_id, 'H') !== false) {
            $hosts = $this->hosts * 5;
        } elseif (strpos($plan_id, 'R') !== false) {
            $hosts = $this->hosts * 5;
        }
        // Backups: $5 each
        // $s3 = $plan->s3 * 5;
        // $ftp = $plan->ftp * 5;
        // $local = $plan->local * 5;

        $total = $hosts; //+ $s3 + $ftp + $local;

        return $total;
    }

    /**
     * Get the amount of each service making up the plan.
     * @param string $plan_id Stripe Plan ID
     * @return object(strings) Amount of each service
     */
    public function planParse($plan_id)
    {
        // Hosts
        if (strpos($plan_id, 'H') !== false) {
            $this->hosts = $this->getStringBetween($plan_id, 'H');
        } elseif (strpos($plan_id, 'R') !== false) {
            $this->hosts = $this->getStringBetween($plan_id, 'R');
        }
        // Backups
        // $this->s3 = $this->getStringBetween($plan_id, 'S', 'FTP');
        // $this->ftp = $this->getStringBetween($plan_id, 'FTP', 'L');
        // $this->local = $this->getStringBetween($plan_id, 'L');

        return $this;
    }

    /**
     * Helper method to parse Plan IDs.
     * Example:
     * $str = 'H10S1FTP2L3';.
     *
     * $hosts = get_string_between($str, 'H', 'S');  Returns '10'.
     * $s3 = get_string_between($str, 'S', 'FTP');   Returns '1'.
     * $ftp = get_string_between($str, 'FTP', 'L');  Returns '2'.
     * $local = get_string_between($str, 'L');       Returns '3'.
     *
     * @param string $string Whole string
     * @param string $start  Between here...
     * @param string $end    and here
     * @return string String between $start and $end
     */
    public function getStringBetween($string, $start, $end = false)
    {
        $string = ' '.$string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        if ($end === false) {
            return substr($string, $ini);
        }

        return substr($string, $ini, $len);
    }
}
