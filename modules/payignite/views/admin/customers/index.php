<section class="title">
	<h4><?php echo lang('payignite:customers'); ?></h4>
</section>

<section class="item">
<div class="content">
    <?php if ($entries['data'] > 0): ?>
	
    <table class="table data-table" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
		<th><?php echo lang('payignite:customer_email') . " (click to email)"; ?></th>
		<th><?php echo lang('payignite:customer_id') . " (click to view details)"; ?></th>
                <th><?php echo lang('payignite:customer_subscriptions'); ?></th>
                <th><?php echo lang('payignite:customer_default_card'); ?></th>
                <th class="dis"></th>
                <th class="dis"></th>
	    </tr>
	</thead>
	
        <tbody>
	    <?php foreach ($entries['data'] as $row): ?>
	    <tr>
		<td><?php
                echo "<a href='mailto:" . $row->email . "'>" . $row->email . "</a>";
                ?>
                </td>
		
                <td><?php
                echo "<a href='admin/payignite/customers/view/" . $row->id . "'>" . $row->id . "</a>";
                ?>
                </td>
                
                <td><?php
                echo $row->subscriptions->total_count;
                ?>
                </td>
                
                <td><?php
                echo $row->default_card;
                ?>
                </td>
		
                <td class="actions"><?php
		echo anchor('admin/payignite/customers/edit/' . $row['id'], lang('global:edit'), array('class' => 'btn blue')); ?>
                </td>
                
                <td class="actions"><?php
                echo anchor('admin/payignite/customers/delete/' . $row['id'], lang('global:delete'), array('class' => 'confirm btn delete red')); ?>
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