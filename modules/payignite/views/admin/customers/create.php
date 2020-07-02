<?php //if the form is being submitted, do this:

//date_default_timezone_set(EST); // for $trial_end...

if ($this->input->post('submit')) {
    $accountbalance = $this->input->post('account_balance');
    $accountbalance == false ? $account_balance = null :
    $account_balance = (int) str_replace('.', '', $accountbalance);

    $this->input->post('plan_quantity') == false ? $plan_quantity = null :
    $plan_quantity = (int) $this->input->post('plan_quantity');

    $this->input->post('trial_end') == false ? $trial_end = null :
    $trial_end = strtotime($this->input->post('trial_end')); //+ 82799;

    $this->input->post('coupon_code') == 'none'  ? $coupon = null :
    $coupon = $this->input->post('coupon_code');

    $cardnumber = $this->input->post('card_number');
    $card_number = preg_replace('/[^0-9]/', '', $cardnumber);

    if ($this->input->post('customer_plan') == 'none') {
        $entry_data = array(
            'email' => $this->input->post('email'),
            'account_balance' => $account_balance,
            'coupon' => $coupon,

            // CC Data
            'card' => array(
                            'name' => $this->input->post('card_name'),
                            //'number'    => $card_number,
                            //'cvc'       => $this->input->post('card_cvc'),
                            //'exp_month' => $this->input->post('card_exp_month'),
                            //'exp_year'  => $this->input->post('card_exp_year'),
                            'address_line1' => $this->input->post('card_add1'),
                            'address_line2' => $this->input->post('card_add2'),
                            'address_city' => $this->input->post('card_city'),
                            'address_state' => $this->input->post('card_state'),
                            'address_zip' => (string) $this->input->post('card_zip'),
            ),
        );
    } else {
        $entry_data = array(
            // User Data
            'email' => $this->input->post('email'),
            'account_balance' => $account_balance,
            'plan' => $this->input->post('customer_plan'),
            'quantity' => $plan_quantity,
            'trial_end' => $trial_end,
            'coupon' => $coupon,

            // CC Data
            'card' => array(
                            'name' => $this->input->post('card_name'),
                            //'number'    => $card_number,
                            //'cvc'       => $this->input->post('card_cvc'),
                            //'exp_month' => $this->input->post('card_exp_month'),
                            //'exp_year'  => $this->input->post('card_exp_year'),
                            'address_line1' => $this->input->post('card_add1'),
                            'address_line2' => $this->input->post('card_add2'),
                            'address_city' => $this->input->post('card_city'),
                            'address_state' => $this->input->post('card_state'),
                            'address_zip' => (string) $this->input->post('card_zip'),
            ),
        );
    }

    /*
    if (
        ($entry_data['interval'] == 'week') && ( $entry_data['interval_count'] > '52')  ||
        ($entry_data['interval'] == 'month') && ( $entry_data['interval_count'] > '12') ||
        ($entry_data['interval'] == 'year') && ( $entry_data['interval_count'] > '1')
       )
    {
        echo "<script>alert('Stripe can only accept recurring plans of up to one year.');</script>";

    }elseif ( ($id == false) || ($entry_data['name'] == false) || ($plan_price == false) || ($entry_data['interval_count'] == false) ) {
        echo "<script>alert('Please fill in the plan name, price, and number of weeks/months/year.');</script>";

    }else {
    */

    $customer = \Stripe\Customer::create($entry_data);
    $customer_users = array(
                'customer_user_id' => $this->current_user->id,
                'customer_user_customer_id' => $customer->id,
                );
    $this->streams->entries->insert_entry($customer_users, 'customer_user', 'payignite', $skips = array(), $extra = array());
    header('location: '.$_SERVER['REQUEST_URI']);
    //}
}

?>


<section class="title">
    <h4><?php echo lang('payignite:customer_create'); ?></h4>
</section>

<section class="item">
<div class="content">

    <?php $form_data = array('class' => '.form1', 'onsubmit' => 'return checkValid();');
    echo form_open('admin/payignite/customers/create', $form_data); ?>
    <table class="table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
        <th><?php echo lang('payignite:customer_email'); ?></th>
        <th><?php echo lang('payignite:customer_beginning_balance'); ?></th>
                <th><?php echo lang('payignite:plan'); ?></th>
                <th><?php echo lang('payignite:plan_quantity'); ?></th>
                <th><?php echo lang('payignite:trial_end'); ?></th>
                <th><?php echo lang('payignite:coupon_code'); ?></th>
        </tr>
    </thead>

        <tbody>
            <tr>
                <td>
                    <?php
                    $email_data = array(
                                        'name' => 'email',
                                        'class' => 'required',
                                        );
                    echo form_input($email_data);
                    ?>
                </td>

                <td>$
                    <?php $account_balance = array('name' => 'account_balance', 'size' => '5');
                    echo form_input($account_balance); ?>
                </td>

                <td>
                <?php
                    $data['entries'] = \Stripe\Plan::all();
                    $plan_options = array('none' => 'No Plan');
                    foreach ($entries['data'] as $row) {
                        $plan_options[$row->id] = $row->name;
                    }
                    echo form_dropdown('customer_plan', $plan_options); ?>
                </td>

                <td><?php $plan_quantity_data = array('name' => 'plan_quantity', 'size' => '2'); ?>
                    <?php echo form_input($plan_quantity_data); ?>
                </td>

                <td><?php $trial_end_data = array('name' => 'trial_end', 'size' => '8', 'placeholder' => 'mm/dd/yy'); ?>
                    <?php echo form_input($trial_end_data); ?>
                </td>

                <td><?php
                    //$coupons['Coupons'] = Stripe_Coupon::all();
                    $coupon_options = array('none' => 'No Coupon');
                    //print_r($Coupons);
                    foreach ($Coupons['data'] as $row) {
                        $coupon_options[$row->id] = $row['id'];
                    }

                    //$coupon_code_data = array('name' => 'coupon_code', 'size' => '8');
                    echo form_dropdown('coupon_code', $coupon_options, 'none'); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
        <th><?php echo lang('payignite:customer_default_card'); ?></th>
        <th></th>
                <th></th>
                <th></th>
                <th></th>
        </tr>
        </thead>

        <tbody>
        <thead>
        <tr>
        <th><?php echo lang('payignite:customer_name'); ?></th>
        <th><?php echo lang('payignite:customer_card_number'); ?></th>
                <th><?php echo lang('payignite:customer_card_cvc'); ?></th>
                <th><?php echo lang('payignite:customer_card_exp_date'); ?></th>
                <th></th>
        </tr>
    </thead>
        <tr>
        <td><?php $card_name_data = array('name' => 'card_name', 'class' => '.card-name');
                    echo form_input($card_name_data); ?>
                </td>

                <td><?php $card_number_data = array('name' => 'card_number', 'class' => '.card-number');
                    echo form_input($card_number_data); ?>
                </td>

                <td><?php $cvc_data = array('name' => 'card_cvc', 'size' => '3', 'class' => '.card-cvc');
                    echo form_input($cvc_data); ?>
                </td>

                <td>
                    <?php $month_data = array('name' => 'card_exp_month', 'size' => '3', 'placeholder' => 'mm', 'class' => '.card-exp-month');
                    echo form_input($month_data); ?>
                    /
                    <?php $year_data = array('name' => 'card_exp_year', 'size' => '3', 'placeholder' => 'yy', 'class' => '.card-exp-year');
                    echo form_input($year_data); ?>
                </td>

                <td>
                </td>
        </tr>
    <thead>
            <tr>
        <th><?php echo lang('payignite:customer_address1'); ?></th>
        <th><?php echo lang('payignite:customer_address2'); ?></th>
                <th><?php echo lang('payignite:customer_city'); ?></th>
                <th><?php echo lang('payignite:customer_state'); ?></th>
                <th><?php echo lang('payignite:customer_zip'); ?></th>
        </tr>
    </thead>
        <tr>
        <td><?php $card_add1_data = array('name' => 'card_add1', 'class' => '.card-add1');
                    echo form_input($card_add1_data); ?>
                </td>

                <td><?php $card_add2_data = array('name' => 'card_add2', 'class' => '.card-add2');
                    echo form_input($card_add2_data); ?>
                </td>

                <td><?php $card_city_data = array('name' => 'card_city', 'class' => '.card-city');
                    echo form_input($card_city_data); ?>
                </td>

                <td><?php $card_state_data = array('name' => 'card_state', 'class' => '.card-state');
                    echo form_input($card_state_data); ?>
                </td>

                <td><?php $card_zip_data = array('name' => 'card_zip', 'class' => '.card-zip');
                    echo form_input($card_zip_data); ?>
                </td>
            </tr>

            <tr>
                <td></td>



                <td class="actions"><?php $save_data = array(
                                    'name' => 'submit',
                                    'value' => 'true',
                                    'type' => 'submit',
                                    'content' => lang('payignite:save'),
                                    'class' => 'btn blue',
                                    );
                echo form_button($save_data); ?></td>

                <td class="actions"><?php $reset_data = array(
                                    'content' => lang('payignite:reset'),
                                    'class' => 'btn gray',
                                    );
                $reset_action = 'onClick="this.form.reset(); checkValidAll();"';
                echo form_button($reset_data, '', $reset_action); ?></td>
                <td></td>
                <td></td>
            </tr>
    </tbody>
    </table>
    <?php echo form_close(); ?>

</div>
</section>

<div id="dialog" title="Check Form">
<p>Please fill in all required (<img src="addons/shared_addons/modules/payignite/css/required_star.png" alt="*">) fields.</p>
</div>
