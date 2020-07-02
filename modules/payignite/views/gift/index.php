<?php

if ($this->input->post('submit')) {
    $random_id = $this->Payignite->randomString();
    $cardnumber = $this->input->post('card_number');
    $card_number = preg_replace('/[^0-9]/', '', $cardnumber);
    $card_last4 = substr($card_number, -4);
    $customer_email = $this->input->post('email');

    $entry_data = array(
        'id' => $random_id, //random by Stripe default...but, you know
        'amount_off' => (int) str_replace('.', '', $this->input->post('amount_off')),
        'currency' => 'usd',
        'duration' => 'once',
        'max_redemptions' => (int) 1,
    );

    try {
        \Stripe\Charge::create(array(
            'amount' => (int) str_replace('.', '', $this->input->post('amount_off')),
            'currency' => 'usd',
            'description' => 'FitHavens Gift Card',
            'metadata' => array('coupon' => "$random_id"),
        // CC Data
        'card' => array(
            'name' => $this->input->post('card_name'),
            'number' => $card_number,
            'cvc' => $this->input->post('card_cvc'),
            'exp_month' => $this->input->post('card_exp_month'),
            'exp_year' => $this->input->post('card_exp_year'),
            'address_line1' => $this->input->post('card_add1'),
            'address_line2' => $this->input->post('card_add2'),
            'address_city' => $this->input->post('card_city'),
            'address_state' => $this->input->post('card_state'),
            'address_zip' => (string) $this->input->post('card_zip'),
            ), ));
    } catch (\Stripe\ApiConnectionError $e) {
        // Network problem, perhaps try again.
            echo 'The network is having problems. Please try again later.';

        return false;
    } catch (\Stripe\InvalidRequestError $e) {
        // You screwed up in your programming. Shouldn't happen!
            echo 'Something is amiss. Please contact the web developer.';

        return false;
    } catch (\Stripe\ApiError $e) {
        // Stripe's servers are down!
            echo 'The Stripe network is down. Please try again later.';

        return false;
    } catch (\Stripe\CardError $e) {
        // Since it's a decline, Stripe_CardError will be caught
            $body = $e->getJsonBody();
        $err = $body['error'];
        echo 'Status is:'.$e->getHttpStatus()."\n";
        echo 'Type is:'.$err['type']."\n";
        echo 'Code is:'.$err['code']."\n";
            // param is '' in this case
            echo 'Param is:'.$err['param']."\n";
        echo 'Message is:'.$err['message']."\n";

        return false;
    }

    \Stripe\Coupon::create($entry_data);
    $email_array = array(

                // Info you want to be available to the email template and call with Lex {{ }} tags.
                'card_last4' => $card_last4, // access via {{ card_last4 }}
                'coupon_id' => $random_id,
                'amount' => '$'.$this->input->post('amount_off'),

                // email info
                'slug' => 'gift-card',  // The email template to use
                'to' => $customer_email,
                'reply-to' => Settings::get('contact_email'), // whom to reply
                'from' => Settings::get('server_email'),  // address the email is from
                'name' => Settings::get('site_name'),     // name the email is from
            );

    // Email user
    Events::trigger('email', $email_array, 'array');

    // This works, but the div and jQuery modal doesn't.
    echo "<script>alert('Gift Card ID: ".$random_id.". An email has been sent to you.');</script>";
}

?>

<section class="title">
    <h4><?php echo lang('payignite:gift_create'); ?></h4>
        <p>All fields required</p>
</section>

<section class="item">
<div class="content">

<?php $form_data = array('onsubmit' => 'return checkValid();');
    echo form_open('payignite/gift', $form_data); ?>
<table class="table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th><?php echo lang('payignite:amount') ?></th>
            <th><?php echo lang('payignite:customer_email'); ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr class='none'>

            <td></td>
            <td></td>
            <td></td>
            <td>
            <?php
            $amount_data = array(
                'name' => 'amount_off',
                'placeholder' => '25.00',
                'size' => '5',
                'style' => '',
                'class' => 'required smallbox none',
            );

            echo "<span id='amount' style='display:inline-block;'>$ &nbsp;</span>".form_input($amount_data); ?>
            </td>

            <td>
            <?php
            $email_data = array(
                'name' => 'email',
                'size' => '15',
                'style' => '',
                'class' => 'required smallbox none',
            );

            echo "<span style='display:inline-block;'>Email: &nbsp;</span>".form_input($email_data); ?>
            </td>
            <td></td>


        </tr>
    </tbody>
</table>

<table class="table" cellpadding="0" cellspacing="0">


        <thead>
        <tr>
        <th><?php echo lang('payignite:customer_name'); ?></th>
        <th><?php echo lang('payignite:customer_card_number'); ?></th>
                <th><?php echo lang('payignite:customer_card_cvc'); ?></th>
                <th><?php echo lang('payignite:customer_card_exp_date'); ?></th>
                <th></th>
        </tr>
    </thead>
        <tbody>
        <tr class='none'>
        <td><?php $card_name_data = array('name' => 'card_name', 'class' => 'card-name required none');
                    echo form_input($card_name_data); ?>
                </td>

                <td><?php $card_number_data = array('name' => 'card_number', 'class' => 'card-number required none');
                    echo form_input($card_number_data); ?>
                </td>

                <td><?php $cvc_data = array('name' => 'card_cvc', 'size' => '3', 'class' => 'card-cvc required none', 'style' => 'width:30%;');
                    echo form_input($cvc_data); ?>
                </td>

                <td>
                    <?php $month_data = array('name' => 'card_exp_month', 'size' => '3', 'placeholder' => 'mm', 'class' => 'card-exp-month smallbox2 required none');
                    echo form_input($month_data); ?>
                    /
                    <?php $year_data = array('name' => 'card_exp_year', 'size' => '3', 'placeholder' => 'yy', 'class' => 'card-exp-year smallbox2 required none');
                    echo form_input($year_data); ?>
                </td>

                <td>
                </td>
        </tr>
        </tbody>
    <thead>
            <tr>
        <th><?php echo lang('payignite:customer_address1'); ?></th>
        <th><?php echo lang('payignite:customer_address2'); ?></th>
                <th><?php echo lang('payignite:customer_city'); ?></th>
                <th><?php echo lang('payignite:customer_state'); ?></th>
                <th><?php echo lang('payignite:customer_zip'); ?></th>
        </tr>
    </thead>
        <tbody>
        <tr class='none'>
        <td><?php $card_add1_data = array('name' => 'card_add1', 'class' => 'card-add1 required none');
                    echo form_input($card_add1_data); ?>
                </td>

                <td><?php $card_add2_data = array('name' => 'card_add2', 'class' => 'card-add2');
                    echo form_input($card_add2_data); ?>
                </td>

                <td><?php $card_city_data = array('name' => 'card_city', 'class' => 'card-city required none');
                    echo form_input($card_city_data); ?>
                </td>

                <td><?php $card_state_data = array('name' => 'card_state', 'class' => 'card-state required none');
                    echo form_input($card_state_data); ?>
                </td>

                <td><?php $card_zip_data = array('name' => 'card_zip', 'class' => 'card-zip required none');
                    echo form_input($card_zip_data); ?>
                </td>
            </tr>

            <tr class='none'>
                <td></td>

                <td class="actions"><?php $save_data = array(
                                    'name' => 'submit',
                                    'value' => 'true',
                                    'type' => 'submit',
                                    'content' => lang('payignite:save'),
                                    'class' => 'btn blue save',
                                    );
                echo form_button($save_data); ?>
                </td>

                <td class="actions"><?php $reset_data = array(
                                    'content' => lang('payignite:reset'),
                                    'class' => 'btn gray',
                                    );
                $reset_action = 'onClick="this.form.reset(); checkValidAll();"';
                echo form_button($reset_data, '', $reset_action); ?>
                </td>
                <td></td>
                <td></td>
            </tr>
    </tbody>
    </table>


<?php echo form_close(); ?>

</div>
</section>

<div id="dialog" title="Check Form">
<p>Please fill in all fields.</p>
</div>
