<section class="title">
	<h4><?php echo lang('payignite:plans'); ?></h4>
</section>

<section class="item">
<div class="content">
    <?php if ($entries['data'] > 0): ?>
	
    <table class="table data-table" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
		<th><?php echo lang('payignite:plan_name_name'); ?></th>
                <!-- 'term' and 'price' = interval and interval_count in Stripe  for recurring charges -->
		<th><?php echo lang('payignite:plan_term'); ?></th>
                <th><?php echo lang('payignite:plan_amount'); ?></th>
                <th><?php echo lang('payignite:plan_trial_period'); ?></th>
                <th class='dis'></th>
	    </tr>
	</thead>
	
        <tbody>
	    <?php foreach ($entries['data'] as $row): ?>
	    <tr>
		<td><?php echo $row->name; ?></a>
                </td>
		
                <td><?php
                if ($row->interval_count > 1) {
                    echo $row->interval_count . " " . $row->interval . "s";
                } else {
                    echo $row->interval_count . " " . $row->interval;
                } ?>
                </td>
                
                <td><?php
                $plan_amount = substr_replace($row->amount, '.', -2, -2);
                echo "$ " . $plan_amount; ?>
                </td>
                
                <td>
                <?php if ($row->trial_period_days == 0) {
                    echo "No Trial";
                } else {
                echo $row->trial_period_days . " days";
                } ?>
                </td>
		
                <td class="actions">
		<?php echo anchor('admin/payignite/plans/delete/' . $row['id'], lang('global:delete'), array('class' => 'confirm btn delete red')); ?>
                </td>
	    </tr>
            <?php endforeach; ?>
	</tbody>
    </table>

    <?php else: ?>
	<div class="no_data"><?php echo lang('payignite:plan_no_entries'); ?></div>
    <?php endif;?>

</div>
</section>