<?php //if the form is being submitted, do this:
    if ($this->input->post('submit')) {
        $planprice = $this->input->post('plan_amount');
        $plan_price = str_replace('.', '', $planprice);
        $ID = $this->input->post('plan_name');
        $id = strtolower(str_replace(' ', '', $ID));

        $entry_data = array(
            'id' => $id,
            'name' => $this->input->post('plan_name'),
            'amount' => (int) $plan_price,
            'currency' => 'usd',
            'interval' => $this->input->post('plan_interval'),
            'interval_count' => (int) $this->input->post('plan_interval_count'),
            'trial_period_days' => (int) $this->input->post('plan_trial_period_days'),
        );

        if (
            ($entry_data['interval'] == 'week') && ($entry_data['interval_count'] > '52')  ||
            ($entry_data['interval'] == 'month') && ($entry_data['interval_count'] > '12') ||
            ($entry_data['interval'] == 'year') && ($entry_data['interval_count'] > '1')
           ) {
            echo "<script>alert('Stripe can only accept recurring plans of up to one year.');</script>";
        } elseif (($id == false) || ($entry_data['name'] == false) || ($plan_price == false) || ($entry_data['interval_count'] == false)) {
            echo "<script>alert('Please fill in the plan name, price, and number of weeks/months/year.');</script>";
        } else {
            \Stripe\Plan::create($entry_data);
            header('location: '.$_SERVER['REQUEST_URI']);
        }
    }
?>

<section class="title">
    <h4><?php echo lang('payignite:plan_create'); ?></h4>
</section>

<section class="item">
<div class="content">


    <table class="table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
        <th><?php echo lang('payignite:plan_name_name'); ?></th>
        <th><?php echo lang('payignite:plan_term'); ?></th>
                <th><?php echo lang('payignite:plan_amount'); ?></th>
                <th><?php echo lang('payignite:plan_trial_period'); ?></th>
                <th></th>
        </tr>
    </thead>

        <tbody>
            <tr>
                <?php $form_data = array('onsubmit' => 'return checkValid();');
                    echo form_open('admin/payignite/plans/create', $form_data); ?>
                <td><?php $plan_name_data = array('name' => 'plan_name', 'class' => 'required');
                    echo form_input($plan_name_data); ?></td>

                <td>
                    <?php $term_data = array('name' => 'plan_interval_count', 'size' => '3', 'style' => 'vertical-align:top', 'class' => '.interval_count required');
                    echo form_input($term_data);

                    $term_options = array(
                    'week' => 'Week(s)',
                    'month' => 'Month(s)',
                    'year' => 'Year',
                    );

                    echo form_dropdown('plan_interval', $term_options, 'week'); ?>
                </td>

                <?php $price_data = array('name' => 'plan_amount', 'placeholder' => 'XX.YY', 'class' => 'required') ?>
                <td>$ <?php echo form_input($price_data); ?></td>

                <?php $trial_period_data = array('name' => 'plan_trial_period_days', 'size' => '3', 'placeholder' => '0'); ?>
                <td><?php echo form_input($trial_period_data); ?> days</td>

                <td class="actions">
                <?php $save_data = array(
                                    'name' => 'submit',
                                    'value' => 'true',
                                    'type' => 'submit',
                                    'content' => lang('payignite:save'),
                                    'class' => 'btn blue',
                                    'style' => 'margin-right: 5px',
                                    );
                echo form_button($save_data);

                $reset_data = array(
                                    'content' => lang('payignite:reset'),
                                    'class' => 'btn gray',
                                    );
                $reset_action = 'onClick="this.form.reset(); checkValidAll();"';
                echo form_button($reset_data, '', $reset_action); ?>
                </td>
                <?php echo form_close(); ?>
            </tr>

            <?php foreach ($entries['data'] as $row): ?>
        <tr>
        <td><?php echo $row->name; ?></a>
                </td>

                <td><?php
                if ($row->interval_count > 1) {
                    echo $row->interval_count.' '.$row->interval.'s';
                } else {
                    echo $row->interval_count.' '.$row->interval;
                } ?>
                </td>

                <td><?php
                $plan_amount = substr_replace($row->amount, '.', -2, -2);
                echo '$ '.$plan_amount; ?>
                </td>

                <td>
                <?php if ($row->trial_period_days == 0) {
    echo 'No Trial';
} else {
    echo $row->trial_period_days.' days';
} ?>
                </td>

                <td class="actions">
                <?php
                echo anchor('admin/payignite/plans/delete/'.$row['id'], lang('global:delete'), array('class' => 'confirm btn delete red')); ?>
                </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    </table>

</div>
</section>

<div id="dialog" title="Check Form">
<p>Please fill in all required (<img src="addons/shared_addons/modules/payignite/css/required_star.png" alt="*">) fields.</p>
</div>
