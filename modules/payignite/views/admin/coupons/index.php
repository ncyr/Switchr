<section class="title">
	<h4><?php echo lang('payignite:coupons'); ?></h4>
</section>

<section class="item">
<div class="content">
    <?php if ($entries['data'] > 0): ?>

<table class="table data-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo lang('payignite:coupon_code'); ?></th>
            <th><?php echo lang('payignite:amount_off') . " / " . lang('payignite:percent_off'); ?></th>
            <th><?php echo lang('payignite:duration'); ?></th>
            <th><?php echo lang('payignite:max_redemptions'); ?></th>
            <th><?php echo lang('payignite:end_date'); ?></th>
            <th class='dis'></th>
        </tr>
    </thead>
    <tbody>


            <?php foreach ($entries['data'] as $row): ?>
                <tr>
                    <td><?php echo $row->id; ?></td>

                    <td><?php
                    if ($row->percent_off != false) {
                        echo $row->percent_off . " %";
                    } else {
                        echo "$ " . substr_replace($row->amount_off, '.', -2, -2);
                    } ?>
                    </td>

                    <td><?php
                    if ($row->duration != 'repeating') {
                        echo ucfirst($row->duration);
                    } else {
                        echo $row->duration_in_months . " month(s)";
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

                    <td><?php if ($row->redeem_by != false) {
                        echo date('m/d/y', $row->redeem_by);
                    } else {
                        echo "Never";
                    } ?>
                    </td>

                    <td class="actions"><?php
                        echo anchor('admin/payignite/coupons/delete/' . $row['id'], lang('global:delete'), array('class' => 'confirm btn delete red')); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
    <?php else: ?>
            <div class="no_data"><?php echo lang('payignite:no_entries'); ?></div>
    <?php endif;?>
    </tbody>
</table>

</div>
</section>
