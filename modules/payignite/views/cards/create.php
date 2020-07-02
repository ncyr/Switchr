<?php //if the form is being submitted, do this:

    if ($this->input->post('submit')) {
        $cardnumber = $this->input->post('card_number');
        $card_number = preg_replace('/[^0-9]/', '', $cardnumber);

        $entry_data = array(
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
            ),
        );

        $cu = \Stripe\Customer::retrieve($customer['customer_user_customer_id']);
        $cu->cards->create($entry_data);
        header('location: '.$_SERVER['REQUEST_URI']);
    }

?>




<section class="title">
    <h4><?php echo lang('payignite:add_card'); ?></h4>
</section>

<section class="item">
<div class="content">

    <?php $form_data = array('class' => '.form1', 'onsubmit' => 'return checkValid();');
    echo form_open('payignite/cards/create', $form_data); ?>


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
