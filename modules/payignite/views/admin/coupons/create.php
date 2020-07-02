<?php

if ($this->input->post('submit')) {
    $redeem_by = strtotime($this->input->post('end_date')); //+ 82799;

    if (($this->input->post('off') == 'amount') && ($this->input->post('duration') != 'repeating')) {
        $entry_data = array(
            'amount_off' => (int) str_replace('.', '', $this->input->post('amount_off')),
            'currency' => 'usd',
            'duration' => $this->input->post('duration'),
        );
    } elseif (($this->input->post('off') == 'percent') && ($this->input->post('duration') != 'repeating')) {
        $entry_data = array(
            'percent_off' => (int) $this->input->post('percent_off'),
            'duration' => $this->input->post('duration'),
        );
    } elseif (($this->input->post('off') == 'amount') && ($this->input->post('duration') == 'repeating')) {
        $entry_data = array(
            'amount_off' => (int) str_replace('.', '', $this->input->post('amount_off')),
            'currency' => 'usd',
            'duration' => $this->input->post('duration'),
            'duration_in_months' => (int) $this->input->post('duration_months'),
        );
    } elseif (($this->input->post('off') == 'percent') && ($this->input->post('duration') == 'repeating')) {
        $entry_data = array(
            'percent_off' => (int) $this->input->post('percent_off'),
            'duration' => $this->input->post('duration'),
            'duration_in_months' => (int) $this->input->post('duration_months'),
        );
    }
    $entry_data['id'] = strtoupper($this->input->post('id'));
    $entry_data['redeem_by'] = $this->input->post('end_date') == '' ? null : $redeem_by;
    $entry_data['max_redemptions'] = $this->input->post('max_redemptions') === 0 || $this->input->post('max_redemptions') === '' ? null : $this->input->post('max_redemptions');

    \Stripe\Coupon::create($entry_data);
    header('location: '.$_SERVER['REQUEST_URI']);
}

?>

<section class="title">
    <h4><?php echo lang('payignite:coupon_create'); ?></h4>
</section>

<section class="item">
<div class="content">

<?php $form_data = array('onsubmit' => 'return checkValid();');
    echo form_open('admin/payignite/coupons/create', $form_data);
?>

<table class="table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo lang('payignite:coupon_code'); ?></th>
            <th><?php echo lang('payignite:amount_off').' / '.lang('payignite:percent_off'); ?></th>
            <th><?php echo lang('payignite:duration'); ?></th>
            <th><?php echo lang('payignite:max_redemptions'); ?></th>
            <th><?php echo lang('payignite:end_date'); ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php $coupon_id = array('name' => 'id', 'placeholder' => 'ex: 25OFF', 'class' => 'required');
            echo form_input($coupon_id); ?>
            </td>

            <td><?php
            $radio1_data = array(
                'name' => 'off',
                'value' => 'amount',
                'checked' => false,
                'style' => 'margin:10px',
            );
            $radio2_data = array(
                'name' => 'off',
                'value' => 'percent',
                'checked' => false,
                'style' => 'margin:10px',
            );
            $amount_data = array(
                'name' => 'amount_off',
                'placeholder' => '25.00',
                'size' => '5',
                'style' => '',
                'class' => 'required',
            );
            $percent_data = array(
                'name' => 'percent_off',
                'placeholder' => '10',
                'size' => '5',
                'style' => '',
                'class' => 'required',
            );
            $radio_onclick = 'onClick="radioOnClick()"';
            echo form_radio($radio1_data, '', '', $radio_onclick)."<span id='amount'>$ &nbsp;</span>".form_input($amount_data).'<br>';
            echo form_radio($radio2_data, '', '', $radio_onclick)."<span id='percent'>% </span>".form_input($percent_data);?>
            </td>

            <td><?php
            $js1 = 'id="duration" onChange="durationBox();"';
            $duration_data = array(
                'once' => 'Once',
                'repeating' => 'Repeating - How many months?',
                'forever' => 'Forever',
            );
            $duration_months = array(
                'name' => 'duration_months',
                'size' => '4',
                'placeholder' => 'ex:3',
                'style' => 'visibility: hidden',
                'class' => 'required',
            );
            echo form_dropdown('duration', $duration_data, 'forever', $js1).'<br>';

            echo form_input($duration_months); ?>
            </td>

            <td><?php $max_redemptions = array('name' => 'max_redemptions', 'placeholder' => 'ex:20', 'size' => '5');
            echo form_input($max_redemptions); ?>
            </td>

            <td><?php $end_date_data = array('name' => 'end_date', 'placeholder' => 'mm/dd/yy', 'size' => '7');
            echo form_input($end_date_data); ?>
            </td>

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
                $reset_action = 'onClick="this.form.reset(); showHidden(); checkValidAll();"';
                echo form_button($reset_data, '', $reset_action);
                ?>
            </td>
        </tr>

    <?php if ($entries['data'] > 0) : ?>
        <tr>
            <?php foreach ($entries['data'] as $row) : ?>
                <tr>
                    <td><?php echo $row->id; ?></td>

                    <td><?php
                    if ($row->percent_off != false) {
                        echo $row->percent_off.' %';
                    } else {
                        echo '$ '.substr_replace($row->amount_off, '.', -2, -2);
                    } ?>
                    </td>

                    <td><?php
                    if ($row->duration != 'repeating') {
                        echo ucfirst($row->duration);
                    } else {
                        echo $row->duration_in_months.' month(s)';
                    } ?>
                    </td>

                    <td><?php
                    if ($row->max_redemptions != $row->times_redeemed) {
                        echo $row->times_redeemed.' of '.$row->max_redemptions;
                    } elseif ($row->max_redemptions == null) {
                        echo $row->times_redeemed.' of UNLIMITED';
                    } else {
                        echo $row->max_redemptions.' ( EXPIRED )';
                    } ?>
                    </td>

                    <td><?php
                    if ($row->redeem_by != false) {
                        echo date('m/d/y', $row->redeem_by);
                    } else {
                        echo 'Never';
                    } ?>
                    </td>

                    <td class="actions"><?php
                        echo anchor('admin/payignite/coupons/delete/'.$row['id'], lang('global:delete'), array('class' => 'confirm btn delete red')); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
    <?php else : ?>
            <div class="no_data"><?php echo lang('payignite:no_entries'); ?></div>
    <?php endif;?>
    </tbody>
</table>
<?php echo form_close(); ?>

</div>
</section>

<div id="dialog" title="Check Form">
<p>Please fill in all required (<img src="addons/shared_addons/modules/payignite/css/required_star.png" alt="*">) fields.</p>
</div>
